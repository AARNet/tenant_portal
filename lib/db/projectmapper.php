<?php

namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class ProjectMapper extends Mapper {

	static private $table = 'tp_project';
	static private $entity = '\OCA\Tenant_Portal\Db\Project';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Return a single Project option by ID
	 *
	 * @param int $id
	 *
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Return all Project options for a particular key
	 *
	 * @param int $id
	 */
	public function findAllValues($key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE config_key = ?";
		return $this->findEntities($sql, [$key]);
	}

	/**
	 * Return a single Project option of a specific
	 * type for a tenant
	 *
	 * @param int $id
	 * @param string $key
	 */
	public function findWithKey($id, $key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE user_id = ? AND lower(config_key) = lower(?)";
		return $this->findEntity($sql, [$id, $key]);
	}

	/**
	 * Return all Project options of a specific
	 * type for a tenant
	 *
	 * @param int $id
	 * @param string $key
	 */
	public function findAllWithKey($id, $key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE user_id = ? AND lower(config_key) = lower(?)";
		return $this->findEntities($sql, [$id, $key]);
	}

	/**
	 * Check to see if a config row exists
	 *
	 * @param int $id tenant_id
	 * @param string $key
	 * @param string $value
	 */
	public function findExact($id, $key, $value) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE user_id = ? AND lower(config_key) = lower(?) AND lower(config_value) = lower(?)";
		return $this->findEntities($sql, [$id, $key, $value]);
	}

	/**
	 * Return all project rows for a particular user ID
	 * @param int $user_id
	 */
	public function findByUser($user_id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE user_id = ?";
		return $this->findEntities($sql, [$user_id]);
	}
}