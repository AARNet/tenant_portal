<?php

namespace OCA\Tenant_Portal\Managers;

use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Util;

class CollaboratorManager {

	private $collaboratorService;

	function __construct() {
		$this->collaboratorService = Util::getCollaboratorService();
	}

	/**
	 * Create a collaborator
	 * @param string $uid
	 * @param string $mail
	 * @param string $displayName
	 * @param string $quota
	 * @param string $sendemail
	 * @param string $type
	 * @return Colalborator
	 */
	public function create($uid, $mail, $displayName, $password=null, $quota=null, $sendemail=null, $type=TenantConfigManager::COLLABORATOR) {
		return $this->collaboratorService->create($uid, $mail, $displayName, $password, $quota, $sendemail, $type);
	}

	/**
	 * Create a collaborator user without validation
	 * @param string $uid
	 * @param string $mail
	 * @param string $displayName
	 * @param string $password
	 * @return Colalborator
	 */
	public function createCollaborator($uid, $mail, $cn, $password=null) {
		return $this->collaboratorService->createCollaborator($uid, $mail, $cn, $password);
	}

	/**
	 * Creates the owncloud user
	 * @param string $uid
	 * @param string $password
	 * @return IUser
	 */
	public function createOwncloudUser($uid, $password=null) {
		return $this->collaboratorService->createOwncloudUser($uid, $password);

	}

	/**
	 * Destroy a Collaborator
	 * @param int $tenant_id
	 * @param int $id
	 * @return Collaborator
	 */
	public function destroy($tenant_id, $id) {
		return $this->collaboratorService->destroy($tenant_id, $id);
	}

	/**
	 * Find a collaborator by ID
	 * @param int $id
	 * @return Collaborator
	 */
	public function find($id) {
		return $this->collaboratorService->find($id);
	}

	/**
	 * Set quota for a collaborator
	 * @param string $uid
	 * @param int $quota
	 * @return boolean
	 */
	public function setQuota($uid, $quota) {
		return $this->collaboratorService->setQuota($uid, $quota);
	}

	/**
	 * Request a password reset email
	 * @param string $uid
	 * @return
	 */
	public function requestPasswordReset($uid) {
		return $this->collaboratorService->requestPasswordReset($uid);
	}

	/**
	 * Reset password using a token
	 * @param string $token
	 * @param string $password
	 * @param string $confirm
	 * @return
	 */
	public function resetPassword($token, $password, $confirm) {
		return $this->collaboratorService->resetPassword($token, $password, $confirm);
	}

	/*
	 * Check if a collaborator exists
	 * @param string $name
	 * @return boolean
	 */
	public function collaboratorExists($user) {
		return $this->collaboratorService->collaboratorExists($user);
	}

	/**
	 * Returns whether collaborator exists
	 * @param int collaborator_id
	 * @return bool
	 */
	public function collaboratorExistsById($id) {
		return $this->collaboratorService->collaboratorExistsById($id);
	}

	/**
	 * Returns collaborator's user id
	 * @param int collaborator_id
	 * @return string
	 */
	public function getUserById($id) {
		try {
			$collaborator = $this->collaboratorService->find($id);
			return $collaborator->getUid();
		} catch (\Exception $e) {
			return null;
		}
	}
}
