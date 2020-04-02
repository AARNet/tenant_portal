<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Stat extends Entity implements JsonSerializable {
	protected $timestamp;
	protected $tenantId;
	protected $statKey;
	protected $statValue;

	public function jsonSerialize() {
		return [
			'timestamp' => $this->getTimestamp(),
			'tenant_id' => $this->getTenantId(),
			'stat_key' => $this->getStatKey(),
			'stat_value' => $this->getStatValue()
		];
	}
}
