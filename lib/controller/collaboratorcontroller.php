<?php
namespace OCA\Tenant_Portal\Controller;

use \Exception;
use \InvalidArgumentException;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Db\DoesNotExistException;

use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Managers\CollaboratorManager;
use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Captcha;
use \OCA\Tenant_Portal\Util;

class CollaboratorController extends Controller {

	protected $request;
	protected $tenantConfigManager;
	protected $collaboratorManager;
	protected $auditLogManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param CollaboratorManager $collaboratorManager
	 * @param TenantConfigManager $tenantConfigManager
	 */
	public function __construct($appName, IRequest $request, CollaboratorManager $collaboratorManager, TenantConfigManager $tenantConfigManager, AuditLogManager $auditLogManager) {
		parent::__construct($appName, $request);
		$this->request = $request;
		$this->tenantConfigManager = $tenantConfigManager;
		$this->collaboratorManager = $collaboratorManager;
		$this->auditLogManager = $auditLogManager;
	}

	/**
	 * List collaborators for tenant
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function index($tenant_id) {
		$tenantCollaborators = $this->tenantConfigManager->findAllWithKey($tenant_id, TenantConfigManager::COLLABORATOR);
		$collaborators = Array();
		if ($tenantCollaborators) {
			foreach ($tenantCollaborators as $collaborator) {
				try {
					$collaborators[] = $this->collaboratorManager->find($collaborator->getConfigValue());
				} catch (DoesNotExistException $e) {
					Util::debugLog("@CollaboratorIndex Collaborator ".$collaborator->getConfigValue()." does not exist");
				}
			}
		}
		return new DataResponse($collaborators);
	}

	/**
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function create($tenant_id, $email, $name, $quota="1 TB") {
		$collaborator = $this->collaboratorManager->create($tenant_id, $email, $name, null, $quota, true);
		$this->auditLogManager->logCreate($tenant_id, sprintf("Created collaborator '%s <%s>' with quota '%s'", $name, $email, $quota));
		return $collaborator;
	}

	/**
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function destroy($tenant_id, $id) {
		$collaborator = $this->collaboratorManager->destroy($tenant_id, $id);
		$this->auditLogManager->logDelete($tenant_id, sprintf("Deleted collaborator '%s <%s>'", $collaborator->getCn(), $collaborator->getMail()));
		return $collaborator;
	}

	/**
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function update() {
	}

	/**
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function generatePasswordResetToken($collaborator_uid, $captcha_token) {
		try {
			$captchaValidator = new Captcha($captcha_token);
			$captchaOk = $captchaValidator->validate();
			if ($captchaOk) {
				$token = $this->collaboratorManager->requestPasswordReset($collaborator_uid);
				return new DataResponse(['code' => 200, 'message' => "success"]);
			} else {
				return new DataResponse(['code' => 300, 'message' => "invalid_captcha"]);
			}
		} catch (\InvalidArgumentException $e) {
			return new DataResponse(['code' => 500, 'message' => "failed"]);
		}
	}

	/**
	 * @NoAdminRequired
	 * @PublicPage
	 */
	public function resetPassword($token, $password, $confirm_password) {
		try {
			$reset = $this->collaboratorManager->resetPassword($token, $password, $confirm_password);
			return new DataResponse(['code' => 200, 'message' => 'success']);
		} catch (\InvalidArgumentException $e) {
			$message = $e->getMessage();
			return new DataResponse(['code' => 500, 'message' => $message]);
		}
	}
}
