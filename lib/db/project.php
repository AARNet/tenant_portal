<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Project extends Entity implements JsonSerializable {
	protected $userId;
	protected $configKey;
	protected $configValue;


	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'user_id' => $this->userId,
			'config_key' => $this->configKey,
			'config_value' => htmlentities($this->configValue),
		];
	}
}
