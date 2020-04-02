<?php

namespace OCA\Tenant_Portal\Managers;

use OCA\Tenant_Portal\Util;

use OCA\Tenant_Portal\Managers\TenantUserManager;
use OCA\Tenant_Portal\Managers\TenantConfigManager;

class TenantManager {

	private $urlGenerator;
	private $tenantService;
	private $tenantUserService;
	private $tenantConfigService;
	private $tenantUserManager;
	private $tenantConfigManager;

	public function __construct() {
		$this->tenantService = Util::getTenantService();
		$this->urlGenerator = Util::getUrlGenerator();
		$this->tenantUserService = Util::getTenantUserService();
		$this->tenantConfigService = Util::getTenantConfigService();
		$this->userManager = Util::getUserManager();
		$this->tenantUserManager = Util::getTenantUserManager();
		$this->tenantConfigManager = Util::getTenantConfigManager();
	}

	/**
	 * Create new tenant
	 * @param string $name
	 * @param string $code
	 * @return Tenant
	 */
	public function create($name, $code) {
		$tenant = $this->tenantService->create($name, $code);
		return $tenant;
	}

	/**
	 * Find all tenants
	 * @return Entity[]
	 */
	public function findAll() {
		return $this->tenantService->findAll();
	}

	/**
	 * Delete a tenant
	 * @param integer $id
	 * @return Entity
	 */
	public function destroy($id) {
		$tenant = $this->tenantService->delete($id);
		return $tenant;
	}

	/**
	 * Check to see if the tenant exists
	 * @param integer tenant id
	 * @return boolean
	 */
	public function exists($tenant_id) {
		try {
			return $this->tenantService->find($tenant_id);
		} catch (Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Retrieve a tenant
	 * @param integer $tid tenant id
	 * @return Tenant
	 */
	public function find($tenant_id) {
		return $this->tenantService->find($tenant_id);
	}
	public function get($tenant_id) {
		return $this->find($tenant_id);
	}

	/**
	 * Retrieve a tenant view url
	 * @param integer $tid tenant id
	 * @return string
	 */
	public function getUrl($tid) {
		return $this->urlGenerator->linkToRoute('tenant_portal.show',["tenant_id"=>$tid]);
	}

	/**
	 * Get the tenant id a user is authorised on
	 * @param integer $uid user id
	 * @return integer
	 */
	public function getIdByUser($uid) {
		return $this->tenantUserManager->findTenantId($uid);
	}

	/**
	 * Returns the quota set for a Tenant
	 * @param int $tenant_id
	 * @return int
	 */
	public function getQuota($tenant_id) {
		try {
			$quota = $this->tenantConfigManager->findByKey($tenant_id, 'quota');
			return $quota->getConfigValue();
		} catch (\Exception $e) {
			return 0;
		}
	}

	/**
	 * Returns the list of domains assinged to a tenant
	 * @param integer $tenant_id
	 * @return Array
	 */
	public function getDomains($tenant_id) {
		return $this->tenantConfigManager->getDomains($tenant_id);
	}

	/**
	 * Checks to see if a user is assigned to the tenant
	 * @param int $tenant_id
	 * @param string $user_id
	 * @return OC\User|false
	 */
	public function hasUser($tenant_id, $user_id) {
		if ($user = $this->tenantUserManager->isDomainUser($tenant_id, $user_id)) {
			return $user;
		}
		if ($user = $this->tenantUserManager->isAdditionalUser($tenant_id, $user_id)) {
			return $user;
		}
		if ($user = $this->tenantUserManager->isCollaboratorUser($tenant_id, $user_id)) {
			return $user;
		}
		if ($user = $this->tenantUserManager->isProjectUser($tenant_id, $user_id)) {
			return $user;
		}
		return false;
	}

	/**
	 * @param int $tenant_id
	 * @return TenantUserManager
	 */
	public function getUserManager() {
		return $this->tenantUserManager;
	}

	/**
	 * @param int $tenant_id
	 * @return TenantConfigManager
	 */
	public function getConfigManager() {
		return $this->tenantConfigManager;
	}
}
