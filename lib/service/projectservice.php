<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\Tenant_Portal\Service\Exceptions\NotFoundException;

use OCA\Tenant_Portal\Db\Project;
use OCA\Tenant_Portal\Db\ProjectMapper;

use OCA\Tenant_Portal\Util;

class ProjectService {
	private $mapper;

	/**
	 * @param ProjectMapper $mapper
	 */
	public function __construct(ProjectMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Return a single Project option by ID
	 * @param int $id
	 * @return Project
	 */
	public function find($id) {
		return $this->mapper->find($id);
	}

	/**
	 * Return all Project options for a particular key
	 * @param int $id
	 * @return Project
	 */
	public function findAllValues($key) {
		return $this->mapper->findAllValues($key);
	}

	/**
	 * Return a single Project option of a specific
	 * type for a tenant
	 * @param int $id
	 * @param string $key
	 * @return Project
	 */
	public function findWithKey($id, $key) {
		return $this->mapper->findWithKey($id, $key);
	}

	/**
	 * Return all Project options of a specific
	 * type for a tenant
	 * @param int $id
	 * @param string $key
	 * @return Project
	 */
	public function findAllWithKey($id, $key) {
		return $this->mapper->findAllWithKey($id, $key);
	}

	/**
	 * Check to see if a config row exists
	 * @param int $id tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Project
	 */
	public function findExact($id, $key, $value) {
		return $this->mapper->findExact($id, $key, $value);
	}

	/**
	 * Return all the config options for a particular user
	 * @param int $id
	 * @return Project
	 */
	public function findByUser($id) {
		return $this->mapper->findByUser($id);
	}

	/**
	 * Create a new config option
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Project
	 */
	public function create($user_id, $key, $value) {
		$user = Util::currentUser();
		Util::debugLog("@Project [user=>$user, action=>create, project_id=>$user_id, config_key=>$key, config_value=>$value]");

		$entity = new Project();
		$entity->setUserId($user_id);
		$entity->setConfigKey($key);
		$entity->setConfigValue(strip_tags($value));
		return $this->mapper->insert($entity);
	}


	/**
	 * Update a config option
	 *
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $value
	 * @return Project
	 */
	public function update($user_id, $key, $value) {
		$user = Util::currentUser();
		try {
			$toUpdate = $this->findWithKey($user_id, $key);
			Util::debugLog("@Project [user=>$user, project_id=>$user_id, action=>update, config_key=>$key, config_value=>".$toUpdate->getConfigValue().", new_config_value=>$value]");
			$toUpdate->setConfigValue($value);
			return $this->mapper->update($toUpdate);
		} catch (DoesNotExistException $e) {
			return $this->create($user_id, $key, strip_tags($value));
		}
		catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Delete a config option
	 * @param integer $id
	 * @return Project
	 */
	public function destroy($id) {
		try {
			$entity = $this->mapper->find($id);
			$user = Util::currentUser();
			Util::debugLog("@Project [user=>$user, project_id=>".$entity->getUserId().", action=>delete, config_key=>".$entity->getConfigKey().", config_value=>".$entity->getConfigValue()."]");
			$this->mapper->delete($entity);
			return $entity;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
