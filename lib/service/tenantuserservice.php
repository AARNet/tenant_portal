<?php

namespace OCA\Tenant_Portal\Service;

use Exception;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IUserManager;
use OCA\Tenant_Portal\Util;
use OCA\Tenant_Portal\Db\TenantUser;
use OCA\Tenant_Portal\Db\TenantUserMapper;
use OCA\Tenant_Portal\Service\Exceptions\NotFoundException;
use OCA\Tenant_Portal\Service\Exceptions\AlreadyAuthedException;

class TenantUserService {
	private $mapper;
	private $userManager;
	private $groupManager;

	/**
	 * @param TenantUserMapper $mapper
	 * @param IUserManager $userManager
	 */
	public function __construct(TenantUserMapper $mapper, IUserManager $userManager) {
		$this->mapper = $mapper;
		$this->userManager = $userManager;
	}

	/**
	 * Returns authorised tenant users
	 * @param integer $tenant_id
	 */
	public function findAllByTenant($tenant_id) {
		return $this->mapper->findAllByTenant($tenant_id);
	}

	/**
	 * Return a authorised tenant user by id
	 * @param integer $id
	 */
	public function find($id) {
		try { 
			return $this->mapper->find($id);
		} catch (Exception $e) {
			$this->handleException($e);
		}	
	}

	/**
	 * Authorise a user on a tenant
	 * @param integer $tenant_id
	 * @param string $user_id
	 */
	public function create($tenant_id, $user_id) {
		$userExists = $this->userManager->userExists($user_id);
		$alreadyAuthed = $this->hasAuthorisation($user_id);
		$uid = Util::getRealUID($user_id);
		if ($userExists && !$alreadyAuthed && $uid) {
			$tenantUser = new TenantUser();
			$tenantUser->setTenantId($tenant_id);
			$tenantUser->setUserId($uid);
			$user = Util::currentUser();
			Util::debugLog("@TenantAuthorisedUser [user=>$user, tenant=>$tenant_id, action=>create, authorised_user=>$user_id]");

			return $this->mapper->insert($tenantUser);
		} else {
			if (!$userExists) {
				throw new NotFoundException();
			}
			if ($alreadyAuthed) {
				throw new AlreadyAuthedException();
			}
		}
	}

	/**
	 * Deauthorise a user from a tenant
	 * @param integer $id
	 */
	public function delete($id) {
		try {
			$tenantUser = $this->mapper->find($id);
			$user = Util::currentUser();
			Util::debugLog("@TenantAuthorisedUser [user=>$user, tenant=>".$tenantUser->getTenantId().", action=>delete, authorised_user=>".$tenantUser->getUserId()."]");
			$this->mapper->delete($tenantUser);
			return $tenantUser;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Helper method to find a tenant id
	 * @param string $user_id
	 * @return FALSE|integer
	 */
	public function findTenantId($user_id) {
		try {
			$tenantUser = $this->mapper->findByUser($user_id);
			return $tenantUser->getTenantId();
		} catch (Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Helper method to determine if a user is
	 * authorised on any tenant
	 * @param string $user_id
	 * @return boolean
	 */
	public function hasAuthorisation($user_id) {
		try {
			$tenantUser = $this->mapper->findByUser($user_id);
			return TRUE;
		} catch (Exception $e) {
			return FALSE;
		}
	}

	/**
	 * Helper method to determine if a user is 
	 * alreadyt authorised on a tenant
	 * @param integer $tenant_id
	 * @param string $user_id
	 * @return boolean
	 */
	public function isAuthorised($tenant_id, $user_id) {
		try {
			$tenantUser = $this->mapper->findByUser($user_id);
			return ($tenantUser->getTenantId() == $tenant_id);
		} catch (Exception $e) {
			return FALSE;
		}
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
}
