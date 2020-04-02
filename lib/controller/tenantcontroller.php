<?php

namespace OCA\Tenant_Portal\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCA\Tenant_Portal\Service\TenantService;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;

class TenantController extends Controller {

	const DEFAULT_PURCHASED_QUOTA=10995116277760; // 10 TB

	protected $tenantManager;
	protected $tenantConfigManager;
	protected $auditLogManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param TenantManager $tenantManager
	 * @param TenantConfigManager $tenantConfigManager
	 * @param AuditLogManager $auditLogManager
	 */
	public function __construct($appName, IRequest $request, TenantManager $tenantManager, TenantConfigManager $tenantConfigManager, AuditLogManager $auditLogManager) {
		parent::__construct($appName, $request);
		$this->tenantManager = $tenantManager;
		$this->tenantConfigManager = $tenantConfigManager;
		$this->auditLogManager = $auditLogManager;
	}

	/**
	 * Returns a list of tenants
	 * @return DataResponse
	 *
	 * @AuthorisedTenantUser
	 */
	public function index() {
		return new DataResponse($this->tenantManager->findAll());
	}


	/**
	 * Returns a tenant
	 *
	 * @param int $id
	 * @return Tenant
	 *
	 * @AuthorisedTenantUser
	 */
	public function show($id) {
		return $this->tenantManager->find($id);
	}

	/**
	 * Create a new tenant
	 *
	 * @param string $name
	 * @param string $code
	 * @return Tenant
	 *
	 * @AuthorisedTenantUser
	 */
	public function create($name, $code) {
		$tenant = $this->tenantManager->create($name, $code);
		$this->tenantConfigManager->create($tenant->getId(), 'quota', self::DEFAULT_PURCHASED_QUOTA);
		$this->auditLogManager->logCreate($tenant->getId(), "Created tenant '$name' with short code '$code'");
		return $tenant;
	}

	/**
	 * Remove a tenant
	 *
	 * @param int $id
	 * @return Tenant
	 * @AuthorisedTenantUser
	 */
	public function destroy($id) {
		$tenant = $this->tenantManager->destroy($id);
		$this->auditLogManager->logDelete($tenant->getId(), "Deleted tenant '{$tenant->getName()}' with short code '{$tenant->getCode()}'");
		return $tenant;
	}
}
