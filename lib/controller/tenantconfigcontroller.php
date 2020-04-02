<?php

namespace OCA\Tenant_Portal\Controller;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCA\Tenant_Portal\Service\NotFoundException;
use \OCA\Tenant_Portal\Util;

use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;

class TenantConfigController extends Controller {

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
		$this->auditLogManager = $auditLogManager;
		$this->tenantManager = $tenantManager;
		$this->tenantConfigManager = $tenantConfigManager;
	}

	/**
	 * Returns an array of domain configs for a tenant
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function getAssignedDomains($tenant_id) {
		return new DataResponse(
			Array(
				"admin" => Util::isAdmin(Util::currentUser()),
				"domains" => $this->tenantConfigManager->findAllWithKey($tenant_id, TenantConfigManager::DOMAIN)
			)
		); 
	}

	/**
	 * Set quota for tenant
	 *
	 * @param integer $tenant_id
	 * @param string $quota
	 * @return DataResponse
	 *
	 * @AuthorisedTenantUser
	 */
	public function setQuota($tenant_id, $quota) {
		$bytes = Util::configQuotaToBytes($quota);
		$humanBytes = Util::toHumanFilesize($quota);
		$getQuota = Util::toHumanFilesize($this->tenantManager->getQuota($tenant_id));	
		$setQuota = $this->tenantConfigManager->update($tenant_id, TenantConfigManager::QUOTA, $bytes);
		$this->doAuditLog($tenant_id, 'update', TenantConfigManager::QUOTA, $humanBytes, $getQuota);
		return new DataResponse($setQuota);
	}

	/**
	 * Toggle Impersonate for tenant
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @AuthorisedTenantUser
	 */
	public function toggleImpersonate($tenant_id) {
		$status = $this->tenantConfigManager->getImpersonateStatus($tenant_id);
		if ($status) {
			$toggleTo = "false";
			$log = "Disabled";
		} else {
			$toggleTo = "true";
			$log = "Enabled";
		}
		$config = $this->tenantConfigManager->update($tenant_id, TenantConfigManager::IMPERSONATE, $toggleTo);
		$this->doAuditLog($tenant_id, 'update', TenantConfigManager::IMPERSONATE, "$log");
		return new DataResponse($config);
	}

	/**
	 * Creates a tenant config option
	 *
	 * @param integer $tenant_id
	 * @param string $name
	 * @param string $value
	 * @return TenantConfig
	 *
	 * @AuthorisedTenantUser
	 */
	public function create($tenant_id, $name, $value) {
		$config = $this->tenantConfigManager->create($tenant_id, $name, $value);
		$this->doAuditLog($tenant_id, 'create', $name, $value);
		return $config;
	}

	/**
	 * Remove a tenant config option
	 *
	 * @param int $id
	 * @return TenantConfig
	 *
	 * @AuthorisedTenantUser
	 */
	public function destroy($id) {
		$config = $this->tenantConfigManager->destroy($id);
		$this->doAuditLog($config->getTenantId(), 'delete', $config->getConfigKey(), $config->getConfigValue());
		return $config;
	}

	/**
	* Update a tenant config option
	*
	* @param int $id
	* @param string $name
	* @param string $value
	* @return TenantConfig
	*
	* @AuthorisedTenantUser
	*/
	public function update($id, $name, $value) {
		$config = $this->tenantConfigManager->update($id, $name, $value);
		$this->auditLogManager->logUpdate($tenant_id, "Config '{$config->getConfigKey()}' with value '{$config->getConfigValue()}' removed");
		return $config;
	}

	/**
	 * Log the change appropriately
	 * @param integer $tenant_id
	 * @param string $action
	 * @param string $name
	 * @param string $value
	 * @param sting $additional
	 */
	private function doAuditLog($tenant_id, $action, $name, $value, $additional=null) {
		switch ($name) {
			case TenantConfigManager::QUOTA:
				$this->auditLogManager->logUpdate($tenant_id, "Changed purchased quota from '$additional' to '$value'");
				break;
			case TenantConfigManager::IMPERSONATE:
				$this->auditLogManager->logUpdate($tenant_id,  "$value impersonate functionality");
				break;
			case TenantConfigManager::ADDITIONALUSER:
				switch ($action) {
					case 'create':
						$this->auditLogManager->logCreate($tenant_id,  "Assigned user '$value'");
						break;
					case 'delete':
						$this->auditLogManager->logDelete($tenant_id,  "Removed user '$value'");
						break;
				}
				break;
			case TenantConfigManager::DOMAIN:
				switch ($action) {
					case 'create':
						$this->auditLogManager->logCreate($tenant_id,  "Assigned domain '$value'");
						break;
					case 'delete':
						$this->auditLogManager->logDelete($tenant_id,  "Removed domain '$value'");
						break;
				}
				break;
		}
	}
}
