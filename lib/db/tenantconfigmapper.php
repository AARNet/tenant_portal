<?php

namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class TenantConfigMapper extends Mapper {

	static private $table = 'tp_tenant_config';
	static private $entity = '\OCA\Tenant_Portal\Db\TenantConfig';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Return a single TenantConfig option by ID
	 *
	 * @param int $id
	 * 
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Return all TenantConfig options for a particular key
	 *
	 * @param int $id
	 */
	public function findAllValues($key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE config_key = ?";
		return $this->findEntities($sql, [$key]);
	}

	/**
	 * Return all TenantConfig options for a tenant
	 *
	 * @param int $id
	 */
	public function findAllByTenant($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ?";
		return $this->findEntities($sql, [$id]);
	}

	/**
	 * Return a single TenantConfig option of a specific 
	 * type for a tenant
	 *
	 * @param int $id
	 * @param string $key
	 */
	public function findByKey($id, $key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? AND lower(config_key) = lower(?)";
		return $this->findEntity($sql, [$id, $key]);
	}

	/**
	 * Return all TenantConfig options of a specific 
	 * type for a tenant
	 *
	 * @param int $id
	 * @param string $key
	 */
	public function findAllByKey($id, $key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? AND lower(config_key) = lower(?)";
		return $this->findEntities($sql, [$id, $key]);
	}

	/**
	 * Check to see if a config row exists
	 *
	 * @param int $id tenant_id
	 * @param string $key
	 * @param string $value
	 */
	public function findByAll($id, $key, $value) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? AND lower(config_key) = lower(?) AND lower(config_value) = lower(?)";
		return $this->findEntities($sql, [$id, $key, $value]);
	}
}
