<?php

namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCA\Tenant_Portal\Db\Stat;
use OCP\AppFramework\Db\Mapper;

class StatMapper extends Mapper {

	static private $table = 'tp_stats';
	static private $entity = '\OCA\Tenant_Portal\Db\Stat';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Returns stats for a tenant based on a key
	 *
	 * @param int $tenant_id
	 * @param string $key
	 */
	public function findAllByKey($tenant_id, $key) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? and stat_key = ? ORDER BY timestamp ASC";
		return $this->findEntities($sql, [$tenant_id, $key]);
	}

	/**
	 * Returns all stats for a tenant
	 *
	 * @param int $tenant_id
	 */
	public function findAllByTenant($tenant_id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? ORDER BY timestamp DESC";
		return $this->findEntities($sql, [$tenant_id]);
	}

	/**
	 * Returns all stats for a tenant on a particular date
	 *
	 * @param int $tenant_id
	 * @param string $date
	 */
	public function findByTenantAndDate($tenant_id, $date) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? AND DATE(timestamp) = ? ORDER BY timestamp DESC";
		return $this->findEntities($sql, $params);
	}
}
