<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Tenant extends Entity implements JsonSerializable {
	var $id;
	protected $name;
	protected $code;

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'name' => htmlentities($this->name),
			'code' => htmlentities($this->code)
		];
	}
}
