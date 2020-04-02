<?php

namespace OCA\Tenant_Portal\Service;

use \Exception;
use \InvalidArgumentException;

use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;
use \OCP\IUserManager;

use \OCA\Tenant_Portal\Db\Collaborator;
use \OCA\Tenant_Portal\Db\CollaboratorMapper;
use \OCA\Tenant_Portal\Service\CollaboratorTokenService;
use \OCA\Tenant_Portal\Service\TenantService;
use \OCA\Tenant_Portal\Service\TenantConfigService;
use \OCA\Tenant_Portal\Service\Exceptions\NotFoundException;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Mailer;

class CollaboratorService {
	const PASSWORD_MIN_LENGTH = 10;
	const PASSWORD_MAX_LENGTH = 64;
	const PASSWORD_SPECIAL_CHARS = "!@#^*_-+=";

	private $mapper;
	private $userManager;
	private $tenantService;
	private $tenantConfigService;
	private $collaboratorTokenService;

	/**
	 * @param CollaboratorMapper $mapper
	 */
	public function __construct(CollaboratorMapper $mapper, IUserManager $userManager, TenantService $tenantService, TenantConfigService $tenantConfigService, CollaboratorTokenService $collaboratorTokenService) {
		$this->mapper = $mapper;
		$this->userManager = $userManager;
		$this->tenantService = $tenantService;
		$this->tenantConfigService = $tenantConfigService;
		$this->collaboratorTokenService = $collaboratorTokenService;
	}

	/**
	 * Returns all collaborators
	 * @return Entity
	 */
	public function findAll() {
		return $this->mapper->findAll();
	}

