<?php
namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class CollaboratorMapper extends Mapper {

	static private $table = 'tp_collaborators';
	static private $entity = '\OCA\Tenant_Portal\Db\Collaborator';

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

	public function findAll() {
		$sql = "SELECT * FROM *PREFIX*".self::$table."";
		return $this->findEntities($sql);
	}

	/**
	 * Returns an authorised tenant user based on the user_id
	 *
	 * @param int $id
	 */
	public function findByUser($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE lower(uid) = lower(?)";
		return $this->findEntity($sql, [$id]);
	}
}
