<?php

namespace OCA\Tenant_Portal\Controller;

use \OCP\IRequest;
use \OCP\IGroupManager;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCA\Tenant_Portal\Service\TenantService;
use \OCA\Tenant_Portal\Service\UserGroupService;
use \OCA\Tenant_portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Util;

class UserGroupController extends Controller {

	protected $service;
	protected $auditLogManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param UserGroupService $configService
	 * @param IGroupManager $groupManager
	 * @param AuditLogManager $auditLogManager
	 */
	public function __construct($appName, IRequest $request, UserGroupService $configService, IGroupManager $groupManager, AuditLogManager $auditLogManager) {
		parent::__construct($appName, $request);
		$this->service = $configService;
		$this->groupManager = $groupManager;
		$this->auditLogManager = $auditLogManager;
	}

	/**
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function index($tenant_id) {
		return new DataResponse($this->service->findAllByTenant($tenant_id));
	}

	/**
	 * Creates a user group
	 *
	 * @param integer $tenant_id
	 * @param integer $name
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function create($tenant_id, $group) {
		$ugroup = $this->service->create($tenant_id, $group);
		$this->auditLogManager->logCreate($tenant_id, sprintf("Created user group '%s'", $group));
		return $ugroup;
	}

	/**
	 * Delete a user group
	 *
	 * @param integer $tenant_id
	 * @param integer $id
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function destroy($tenant_id, $id) {
		$ugroup = $this->service->delete($tenant_id, $id);
		$this->auditLogManager->logDelete($tenant_id, sprintf("Removed user group '%s'", $group));
		return $ugroup;
	}	

	/**
         *
         * @param integer $tenant_id
         * @param integer $id
         *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function show($tenant_id, $id) {
		return new DataResponse($this->service->show($tenant_id, $id));
	}	

	/**
         *
         * @param integer $tenant_id
         * @param integer $group_id
	 * @param string $user
         *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function addMember($tenant_id, $group_id, $user) {
		$member = $this->service->addMember($tenant_id, $group_id, $user);
		$ugroup = $this->service->find($group_id);
		$this->auditLogManager->logUpdate($tenant_id, sprintf("Added '%s' to user group '%s'", $user, $ugroup->getConfigValue()));
		return $member;
	}

	/**
         *
         * @param integer $tenant_id
         * @param integer $group_id
         * @param string $user
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function removeMember($tenant_id, $group_id, $user) {
		$member = $this->service->removeMember($tenant_id, $group_id, $user);
		$ugroup = $this->service->find($group_id);
		$this->auditLogManager->logUpdate($tenant_id, sprintf("Removed '%s' from user group '%s'", $user, $ugroup->getConfigValue()));
		return $member;
	}
}
