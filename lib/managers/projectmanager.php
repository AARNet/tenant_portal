<?php

namespace OCA\Tenant_Portal\Managers;

use \Exception;
use \InvalidArgumentException;

use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;
use \OCP\IUserManager;

use \OCA\Tenant_Portal\Manager\Exceptions\NotFoundException;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Managers\CollaboratorManager;
use \OCA\Tenant_Portal\Managers\ProjectFolderManager;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Mailer;

class ProjectManager {
	const PROJECTFOLDER = 'folder';
	const PROJECTQUOTA = 'quota';
	const PROJECTPREFIX = 'p-';
	const PROJECTDEFAULTEMAIL = 'owncloud-noreply@example.com'; 
	const PROJECTDEFAULTQUOTA = '1 TB';
	const PASSWORD_SPECIAL_CHARS = "!@#^*_-+=";

	private $tenantConfigManager;
	private $collaboratorManager;
	private $projectService;
	private $userManager;
	private $configManager;
	private $tenantManager;
	private $tenantUserManager;

	public function __construct() {
		$this->projectService = Util::getProjectService();
		$this->collaboratorManager = Util::getCollaboratorManager();
		$this->tenantConfigManager = Util::getTenantConfigManager();
		$this->userManager = Util::getUserManager();
		$this->configManger =\OC::$server->getConfig();
		$this->tenantManager = Util::getTenantManager();
		$this->tenantUserManager = Util::getTenantUserManager();
	}

	/**
	 * Find the group drive user
	*/
	public function find($id) {
		return $this->collaboratorManager->find($id);
	}

	/**
	 * Find a project option
	 * @param integer $id
	 * @return Project
	 */
	public function findOption($project_uid, $key) {
		return $this->projectService->findWithKey($project_uid, $key);
	}

	public function findCollaborator($project_uid) {
		return $this->collaboratorManager->find($project_uid);
	}

	/**
	 * Create a project
	 * @param string $tenant_id
	 * @param string $uid
	 * @param string $displayName
	 * @param string $notifyEmail
	 * @param string $quota
	 * @return Mixed
	 */
	public function create($tenant_id, $displayName, $notifyEmail=null, $quota="1 TB") {
		Util::debugLog("@Project [action=>create, tenant_id=>$tenant_id, displayName=>$displayName, notifyEmail=>$notifyEmail, quota=>$quota]");
	    $displayName = trim($displayName);
		if ($this->validateProject($tenant_id, $displayName, $quota)) {
			$project_uid = $this->generateUsername($tenant_id, $displayName);
			$password = Util::randomString(16, self::PASSWORD_SPECIAL_CHARS, true);
			$collaborator = $this->collaboratorManager->createCollaborator($project_uid, $project_uid, $displayName, $password);
			if ($collaborator) {
				$ocUser = $this->collaboratorManager->createOwncloudUser($project_uid, $password);
				if ($ocUser) {
					$ocUser->setDisplayName($displayName);
					// Create project folder
					$projectFolderManager = new ProjectFolderManager($project_uid);
					$projectFolderManager->create($displayName);
					// Create tenant to project user association
					$this->tenantConfigManager->create($tenant_id, TenantConfigManager::PROJECT, $collaborator->getId());
					// Create folder to collaborator to folder association
					$this->projectService->create($collaborator->getId(), self::PROJECTFOLDER, $displayName);
					// Set quota if necessary
					if ($quota) {
						$this->setQuota($project_uid, $quota);
					}
					// Set notification email to noreply
					$this->setEmail($project_uid, self::PROJECTDEFAULTEMAIL);

					return $collaborator;
				}
				$this->collaboratorManager->destroy($tenant_id, $collaborator->getId());
				return false;
			}
		}
	}

	/**
	 * DESTROY a project
	 * @param integer $tenant_id
	 * @param string $project_uid
	 * @return Collaborator
	 */
	public function destroy($tenant_id, $project_uid) {
		$projectUser = $this->find($project_uid);
		$quota = Util::configQuotaToBytes($this->getQuota($projectUser->getUid()));
		$projectFolder = $this->getProjectFolder($project_uid);
		$projectFolderManager = new ProjectFolderManager($projectUser->getUid());
		$projectFolderManager->unshareToAll($projectFolder);
		$tenant_association = $this->tenantConfigManager->findExact($tenant_id, TenantConfigManager::PROJECT, $project_uid);
		if ($tenant_association) {
			$project_options = $this->projectService->findByUser($tenant_association[0]->getConfigValue());
			foreach ($project_options as $option) {
				$this->projectService->destroy($option->getId());
			}
			$this->tenantConfigManager->destroy($tenant_association[0]->getId());
		}
		$collaborator = $this->collaboratorManager->destroy($tenant_id, $project_uid);
		return $collaborator;
	}

