<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\Tenant_Portal\Util;
use OCA\Tenant_Portal\Db\Stat;
use OCA\Tenant_Portal\Db\StatMapper;

class StatService {
	private $mapper;

	/**
	 * @param StatMapper $mapper
	 */
	public function __construct(StatMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Returns all stats based on tenant id
	 * @param integer $tenant_id
	 * @return Stat
	 */
	public function findAllByTenant($tenant_id) {
		return $this->mapper->findAllByTenant($tenant_id);
	}

	/**
	 * Returns all stats of a certain type for a tenant
	 * @param integer $tenant_id
	 * @param string $key
	 * @return Stat
	 */
	public function findAllByKey($tenant_id, $key) {
		return $this->mapper->findAllByKey($tenant_id, $key);
	}

	/**
	 * Returns all stats for a tenant based on provided date
	 * @param integer $tenant_id
	 * @param string $date (format: YYYY-MM-DD)
	 * @return Stat
	 */
	public function findByTenantAndDate($tenant_id, $date) {
		return $this->mapper->findByTenantAndDate($tenant_id, $date);
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
	 * Create a new stat
	 *
	 * @param integer $tenant_id
	 * @param string $key
	 * @param string $key
	 * @param Date $timestamp
	 * @return Stat
	 */
	public function create($tenant_id, $key, $value, $timestamp=null) {
		if ($timestamp === null) {
			$timestamp = date("Y-m-d H:i:s");
		}
		$stat = new Stat();
		$stat->setTenantId($tenant_id);
		$stat->setTimestamp($timestamp);
		$stat->setStatKey($key);
		$stat->setStatValue($value);
		return $this->mapper->insert($stat);
	}
}
