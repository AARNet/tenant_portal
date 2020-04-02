<?php

namespace OCA\Tenant_Portal\Managers;

use \OCA\Tenant_Portal\Util;

class TenantConfigManager {
	const ADDITIONALUSER 	= 'additional_user';
	const DOMAIN = 'domain';
	const USERGROUP=  'user_group';
	const PROJECT = 'project';
	const QUOTA = 'quota';
	const COLLABORATOR = 'collaborator';
	const IMPERSONATE = 'impersonate';

	private $userManager;
	private $groupManager;
	private $collaboratorManager;
	private $tenantConfigService;
	private $tenantId;

	public function __construct() {
		$this->userManager = Util::getUserManager();
		$this->groupManager = Util::getGroupManager();
		$this->collaboratorManager = Util::getCollaboratorManager();
		$this->tenantConfigService = Util::getTenantConfigService();
	}

	/**
	 * Create a config option
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Entity
	 */
	public function create($tenant_id, $key, $value) {
		return $this->tenantConfigService->create($tenant_id, $key, $value);
	}

	/**
	 * Find all config options with aparticular key
	 * @param integer $tenant_id
	 * @param string $key
	 * @return Entity
	 */
	public function findAllWithKey($tenant_id, $key) {
		return $this->tenantConfigService->findAllWithKey($tenant_id, $key);
	}

	/**
	 * Find ane xact match for a config option
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Entity
	 */
	public function findExact($tenant_id, $key, $value) {
		return $this->tenantConfigService->findExact($tenant_id, $key, $value);
	}

	/**
	 * Find ane xact match for a config option
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Entity
	 */
	public function findByKey($tenant_id, $key) {
		return $this->tenantConfigService->findByKey($tenant_id, $key);
	}

	/**
	 * Delete a config option
	 * @param integer $id
	 * @return Entity
	 */
	public function destroy($id) {
		return $this->tenantConfigService->delete($id);
	}

	/**
	 * Update a config option
	 * @param integer $id
	 * @return Entity
	 */
	public function update($tenant_id, $config_key, $config_value) {
		return $this->tenantConfigService->update($tenant_id, $config_key, $config_value);
	}

	/**
	 * Validate a config value based on it's key type
	 * @param string $key config key type
	 * @param string $value config value
	 * @return boolean
	 */
	public function validate($tenant_id, $key, $value=null) {
		$configExists = $this->tenantConfigService->configExists($tenant_id, $key, $value);

		if (trim($key) === '') {
			return FALSE;
		}

		if (strip_tags($value) != $value) {
			return FALSE;
		}

		switch ($key) {
			case self::USERGROUP:
				if (!$configExists) {
					return TRUE;
				}
				break;
			case self::ADDITIONALUSER:
				if ($this->userManager->userExists($value) && !$configExists) {
					return TRUE;
				}
				break;
			case self::DOMAIN:
				if (preg_match("/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/", $value) && !$configExists) {
					return TRUE;
				}
				break;
			case self::QUOTA:
				$bytes = Util::configQuotaToBytes($value);
				return ($bytes >= 0 ? TRUE : FALSE);
				break;
			case self::COLLABORATOR:
				if (!$configExists) {
					return TRUE;
				}
				break;
			case self::PROJECT:
				return TRUE;
				break;
			case self::IMPERSONATE:
				return TRUE;
				break;
		}
		return FALSE;
	}

	/**
	 * Returns an array of domains assigned to the tenant
	 * @param int $tenant_id
	 * @return Array
	 */
	public function getDomains($tenant_id) {
		$tenantConfigService = Util::getTenantConfigService();
		$config = $tenantConfigService->findAllByKey($tenant_id, self::DOMAIN);
		$domains = array_map(function($v) { return $v->getConfigValue(); }, $config);
		return $domains;
	}

	/**
	 * Returns an array of additional users assigned to the tenant
	 * @param int $tenant_id
	 * @return TenantConfig[]
	 */
	public function getAdditionalUsers($tenant_id) {
		$tenantConfigService = Util::getTenantConfigService();
		$config = $tenantConfigService->findAllByKey($tenant_id, self::ADDITIONALUSER);
		return $config;
	}

	/**
	 * Returns an array of collaborator users assigned to the tenant
	 * @param int $tenant_id
	 * @return TenantConfig[]
	 */
	public function getCollaboratorUsers($tenant_id) {
		$tenantConfigService = Util::getTenantConfigService();
		$config = $tenantConfigService->findAllByKey($tenant_id, self::COLLABORATOR);
		foreach ($config as $collab) {
			$uid = $this->collaboratorManager->find($collab->getConfigValue());
			$collab->setConfigValue($uid->getUid());
		}
		return $config;
	}

	/**
	 * Returns an array of project users assigned to the tenant
	 * @param int $tenant_id
	 * @return TenantConfig[]
	 */
	public function getProjectUsers($tenant_id) {
		$tenantConfigService = Util::getTenantConfigService();
		$config = $tenantConfigService->findAllByKey($tenant_id, self::PROJECT);
		foreach ($config as $project) {
			$uid = $this->collaboratorManager->find($project->getConfigValue());
			$project->setConfigValue($uid->getUid());
		}
		return $config;
	}

	/**
	 * Returns current impersonate status
	 */
	public function getImpersonateStatus($tenant_id) {
		$tenantConfigService = Util::getTenantConfigService();
		if ($tenantConfigService->configExists($tenant_id, self::IMPERSONATE)) {
			$config = $tenantConfigService->findByKey($tenant_id, self::IMPERSONATE);
			$value = $config->getConfigValue();
			if ($value === "false") {
				return false;
			}
		}
		return true;
	}

}
