<?php

namespace OCA\Tenant_Portal\Service;

use \Exception;
use \InvalidArgumentException;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Tenant_Portal\Db\CollaboratorToken;
use OCA\Tenant_Portal\Db\CollaboratorTokenMapper;
use OCA\Tenant_Portal\Db\CollaboratorMapper;
use OCA\Tenant_Portal\Service\CollaboratorService;
use OCA\Tenant_Portal\Util;

class CollaboratorTokenService {
	private $tokenMapper;
	private $collaboratorMapper;

	/**
	 * @param CollaboratorMapper $mapper
	 */
	public function __construct(CollaboratorTokenMapper $tokenMapper, CollaboratorMapper $collaboratorMapper) {
		$this->tokenMapper = $tokenMapper;
		$this->collaboratorMapper = $collaboratorMapper;
	}

	/**
	 * Find token by id
	 * @param integer $id
	 * @return CollaboratorToken
	 */
	public function find($id) {
		return $this->tokenMapper->find($id);
	}

	/**
	 * Find a token by token
	 * @param string $token
	 * @return CollaboratorToken
	 */
	public function findByToken($token) {
		return $this->tokenMapper->findByToken($token);
	}

	/**
	 * Find a token by collaborator ID
	 * @param integer $collaborator_id
	 * @return CollaboratorToken[]|Array
	 */
	public function findByCollaboratorId($collaborator_id) {
		try {
			return $this->tokenMapper->findByCollaboratorId($collaborator_id);
		} catch (\Exception $e) {
			return Array();
		}
	}

	/**
	 * Create a token
	 * @param integer $collaborator_id
	 * @return CollaboratorToken
	 */
	public function create($collaborator_id) {
		if ($this->collaboratorIdExists($collaborator_id)) {
			// Cleanup existing tokens
			$this->deleteByCollaboratorId($collaborator_id);
			// Create new token
			$token = new CollaboratorToken();
			$token->setCollaboratorId($collaborator_id);
			$token->setToken(Util::generateToken());
			$token_date = new \DateTime();
			$token->setCreated($token_date->format('Y-m-d H:i:s'));
 			Util::debugLog("@CollaboratorToken [collaborator=>$collaborator_id, token=>".$token->getToken()."]");
			return $this->tokenMapper->insert($token);
		}
		throw new \Exception("Invalid collaborator ID");
	}

	/**
	 * Delete tokens by collaborator id
	 * @param integer $collaborator_id
	 * @return CollaboratorToken|false
	 */
	public function deleteByCollaboratorId($collaborator_id) {
		try {
			return $this->tokenMapper->deleteByCollaboratorId($collaborator_id);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Check if collaborator ID is valid
	 * @param integer $collaborator_id
	 * @return Collaborator|false
	 */
	public function collaboratorIdExists($collaborator_id) {
		try {
			return $this->collaboratorMapper->find($collaborator_id);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Checks if the token is still valid
	 * @param $token string
	 * @return CollaboratorToken|false
	 */
	public function validToken($token) {
		try {
			$maxAge = 72 * 60 * 60; // 72 hours in seconds
			$token = $this->findByToken($token);
			$token_created = new \DateTime($token->getCreated());
			$now = new \DateTime();
			$diff = $now->diff($token_created);
			if ($token->getAge() < $maxAge) {
				return $token;
			}
		} catch (DoesNotExistException $e) {
			return false;
		}
		catch (\Exception $e) {
			return false;
		}
		return false;
	}
}
