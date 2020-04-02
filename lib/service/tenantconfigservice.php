<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\Tenant_Portal\Service\Exceptions\NotFoundException;

use OCA\Tenant_Portal\Util;
use OCA\Tenant_Portal\Db\TenantConfig;
use OCA\Tenant_Portal\Db\TenantConfigMapper;
use OCA\Tenant_Portal\Managers\TenantManager;
use OCA\Tenant_Portal\Managers\TenantConfigManager;

class TenantConfigService {
	private $mapper;

	/**
	 * @param TenantConfigMapper $mapper
	 */
	public function __construct(TenantConfigMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Return all config options for a tenant
	 * @param integer $tenant_id
	 */
	public function findAllByTenant($tenant_id) {
		return $this->mapper->findAllByTenant($tenant_id);
	}

	/**
	 * Return config option of a certain type for a tenant
	 * @param integer $tenant_id
	 * @param string $key
	 */
	public function findByKey($tenant_id, $key) {
		return $this->mapper->findByKey($tenant_id, $key);
	}

	/**
	 * Return all config options of a certain type for a tenant
	 * @param integer $tenant_id
	 * @param string $key
	 */
	public function findAllWithKey($tenant_id, $key) {
		return $this->mapper->findAllByKey($tenant_id, $key);
	}
	// @deprecated use findAllWithKey()
	public function findAllByKey($tenant_id, $key) {
		return $this->findAllWithKey($tenant_id, $key);
	}

	/**
	 * Return all config options of a certain type for a tenant
	 * @param integer $tenant_id
	 * @param string $key
	 */
	public function findExact($tenant_id, $key, $value) {
		return $this->mapper->findByAll($tenant_id, $key, $value);
	}
	// @deprecated use findExact()
	public function findByAll($tenant_id, $key, $value) {
		return $this->findExact($tenant_id, $key, $value);
	}

	/**
	 * Returns all config options for all tenants with a certain config key
	 */
	public function findAllValues($key) {
		return $this->mapper->findAllValues($key);
	}

	/**
	 * See if a config already exists on a tenant
	 *
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 */
	public function configExists($tenant_id, $key, $value=null) {
		if ($value) {
			return count($this->mapper->findByAll($tenant_id, $key, $value)) ? true : false;
		} else {
			return count($this->mapper->findAllByKey($tenant_id, $key)) ? true : false;
		}
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
	 * Return a single config option by id
	 * @param integer $id
	 */
	public function find($id) {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Create a new config option
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 */
	public function create($tenant_id, $key, $value) {
		$configManager = new TenantConfigManager();
		if (!$configManager->validate($tenant_id, $key, $value)) {
			throw new Exception('Invalid config key or value');
		}

		$user = Util::currentUser();
		Util::debugLog("@TenantConfig [user=>$user, tenant=>$tenant_id, action=>create, config_key=>$key, config_value=>$value]");

		$tenantConfig = new TenantConfig();
		$tenantConfig->setTenantId($tenant_id);
		$tenantConfig->setConfigKey($key);
		$tenantConfig->setConfigValue($value);
		return $this->mapper->insert($tenantConfig);
	}


	/**
	 * Update a config option
	 *
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 */
	public function update($tenant_id, $key, $value) {
		$configManager = new TenantConfigManager($tenant_id);
		if (!$configManager->validate($tenant_id, $key, $value)) {
			throw new Exception('Invalid config key or value');
		}

		$user = Util::currentUser();
		try {
			$toUpdate = $this->mapper->findByKey($tenant_id, $key);
			Util::debugLog("@TenantConfig [user=>$user, tenant=>$tenant_id, action=>update, config_key=>$key, config_value=>".$toUpdate->getConfigValue().", new_config_value=>$value]");
			$toUpdate->setConfigValue($value);
			return $this->mapper->update($toUpdate);
		} catch (DoesNotExistException $e) {
			return $this->create($tenant_id, $key, $value);
		}
		catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Delete a config option
	 * @param integer $id
	 */
	public function delete($id) {
		try {
			$tenantConfig = $this->mapper->find($id);
			$user = Util::currentUser();
			Util::debugLog("@TenantConfig [user=>$user, tenant=>".$tenantConfig->getTenantId().", action=>delete, config_key=>".$tenantConfig->getConfigKey().", config_value=>".$tenantConfig->getConfigValue()."]");
			$this->mapper->delete($tenantConfig);
			return $tenantConfig;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
