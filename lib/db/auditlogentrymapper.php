<?php
namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class AuditLogEntryMapper extends Mapper {

	static private $table = 'tp_audit_log';
	static private $entity = '\OCA\Tenant_Portal\Db\AuditLogEntry';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Returns an audit log entry
	 *
	 * @param int $id
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	public function get($id) {
		return $this->find($id);
	}

	/**
	 * Returns all  audit log entries for a particular tenant
	 * *
	 * @param int $id
	 */
	public function findAllByTenant($tenant_id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE tenant_id = ? ORDER BY timestamp DESC";
		$params = [$tenant_id];
		try {
			$result = $this->findEntities($sql, $params);
		} catch (\Exception $e) {
			\OCP\Util::writeLog('tenant_portal', "exception=>".$e->getMessage(), \OCP\Util::ERROR);
			$result = [];
		}
		return $result;
 
	}
}
