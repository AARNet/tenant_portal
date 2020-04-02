<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Collaborator extends Entity implements JsonSerializable {
	protected $uid;
	protected $mail;
	protected $password;
	protected $salt;
	protected $cn;

	public function getCn() {
		return htmlentities($this->cn);
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'uid' => $this->getUid(),
			'mail' => $this->getMail(),
			'cn' => $this->getCn(),
		];
	}
}
