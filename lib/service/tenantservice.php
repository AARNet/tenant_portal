<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use \OCA\Tenant_Portal\Db\Tenant;
use \OCA\Tenant_Portal\Db\TenantMapper;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Service\Exceptions\NotFoundException;

class TenantService {
	private $mapper;

	/**
	 * @param TenantMapper $mapper
	 */
	public function __construct(TenantMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Returns all tenants
	 */
	public function findAll() {
		return $this->mapper->findAll();
	}

	/*
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
	 * Return a tenant by id
	 * @param integer $id
	 */
	public function find($id) {
		try {
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
	// alias
	public function get($id) {
		return $this->find($id);
	}

	/*
	 * Create a new tenant
	 *
	 * @param string $name
	 * @param string $short_code
	 */
	public function create($name, $code) {
		$name = trim($name);
		$code = trim($code);

		if ($this->tenantExists($name)) {
			throw new Exception('Tenant "'.$name.'" already exists');
		}
		if ($this->codeExists($code)) {
			throw new Exception('Tenant with code "'.$code.'" already exists');
		}
		if ($name != strip_tags($name)) {
			throw new Exception('HTML tags are not allowed in the tenant name');
		}
		if (!$name) {
			throw new Exception('A tenant name must be provided.');
		}
		if ($code != strip_tags($code)) {
			throw new Exception('HTML tags are not allowed in the tenant code');
		}
		if (!$code) {
			throw new Exception('A tenant short code must be provided.');
		}
		if (preg_match('/\s/',$code)) {
			throw new Exception('Tenant code cannot contain spaces');
		}

		$tenant = new Tenant();
		$tenant->setName($name);
		$tenant->setCode($code);

		$user = Util::currentUser();
		Util::debugLog("@Tenant [user=>$user, action=>create, tenant_name=>$name, tenant_short_code=>$code]");
		return $this->mapper->insert($tenant);
	}

	/*
	 * Check if a tenant exists
	 * @param string $name
	 * @return boolean
	 */
	public function tenantExists($name) {
		try {
			$this->mapper->findByName($name);
		} catch (Exception $e) {
			if ($e instanceof DoesNotExistException) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a tenant short code is in use
	 * @param string $code
	 * @return boolean
	 */
	public function codeExists($code) {
		try {
			$this->mapper->findByCode($code);
		} catch (Exception $e) {
			if ($e instanceof DoesNotExistException) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete a tenant by id
	 * @param integer $id
	 */
	public function delete($id) {
		try {
			$tenant = $this->mapper->find($id);
			$user = Util::currentUser();
			Util::debugLog("@Tenant [user=>$user, tenant=>$id, action=>delete, tenant_name=>".$tenant->getName()."]");
			$this->mapper->delete($tenant);
			return $tenant;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
