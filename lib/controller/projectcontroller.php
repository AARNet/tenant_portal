<?php
namespace OCA\Tenant_Portal\Controller;

use \Exception;
use \InvalidArgumentException;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCA\Tenant_Portal\Managers\TenantManager;

use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Managers\ProjectManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Util;

class ProjectController extends Controller {

	private $tenantManager;
	private $tenantConfigManager;
	private $tenantUserManager;
	private $projectManager;
	private $auditLogManager;
	private $userManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param TenantManager $tenantManager
	 * @param TenantConfigManager $tenantConfigManager
	 * @param TenantUserManager $tenantUserManager
	 * @param ProjectManager $projectManager
	 * @param AuditLogManager $auditLogManager
	 */
	public function __construct($appName, IRequest $request, TenantManager $tenantManager, TenantConfigManager $tenantConfigManager, TenantUserManager $tenantUserManager, ProjectManager $projectManager, AuditLogManager $auditLogManager) {
		parent::__construct($appName, $request);
		$this->tenantManager = $tenantManager;
		$this->tenantConfigManager = $tenantConfigManager;
		$this->tenantUserManager = $tenantUserManager;
		$this->projectManager = $projectManager;
		$this->auditLogManager = $auditLogManager;
		$this->userManager = \OC::$server->getUserManager();
	}

	/**
	* @param integer $tenant_id
	*
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function index($tenant_id) {
		$tenantProjects = $this->tenantConfigManager->findAllWithKey($tenant_id, TenantConfigManager::PROJECT);
		$projects = Array();
		foreach ($tenantProjects as $project) {
			try {
				$project_id = $project->getConfigValue();
				$user = $this->projectManager->find($project_id);
				$folder = $this->projectManager->findOption($project_id, ProjectManager::PROJECTFOLDER);
				$quota = $this->projectManager->getQuota($user->getUid());
				if (!$quota) {
					$this->projectManager->setQuota($user->getUid());
					$quota = $this->projectManager->getQuota($user->getUid());
				}
				$quota = Util::configQuotaToBytes($quota);
				$quota = Util::toHumanFileSize($quota);

				$used_quota = $this->projectManager->getUsedQuota($user->getUid());
				$used_quota = Util::configQuotaToBytes($used_quota);
				$used_quota = Util::toHumanFileSize($used_quota);
				$projects[] = Array(
					"id" => $user->getId(),
					"username" => $user->getUid(),
					"quota" => $quota ? $quota : "N/A",
					"used_quota" => $used_quota ? $used_quota : "N/A",
					"folder" => htmlentities($folder->getConfigValue()),
				);
			} catch (DoesNotExistException $e) {
				Util::debugLog("@ProjectIndex DoesNotExistException: ".$project->getConfigValue());
			}
		}
		return new DataResponse($projects);
	}

	/**
	* @param integer $tenant_id
	* @param string $project_name
	* @param string $quota
	*
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function create($tenant_id, $project_name, $quota=null) {
		if (is_null($quota) || trim($quota) === "" || trim($quota) == "0") {
			$quota = "1 TB";
		}
		$project = $this->projectManager->create($tenant_id, $project_name, null, $quota);
		$this->auditLogManager->logCreate($tenant_id, "Created group drive '$project_name' with quota '".Util::toHumanFilesize($quota)."'");
		return $project;
	}

	/**
	* @param integer $tenant_id
	* @param integer $id
	*
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function destroy($tenant_id, $id) {
		$project = $this->projectManager->destroy($tenant_id, $id);
		$this->auditLogManager->logDelete($tenant_id, "Deleted group drive '".$project->getCn()."' with quota '".Util::toHumanFilesize($quota)."'");
		return $project;
	}

	/**
	* @param integer $tenant_id
	* @param integer $project_id
	*	
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function getMembers($tenant_id, $project_id) {
		$users = $this->projectManager->getMembers($project_id);
		$result = Array();
		foreach ($users as $item) {
			$already_exists = array_filter($result, function($x) use ($item) { return $x["uid"] == $item["share_with"]; });
			if (count($already_exists) === 0) {
				$result[] = Array(
					"uid" => $item["share_with"],
					"permissions" => $item["permissions"],
				);
			}
		}
		return $result;
	}

	/**
	* @param integer $tenant_id
	* @param integer $project_id
	* @param string $uid
	* @param integer $permissions
	*
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function addMember($tenant_id, $project_id, $uid, $permissions) {
		$member = $this->projectManager->addMember($tenant_id, $project_id, $uid, $permissions);
		$projectUser = $this->projectManager->find($project_id);
		$this->auditLogManager->logUpdate($tenant_id, "Added '$uid' to group drive '".$projectUser->getCn()."'");
		return $member;
	}

	/**
	* @param integer $tenant_id
	* @param integer $project_id
	* @param string $uid
	*
	* @NoAdminRequired
	* @AuthorisedTenantUser
	*/
	public function removeMember($tenant_id, $project_id, $uid) {
		$member = $this->projectManager->removeMember($project_id, $uid);
		$projectUser = $this->projectManager->find($project_id);
		$this->auditLogManager->logUpdate($tenant_id, "Removed '$uid' from group drive '".$projectUser->getCn()."'");
		return $member;
	}
	/**
	 * Set quota for tenant
	 *
	 * @param integer $tenant_id
	 * @param string $quota
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function setQuota($tenant_id, $project_id, $quota) {
		$collaborator = $this->projectManager->findCollaborator($project_id);
		$getQuota = Util::toHumanFilesize($this->projectManager->getQuota($collaborator->getUid()));
		$setQuota = $this->projectManager->setQuota($collaborator->getUid(), $quota);
		$this->auditLogManager->logUpdate($tenant_id, "Group Drive quota for '{$collaborator->getCn()}' changed from '$getQuota' to '".Util::toHumanFilesize($quota)."'");
		return new DataResponse(Array("user" => $collaborator->getUid(), "success"=>$setQuota));
	}

}