	/**
	 * @param Exception $e
	 */
	public function handleException ($e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 * Return a collaborator by id
	 * @param integer $id
	 * @return Entity
	 */
	public function find($id) {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Create a new collaborator
	 *
	 * @param string $name
	 * @param string $short_code
	 */
	public function create($tenant_id, $mail, $cn, $password=null, $quota=null, $sendemail=false, $type=TenantConfigManager::COLLABORATOR) {
		// Get current user for logging purposes
		$user = Util::currentUser();

		// Validate input
		$this->validate($mail, $cn);

		// Create collaborator object
		$collaborator = $this->createCollaborator($mail, $mail, $cn, $password);

		if ($collaborator->getId()) {
			// Add collaborator reference to tenant
			$tenantConfig = $this->tenantConfigService->create($tenant_id, 'collaborator', $collaborator->getId());

			// Create Owncloud user
			$ocUser = $this->createOwncloudUser($mail, $password, $cn, $quota);
			$ocUser->setEmailAddress($mail);

			if ($sendemail) {
				// Send email, rollback on failure.
				try {
					$this->createSendMail($collaborator, $tenant_id);
				} catch (\Exception $e) {
					$this->destroy($tenant_id, $collaborator->getId());
					Util::debugLog("@Collaborator [user=>$user, action=>create, status=>failed_email tenant_id=>$tenant_id, collab_email=>$mail]");
					throw $e;
				}
			}
			Util::debugLog("@Collaborator [user=>$user, action=>create, tenant_id=$tenant_id, collab_email=>$mail, collab_name=>$cn]");
			return $collaborator;
		}
		throw new Exception("Unable to create collaborator");
	}

	/**
	 * Validate collaborator input
	 * @param string $mail
	 * @return boolean
	 */
	public function validate($mail, $cn) {
		if (\OC::$server->getUserManager()->userExists($mail) || $this->collaboratorExists($mail)) {
			throw new \InvalidArgumentException("$mail already exists or is already a CloudStor user");
		}
		if (!Util::validEmail($mail)) {
			throw new \InvalidArgumentException("$mail is not a valid email address");
		}
		if (preg_match("/[^a-zA-Z0-9 _\.@\-\']/", $mail)) {
			throw new \InvalidArgumentException("Email address can only contain A-Z, 0-9 and _.@-'");
		}
		if ($cn != strip_tags($cn)) {
			throw new \InvalidArgumentException("HTML tags are not allowed in the user's display name");
		}
	}

	/**
	 * Sends "New Collaborator" email
	 * @param Collaborator $collaborator
	 * @param int $tenant_id
	 * @return bool
	 */
	public function createSendMail($collaborator, $tenant_id) {
		$tenant = $this->tenantService->get($tenant_id);
		$token = $this->collaboratorTokenService->create($collaborator->getId());
		$resetUrl = \OC::$server->getUrlGenerator()->linkToRouteAbsolute('tenant_portal.view.collaborator_reset_password', Array('token'=>$token->getToken()));
		$mailer = new Mailer(
			$collaborator->getMail(),
			Array('owncloud-noreply@example.com' => "OwnCloud"),
			"Your new OwnCloud Collaborator Account",
			'collaborator.new',
			Array('tenantName'=>$tenant->getName(), 'collaborator'=>$collaborator->getUid(), 'displayName'=>$collaborator->getCn(), 'resetUrl'=>$resetUrl));
		$mailer->send();
		return true;
	}

	/**
	 * Request password reset
	 * @param int $collaborator_uid
	 * @return mixed
	 */
	public function requestPasswordReset($collaborator_uid) {
		try {
			$collaborator = $this->mapper->findByUser($collaborator_uid);
			$collaboratorToken = $this->collaboratorTokenService->create($collaborator->getId());
			$toEmail = $collaborator->getMail();
			$verifyUrl = \OC::$server->getUrlGenerator()->linkToRouteAbsolute('tenant_portal.view.collaborator_reset_password', Array('token'=>$collaboratorToken->getToken()));
			$email = new Mailer(
				$toEmail,
				Array('owncloud-noreply@example.com' => "OwnCloud"),
				"Password Reset Confirmation for your OwnCloud Collaborator Account",
				'collaborator.resetpass.confirmation',
				Array('displayName'=>$collaborator->getCn(), 'verifyUrl'=>$verifyUrl));
			$email->send();
			return $collaboratorToken;
		} catch (NotFoundException $e) {
			return false;
		} catch (\Exception $e) {
			throw $e;
		}
	}


	/**
	 * Reset password
	 * @param string $token
	 * @param string $password
	 * @param stirng $confirm
	 * @return Collaborator
	 */
	public function resetPassword($token, $password, $confirm) {
		$token = $this->collaboratorTokenService->validToken($token);
		if ($token && $password) {
			if ($this->validPassword($password, $confirm)) {
				$collaborator = $this->find($token->getCollaboratorId());
				$hashed_password = hash('sha256', $collaborator->getSalt().$password);
				$collaborator->setPassword($hashed_password);
				$collaborator = $this->mapper->update($collaborator);
				$this->collaboratorTokenService->deleteByCollaboratorId($token->getCollaboratorId());
				Util::debugLog("@Collaborator [user=>".$collaborator->getUid().", action=>reset_password]");
				return $collaborator;
			}
		} else {
			throw new InvalidArgumentException("The token provided is invalid or has already been used.");
		}
	}

	/**
	 * Validate a password
	 * @return boolean
	 */
	public function validPassword($password, $confirm=null) {
		if ($confirm && $confirm !== $password) {
			throw new InvalidArgumentException("Passwords do not match");
		}
		if (strlen($password) < self::PASSWORD_MIN_LENGTH || strlen($password) > self::PASSWORD_MAX_LENGTH) {
			throw new InvalidArgumentException("Password must be between ".self::PASSWORD_MIN_LENGTH." and ".self::PASSWORD_MAX_LENGTH." characters");
		}
		if (!preg_match("/^[a-zA-Z0-9@#$%^&+=_]*$/", $password)) {
			throw new InvalidArgumentException("Passwords can only contain A-Z, 0-9, and the special characters @#$%^&+=.");
		}
		if ((!preg_match("/[a-zA-Z]/", $password)) || (!preg_match("/[0-9@#$%^&+=_]/", $password))){
			throw new InvalidArgumentException("Password must contain at least one letter and one number or special character (@#$%^&+=).");
		}
		return true;
	}

	/**
	 * Delete a collaborator by id
	 * @param integer $id
	 */
	public function destroy($tenant_id, $id) {
		try {
			$collaborator = $this->mapper->find($id);
			$tenantConfigReference = $this->tenantConfigService->findByAll($tenant_id, "collaborator", $id);
			if (count($tenantConfigReference) > 0) {
				$this->tenantConfigService->delete($tenantConfigReference[0]->getId());
			}
			$user = Util::currentUser();
			Util::debugLog("@Collaborator [user=>$user, collaborator=>$id, action=>delete, collaborator_uid=>".$collaborator->getUid()."]");
			$this->mapper->delete($collaborator);
			return $collaborator;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Creates the collaborator
	 * @param string $uid
	 * @param string $mail
	 * @param string $cn
	 * @param string $password
	 * @return Collaborator
	 */
	public function createCollaborator($uid, $mail, $cn, $password=null) {
		// Hash password
		$salt = Util::randomString(6);
		if (is_null($password) || empty(trim($password))) {
			$password = Util::randomString(16, self::PASSWORD_SPECIAL_CHARS, true);
		}
		$hashed_password = hash('sha256', $salt.$password);

		// Create collaborator object
		$collaborator = new Collaborator();
		$collaborator->setUid($mail);
		$collaborator->setMail($mail);
		$collaborator->setCn($cn);
		$collaborator->setSalt($salt);
		$collaborator->setPassword($hashed_password);

		// Insert into database table
		return $this->mapper->insert($collaborator);
	}

	/**
	 * Creates the owncloud user
	 * @param string $uid
	 * @param string $password
	 * @param string $displayName
	 * @return IUser
	 */
	public function createOwncloudUser($uid, $password=null, $displayName=null, $quota=null) {
		if (is_null($password) || empty(trim($password))) {
			$password = Util::randomString(16, self::PASSWORD_SPECIAL_CHARS, true);
		}
		$ocUser = $this->userManager->createUser($uid, $password);
		if (!$ocUser) {
			throw new Exception("Unable to create CloudStor user");
		}
		if (!$displayName) {
			$displayName = $uid;
		}
		$ocUser->setDisplayName($displayName);

		// Set the Owncloud user's quota
		if ($quota) {
			$ocUser->setQuota($quota);
		}
		return $ocUser;
	}

	/**
	 * Check if a collaborator exists
	 * @param string $name
	 * @return boolean
	 */
	public function collaboratorExists($user) {
		try {
			$this->mapper->findByUser($user);
		} catch (Exception $e) {
			if ($e instanceof DoesNotExistException) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a collaborator exists
	 * @param string $name
	 * @return boolean
	 */
	public function collaboratorExistsById($user_id) {
		try {
			$this->mapper->find($user_id);
		} catch (Exception $e) {
			if ($e instanceof DoesNotExistException) {
				return false;
			}
		}
		return true;
	}
}