	/**
	 * Check to see if a project already exists
	 * @param string $project_uid
	 * @return boolean
	 */
	public function projectExists($project_uid) {
		return ($this->userManager->userExists($project_uid) ? true : false);
	}

	/**
	 * Set a users quota
	 * @param string $project_uid
	 * @param string $quota
	 */
	public function setQuota($project_uid, $quota=null) {
		if (is_null($quota)) {
			$quota = self::PROJECTDEFAULTQUOTA;
		}
		return $this->tenantUserManager->setQuota($project_uid, $quota);
	}

	/**
	 * Set email address for user
	 * @param string $project_uid
	 * @param string $email
	 */
	public function setEmail($project_uid, $email=null) {
		if (is_null($email)) {
			$email = self::PROJECTDEFAULTEMAIL;
		}
		return $this->tenantUserManager->setEmail($project_uid, $email);
	}

	/**
	 * Return the user's storage limit
	 * @param string $uidd
	 * @return string
	 */
	public function getQuota($uid) {
		return $this->tenantUserManager->getQuota($uid);
	}

	/**
	 * Return how much the user has used of their storage limit
	 * @param string $uid
	 * @return integer
	 */
	public function getUsedQuota($uid) {
		$used_quota = $this->tenantUserManager->getStorageSize($uid);
		if ($used_quota < 0) {
			$used_quota = 0;
		}
		return $used_quota;
	}

	/**
	 * Get a list of users on a project
	 * @param int $project_uid
	 * @return Array
	 */
	public function getMembers($project_uid) {
		$projectFolder = $this->getProjectFolder($project_uid);
		$projectUser = $this->find($project_uid);
		$projectFolderManager = new ProjectFolderManager($projectUser->getUid());
		try {
			$users = $projectFolderManager->getShareUsers($projectFolder);
			return $users;
		} catch (NotFoundException $e) {
			throw $e;
		} catch (Exception $e) {
			return Array();
		}
	}

	/**
	 * Add a member to a project
	 * @param integer $project_uid
	 * @param string $uid
	 * @param integer $permissions
	 * @return true
	 */
	public function addMember($tenant_id, $project_uid, $uid, $permissions=null) {
		$projectFolder = $this->getProjectFolder($project_uid);
		$projectUser = $this->findCollaborator($project_uid);
		$projectFolderManager = new ProjectFolderManager($projectUser->getUid());
		$doShare = $projectFolderManager->shareToUser($projectFolder, $projectUser->getUid(), $uid, $permissions);
		return true;
	}

	/**
	 * Remove a member from a project
	 * @param integer $project_uid
	 * @param string $uid
	 * @return boolean
	 */
	public function removeMember($project_uid, $uid) {
		$projectFolder = $this->getProjectFolder($project_uid);
		$projectUser = $this->find($project_uid);
		$projectFolderManager = new ProjectFolderManager($projectUser->getUid());
		$doShare = $projectFolderManager->unshareToUser($projectFolder, $uid);
		return true;
	}

	/**
	 * Return the project folder for a project
	 * @param integer $project_uid
	 * @return string
	 */
	public function getProjectFolder($project_uid) {
		$project = $this->projectService->findWithKey($project_uid, self::PROJECTFOLDER);
		if ($project) {
			return $project->getConfigValue();
		}
		return null;
	}

	/**
	 * Validation for when creating a project
	 * @param integer $tenant_id
	 * @param string $folder
	 * @param string $quota
	 * @return boolean
	*/
	public function validateProject($tenant_id, $folder, $quota=null) {
		if (strlen($folder) == 0) {
			throw new \Exception("Project name is required");
		}
		if (strip_tags($folder) !== $folder) {
			throw new \Exception("Invalid project name");
		}
		if ($tenant = $this->tenantManager->exists($tenant_id)) {
			if (strlen($folder) > 100) {
				throw new \Exception("Project name is too long");
			}
			$username = $this->generateUsername($tenant_id, $folder);
			if ($this->userManager->userExists($username)) {
				throw new \Exception("Invalid name");
			}
		} else {
			throw new \Exception("Invalid Tenant ID");
		}
		return true;
	}

	/**
	 * Generate the project username from user input
	 * @param integer $tenant_id
	 * @param string $folder
	 * @return string|null
	 */
	public function generateUsername($tenant_id, $folder) {
		if ($tenant = $this->tenantManager->exists($tenant_id)) {
			$tenant_code = $tenant->getCode();
			// Remove anything that is not a valid character
			$folder_name = preg_replace('/[^a-zA-Z0-9 _\.@\-\']/', '', $folder);
			// Replace spaces with _
			$folder_name = preg_replace('/ /', '_', $folder_name);
			$username = sprintf("%s%s-%s", self::PROJECTPREFIX, strtolower($tenant_code), $folder_name);
			Util::debugLog("@Project [action=>generateUsername, username=>$username, tenant_id=>$tenant_id, folder=>$folder]");
			return $username;
		} else {
			return null;
		}
	}
}

