<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class TenantConfig extends Entity implements JsonSerializable {
	protected $tenantId;
	protected $configKey;
	protected $configValue;

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'tenant_id' => $this->tenantId,
			'config_key' => $this->configKey,
			'config_value' => htmlentities($this->configValue),
		];
	}
}
