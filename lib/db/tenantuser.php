<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class TenantUser extends Entity implements JsonSerializable {
	protected $tenantId;
	protected $userId;

	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'tenant_id' => $this->tenantId,
			'user_id' => $this->userId,
		];
	}
}
