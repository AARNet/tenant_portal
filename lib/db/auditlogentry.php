<?php

namespace OCA\Tenant_Portal\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class AuditLogEntry extends Entity implements JsonSerializable {
	protected $timestamp;
	protected $tenantId;
	protected $userId;
	protected $action;
	protected $details;

	public function getDetails() {
		return htmlentities($this->details);
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'timestamp' => $this->getTimestamp(),
			'user_id' => $this->getUserId(),
			'tenant_id' => $this->getTenantId(),
			'action' => $this->getAction(),
			'details' => $this->getDetails()
		];
	}
}
