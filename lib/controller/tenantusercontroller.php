<?php

namespace OCA\Tenant_Portal\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;

class TenantUserController extends Controller {

	protected $tenantUserManager;
	protected $auditLogManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param AuditLogManager $auditLogManager
	 * @param TenantUserManager $tenantUserManager
	 */
	public function __construct($appName, IRequest $request, AuditLogManager $auditLogManager, TenantUserManager $tenantUserManager) {
		parent::__construct($appName, $request);
		$this->auditLogManager = $auditLogManager;
		$this->tenantUserManager = $tenantUserManager;
	}

	/**
	 * Returns a list of authorised users for
	 * a tenant
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function index($tenant_id) {
                return new DataResponse(
                         Array(
                                "admin" => Util::isAdmin(Util::currentUser()),
                                "authorised_users" => $this->tenantUserManager->findAllByTenant($tenant_id)
                        )
                );
	}

	/**
	 * Create a new authorised unit for a tenant
	 *
	 * @param integer $tenant_id
	 * @param string $user_id
	 * @return TenantUser
	 *
	 * @AuthorisedTenantUser
	 */
	public function create($tenant_id, $user_id) {
		$user = $this->tenantUserManager->create($tenant_id, $user_id);
		$this->auditLogManager->logCreate($tenant_id, "Added '$user_id' as authorised user");
		return $user;
	}

	/**
	 * Removes an authorised user from a tenant
	 *
	 * @param integer $id
	 * @return TenantUser
	 *
	 * @AuthorisedTenantUser
	 */
	public function destroy($id) {
		$user = $this->tenantUserManager->destroy($id);
		$this->auditLogManager->logDelete($user->getTenantId(),  "Removed '{$user->getUserId()}' as authorised user");
		return $user;
	}

}
