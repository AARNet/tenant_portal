<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use \OCP\AppFramework\Db\DoesNotExistException;
use \OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;

use \OCA\Tenant_Portal\Db\TenantConfig;
use \OCA\Tenant_Portal\Service\TenantConfigService;
use \OCP\IUserManager;
use \OCP\IGroupManager;
use \OCA\Tenant_Portal\Service\Exceptions\NotFoundException;

class UserGroupService {
	private $service;
	private $groupManager;
	private $userManager;
	private $tenantManager;

	/**
	 * @param TenantConfigMapper $service
	 */
	public function __construct(TenantConfigService $service, IUserManager $userManager, IGroupManager $groupManager, TenantManager $tenantManager) {
		$this->service = $service;
		$this->groupManager = $groupManager;
		$this->userManager = $userManager;
		$this->tenantManager = $tenantManager;
	}

	/**
	 * @param interger $id
	 */
	public function find($id) {
		return $this->service->find($id);
	}

	/**
	 * @param integer $tenant_id
	 */
	public function findAllByTenant($tenant_id) {
		$result = Array();
		$groups = $this->service->findAllByKey($tenant_id, TenantConfigManager::USERGROUP);
		foreach ($groups as $group) {
			try {
				$object = $this->findByName($tenant_id, $group->getConfigValue());
				if ($object) {
					$result[] = $group;
				}
			} catch (NotFoundException $e) { }
		}
		return $groups;
	}

	/*
	 * @param integer $tenant_id
	 * @param string $value
	 */
	public function findByName($tenant_id, $value) {
		$group = $this->service->findByAll($tenant_id, TenantConfigManager::USERGROUP, $value);
		if (count($group)) {
			if ($tenant = $this->tenantManager->exists($tenant_id)) {
				$tenantShortCode = $tenant->getCode();
				$groupName = sprintf("%s-%s", $tenantShortCode, $group[0]->getConfigValue());
				if ($this->groupManager->groupExists($groupName)) {
					$group = $this->groupManager->get($groupName);
					return $group;
				}
			}
			throw new NotFoundException('Group not found on tenant');
		}
	}


	/**
	 * @param integer $tenant_id
	 * @param string $value
	 */
	public function groupExists($tenant_id, $groupName) {
		$existsTenantConfig = $this->service->configExists($tenant_id, TenantConfigManager::USERGROUP, $groupName);
		$existsOwncloudGroup = $this->groupManager->groupExists($groupName);
		return ($existsTenantConfig || $existsOwncloudGroup);
	}

	/**
	 * @param integer $tenant_id
	 * @param string $value
	 */
	public function create($tenant_id, $value) {
		if ($tenant = $this->tenantManager->exists($tenant_id)) {
			$tenantShortCode = $tenant->getCode();
			// add tenant short code to group name
			$groupName = sprintf("%s-%s", $tenantShortCode, $value);

			if (!$this->groupExists($tenant_id, $groupName)) {
				$createGroup = $this->groupManager->createGroup($groupName);
				if ($createGroup) {
					return $this->service->create($tenant_id, TenantConfigManager::USERGROUP, $groupName);
				}
			}
		}
		throw new Exception('Something went wrong creating the User Group');
	}

	/**
	 * Update a config option
	 *
	 * @param integer $tenant_id
	 * @param string $value
	 */
	public function update($tenant_id, $value) {
		return $this->service->update($tenant_id, TenantConfigManager::USERGROUP, $value);
	}

	/**
	 * Delete a config option
	 * @param integer $id
	 */
	public function delete($tenant_id, $id) {
		$config = $this->service->find($id);
		$tenant = $this->tenantManager->exists($config->getTenantId());
		$groupName = $config->getConfigValue();
		$group = $this->groupManager->get($groupName);
		$result = null;
		if ($group) {
			if ($group->delete()) {
				$result = $this->service->delete($id);
			}
		}
		return $result;
	}

	/**
	 * Returns an array of usernames for the members of a group
	 * @param int $tenant_id
	 * @param int $id
	 * @return Array
	 */
	public function show($tenant_id, $id) {
		$config = $this->service->find($id);
		$groupName = $config->getConfigValue();
		$group = $this->groupManager->get($groupName);
		if ($group) {
			$groupMembers = $group->getUsers();
			$users = array_keys($groupMembers);
		} else {
			$users = Array();
		}
		if (count($users)) {
			$users = array_map(function ($u) { return Array("username" => $u); }, $users);
		}
		return $users;
	}

	/**
	 * Add member to a user group
	 * @param int $tenant_id
	 * @param int $group_id
	 * @param string $user
	 * @return boolean
	 */
	public function addMember($tenant_id, $group_id, $user) {
		$groupConfig = $this->service->find($group_id);
		$groupName = $groupConfig->getConfigValue();
		$user = Util::getRealUID($user);
		if ($this->groupManager->isInGroup($user, $groupName)) {
			return true;
		} else {
			$ocUser = $this->userManager->get($user);
			$ocGroup = $this->groupManager->get($groupName);
			if ($ocUser) {
				$ocGroup->addUser($ocUser);
				return true;
			} else {
				throw new NotFoundException('Unable to add member to group as the user does not exist.');
			}
		}
		throw new Exception('Something went wrong adding the user to the group.') ;
	}

	/**
	 * Remove member from a user group
	 * @param int $tenant_id
	 * @param int $group_id
	 * @param string $user
	 * @return boolean
	 */
	public function removeMember($tenant_id, $group_id, $user) {
		$groupConfig = $this->service->find($group_id);
		$groupName = $groupConfig->getConfigValue();
		$user = Util::getRealUID($user);
		if ($this->groupManager->isInGroup($user, $groupName)) {
			$ocUser = $this->userManager->get($user);
			$ocGroup = $this->groupManager->get($groupName);
			if ($ocUser) {
				return $ocGroup->removeUser($ocUser);
			}
		}
		return false;
	}
}
