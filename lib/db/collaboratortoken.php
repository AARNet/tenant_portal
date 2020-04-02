<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class CollaboratorToken extends Entity implements JsonSerializable {
	protected $collaboratorId;
	protected $token;
	protected $created;

	/**
	 * Returns the age of a token in seconds
	 */
	public function getAge() {
			$token_created = new \DateTime($this->getCreated());
			$now = new \DateTime();
			$diff = $now->diff($token_created);
			return ($diff->y * 365 * 24 * 60 * 60) +
				($diff->m * 30 * 24 * 60 * 60) +
				($diff->d * 24 * 60 * 60) +
				($diff->h * 60 * 60) +
				($diff->i * 60) +
				$diff->s;
	}

	public function jsonSerialize() {
		return [
			'collaborator_id' => $this->getCollaboratorId(),
			'token' => $this->getToken(),
			'created' => $this->getCreated(),
		];
	}
}
