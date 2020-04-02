<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Tenant_Portal\Util;
use OCA\Tenant_Portal\Db\AuditLogEntry;
use OCA\Tenant_Portal\Db\AuditLogEntryMapper;

class AuditLogService {
	private $mapper;

	/**
	 * @param AuditLogEntryMapper $mapper
	 */
	public function __construct(AuditLogEntryMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Returns all entries based on tenant id
	 * @param integer $tenant_id
	 * @return Stat
	 */
	public function findAllByTenant($tenant_id) {
		return $this->mapper->findAllByTenant($tenant_id);
	}

	/**
	 * @param Exception $e
	 */
	public function handleException ($e) {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new NotFoundException($e->getMessage());
		} else {
			throw $e;
		}
	}

	/**
	 * Create a new entry
	 *
	 * @param integer $tenant_id
	 * @param string $user_id
	 * @param string $action
	 * @param string $details
	 * @param Date $timestamp
	 * @return AuditLogEntry
	 */
	public function create($tenant_id, $user_id, $action, $details, $timestamp=null) {
		if ($timestamp === null) {
			$timestamp = date("Y-m-d H:i:s");
		}
		$entry = new AuditLogEntry();
		$entry->setTimestamp($timestamp);
		$entry->setTenantId($tenant_id);
		$entry->setUserId($user_id);
		$entry->setDetails(strip_tags($details));
		$entry->setAction($action);
		return $this->mapper->insert($entry);
	}
}
