<?php

namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class TenantUserMapper extends Mapper {

	static private $table = 'tp_tenant_users';
	static private $entity = '\OCA\Tenant_Portal\Db\TenantUSer';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Returns an authorised tenant user
	 *
	 * @param int $id
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Returns an authorised tenant user based on the user_id
	 *
	 * @param int $id
	 */
	public function findByUser($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE lower(user_id) = lower(?)";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Returns all authorised tenant users for a tenant
	 *
	 * @param int $id
	 */
	public function findAllByTenant($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ?";
		return $this->findEntities($sql, [$id]);
	}
}
