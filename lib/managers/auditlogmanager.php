<?php

namespace OCA\Tenant_Portal\Managers;

use \OCA\Tenant_Portal\Util;

class AuditLogManager {

	private $auditLogService;

	const ENTRY_LIMIT = 10;
	const ENTRY_OFFSET = 0;
	const TYPE_UPDATE = "Update";
	const TYPE_CREATE = "Create";
	const TYPE_DELETE = "Delete";

	public function __construct() {
		$this->auditLogService = Util::getAuditLogService();
	}

	/**
	 * Create an audit log entry
	 * @param string $tenant_id
	 * @param string $user_id
	 * @param string $action
	 * @param string $info
	 * @param string $timestamp
	 * @return AuditLogEntry
	 */
	public function create($tenant_id, $user_id, $action, $details, $timestamp=null) {
		return $this->auditLogService->create($tenant_id, $user_id, $action, $details, $timestamp=null);
	}

	/**
	 * Just a placeholder to stop destroy from working
	 */
	public function destroy() {
		return;
	}

	/**
	 * Retrieve log entries for a tenant
	 * @param int $tenant_id
	 * @param int $limit
	 * @param int $offset
	 * @param string $filter
	 * @param mixed $request
	 * @return array
	 */
	public function get($tenant_id, $limit=self::ENTRY_LIMIT, $offset=self::ENTRY_OFFSET, $filter=null, $request=Array()) {
		$entries = $this->auditLogService->findAllByTenant($tenant_id, $limit, $offset, $filter);
	
		// Filter out entries
		$filter = trim(strtolower($filter));
		$filteredEntries = Array();
		// Convert entries to array and filter if one is provided
		foreach ($entries as $entry) {
			if ($filter !== "") {
				if (stripos($entry->getUserId(), $filter) !== false || 
				    stripos($entry->getDetails(), $filter) !== false ||
				    stripos($entry->getTimestamp(), $filter) !== false) {
				    $filteredEntries[] = $entry->jsonSerialize();
				}
			} else {
				$filteredEntries[] = $entry->jsonSerialize();
			}
		}

		// Cut down to a page
		$filteredEntries = array_slice($filteredEntries, $offset, $limit);
		
		$recordsTotal = count($entries);
		if ($filter !== "") {
			$recordsFiltered = count($filteredEntries);
		} else { 
			$recordsFiltered = count($entries);
		}

		// Define database column to datatables column association
		$columns = Array(
			Array('db' => 'timestamp', 'dt' => 0),
			Array('db' => 'user_id', 'dt' => 1),
			Array('db' => 'details', 'dt' => 2),
		);

		// Return result in datatables format
		$result = Array(
			"draw" => isset($request['draw']) ? intval($request['draw']) : 0,
			"recordsTotal" => intval($recordsTotal),
			"recordsFiltered" => intval($recordsFiltered),
			"data" => Util::formatDataTables($columns, $filteredEntries),
		);	
		return $result;
	}

	/**
	 * helper method for log creation
	 * @param int $tenant_id
	 * @param string $message
	 */
	public function logCreate($tenant_id, $message) {
		$this->create($tenant_id, Util::currentUser(), self::TYPE_CREATE, $message);
	}

	/**
	 * helper method for log creation
	 * @param int $tenant_id
	 * @param string $message
	 */
	public function logUpdate($tenant_id, $message) {
		$this->create($tenant_id, Util::currentUser(), self::TYPE_UPDATE, $message);
	}

	/**
	 * helper method for log creation
	 * @param int $tenant_id
	 * @param string $message
	 */
	public function logDelete($tenant_id, $message) {
		$this->create($tenant_id, Util::currentUser(), self::TYPE_DELETE, $message);
	}
}
