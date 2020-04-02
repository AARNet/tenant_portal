<?php
namespace OCA\Tenant_Portal\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class CollaboratorTokenMapper extends Mapper {

	static private $table = 'tp_collaborators_tokens';
	static private $entity = '\OCA\Tenant_Portal\Db\CollaboratorToken';

	public function __construct(IDBConnection $db) {
		parent::__construct($db, self::$table, self::$entity);
	}

	/**
	 * Find token by id
	 * @param integer $id
	 * @return Entity
	 */
	public function find($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE id = ?";
		return $this->findEntity($sql, [$id]);
	}

	/**
	 * Find token by... token
	 * @param string $token
	 * @return Entity
	 */
	public function findByToken($token) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE token = ?";
		return $this->findEntity($sql, [$token]);
	}

	/**
	 * Find token by collaborator id
	 * @param integer $id
	 * @return Array[Entity]
	 */
	public function findByCollaboratorId($id) {
		$sql = "SELECT * FROM *PREFIX*".self::$table." WHERE collaborator_id = ?";
		return $this->findEntities($sql, [$id]);
	}

	/**
	 * Delete tokens for a collaborator
	 * @param integer $id
	 * @return boolean
	 */
	public function deleteByCollaboratorId($id) {
		$sql = "DELETE FROM *PREFIX*".self::$table." WHERE collaborator_id = ?";
		return $this->execute($sql, [$id]);
	}
}
