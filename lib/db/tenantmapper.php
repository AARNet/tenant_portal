<?php

namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;
use OCP\AppFramework\Db\DoesNotExistException;

class TenantMapper extends Mapper {

	static private $table = 'tp_tenants';
	static private $entity = '\OCA\Tenant_Portal\Db\Tenant';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Returns a single tenant
	 *
	 * @param int $id
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Returns all tenants
	 */

	public function findAll() {
		$sql = "SELECT * FROM *PREFIX*".self::$table;
		return $this->findEntities($sql);
	}

	/**
	 * Finds and returns a tenant based on name
	 *
	 * @param string $name
	 */
	public function findByName($name) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE LOWER(name) = LOWER(?)";
		return $this->findEntity($sql, [$name]);
	}

	/**
	 * Finds and returns a tenant based on short code
	 *
	 * @param string $code
	 */
	public function findByCode($code) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE LOWER(code) = LOWER(?)";
		return $this->findEntity($sql, [$code]);
	}
}
