<?php

namespace OCA\Tenant_Portal\Managers;

use OCA\Tenant_Portal\Util;
use OCA\Tenant_Portal\Managers\TenantConfigManager;

class TenantUserManager {
	private $cache = [];
	private $tenantId;
	private $userManager;
	private $tenantConfigManager;
	private $collaboratorManager;
	private $config;
	private $appConfig;
	private $tenantUserService;

	private $domains;
	private $domainUsers;
	private $additionalUsers;
	private $additionalUserIds;
	private $additionalUserConfig;
	private $collaboratorUsers;
	private $collaboratorUserIds;
	private $collaboratorUserConfig;
	private $projectUsers;
	private $projectUserIds;
	private $projectUserConfig;

	public function __construct() {
		$this->config = \OC::$server->getConfig();
		$this->appConfig = \OC::$server->getAppConfig();
		$this->userManager = \OC::$server->getUserManager();
		$this->collaboratorManager = Util::getCollaboratorManager();
		$this->tenantUserService = Util::getTenantUserService();
		$this->tenantConfigManager = new TenantConfigManager();

		$this->cache = Array();
		$this->domains = Array();
		$this->domainUsers = Array();
		$this->additionalUserConfig = Array();
		$this->additionalUsers = Array();
		$this->additionalUserIds = Array();
		$this->collaboratorUserConfig = Array();
		$this->collaboratorUsers = Array();
		$this->collaboratorUserIds = Array();
		$this->projectUserConfig = Array();
		$this->projectUsers = Array();
		$this->projectUserIds = Array();
	}

	/**
	 * Assign an authorised user
	 * @param integer $tenant_id
	 * @param string $user_id
	 * @return TenantUser
	 */
	public function create($tenant_id, $user_id) {
		return $this->tenantUserService->create($tenant_id, $user_id);
	}

	/**
	 * Find an authorised user
	 * @param integer $id
	 * @return TenantUser
	 */
	public function find($id) {
		return $this->tenantUserService->find($id);
	}

	/**
	 * Find all authorised users on a tenant
	 * @param integer $tenant_id
	 * @return TenantUser[]
	 */
	public function findAllByTenant($tenant_id) {
		return $this->tenantUserService->findAllByTenant($tenant_id);
	}

	/**
	 * Delete an authorised user
	 * @param integer $id
	 * @return TenantUser
	 */
	public function destroy($id) {
		return $this->tenantUserService->delete($id);
	}

	/**
	 * Sets the notification email for the collaborator
	 * @param string $uid
	 * @param int $email
	 * @return boolean
	 */
	public function setEmail($uid, $email) {
		if (trim($uid) === '') {
			throw new \InvalidArgumentException("You must specify a username");
		}
		if (!Util::validEmail($email)) {
			throw new \InvalidArgumentException("Invalid email");
		}
		if (!$uid) {
			throw new \InvalidArgumentException("No user ID specified");
		} else {
			\OC::$server->getConfig()->setUserValue($uid, 'settings', 'email', $email);
			return true;
		}
		return false;
	}

	/**
	 * Sets the quota for the collaborator
	 * Note: mainly copied from /settings/ajax/setquota.php
	 * @param string $uid
	 * @param int $quota
	 * @return boolean
	 */
	public function setQuota($uid, $quota) {
		if (trim($uid) === '') {
			throw new \InvalidArgumentException("You must specify a username");
		}
		if (!preg_match("/^[0-9]/", $quota)) {
			throw new \InvalidArgumentException("Invalid quota");
		}
		if (!$uid) {
			throw new \InvalidArgumentException("No user ID specified");
		} else {
			$user = $this->userManager->get($uid);
			if (!$user) {
				throw new \InvalidArgumentException("Unable to find specified user");
			}
			/* Convert to bytes and back to human file size if the specified quota
			 * contains anything but numbers to ensure correct format */
			$quota=\OC_Helper::computerFileSize($quota);
			$quota=\OC_Helper::humanFileSize($quota);
			$user->setQuota($quota);
			return true;
		}
		return false;
	}

	/**
	 * Return the user's storage limit
	 * @param string $uidd
	 * @return string
	 */
        public function getQuota($uid) {
                $user = $this->userManager->get($uid);
                if ($user) {
                        $quota = $user->getQuota();
                        return $quota;
                }
                return false;
        }

	/**
	 * Find which tenant a user is assigned to
	 * @param string $user_id
	 * @return integer
	 */
	public function findTenantId($user_id) {
		return $this->tenantUserService->findTenantId($user_id);
	}

	/**
	 * Check if a user is authorisd on a tenant
	 * @param integer $tenant_id
	 * @param string $user_id
	 */
	public function isAuthorised($tenant_id, $user_id) {
		return $this->tenantUserService->isAuthorised($tenant_id, $user_id);
	}

	/**
	 * Returns a list of users for the tenant specified
	 * @return Array
	 */
	public function getDomainUsers($tenant_id) {
		$this->domains = $this->tenantConfigManager->getDomains($tenant_id);
		$this->domainUsers = Array();
		foreach ($this->domains as $domain) {
			$this->domainUsers = array_merge($this->userManager->search($domain), $this->domainUsers);
		}
		return $this->domainUsers;
	}

	/**
	 * Returns users on a domain
	 * #param string $domain
	 * @return array
	 */
	public function findDomainUsers($domain) {
		$users = Array();
		$sql = \OC::$server->getDatabaseConnection()->getQueryBuilder();
		$sql->select('uid')
			->from('*PREFIX*users', 'u')
			->where($sql->expr()->like('uid', $sql->createNamedParameter('%@'.$domain)))
			->orWhere($sql->expr()->like('uid', $sql->createNamedParameter('%@%.'.$domain)));
		$result = $sql->execute();
		while ($row = $result->fetch()) {
			if ($this->userManager->userExists($row['uid'])) {
				$users[] = $this->userManager->get($row["uid"]);
			}
		}
		return $users;
	}

	/**
	 * Returns a list of additional users for the tenant specified
	 * @return Array
	 */
	public function getAdditionalUsers($tenant_id) {
		$this->additionalUsers = Array();
		$this->additionalUserIds = Array();
		$this->additionalUserConfig = $this->tenantConfigManager->getAdditionalUsers($tenant_id);

		foreach ($this->additionalUserConfig as $u) {
			$uid = $u->getConfigValue();
			if ($this->userManager->userExists($uid)) {
				$actual_uid = Util::getRealUID($uid);
				if ($actual_uid && strtolower($uid) == strtolower($actual_uid)) {
					$this->additionalUserIds[strtolower($u->getConfigValue())] = $u->getId();
					$this->additionalUsers[] = $this->userManager->get($actual_uid);
				}
			}
		}
		return $this->additionalUsers;
	}


	/**
	 * Returns a list of collaborator users for the tenant specified
	 * @return Array
	 */
	public function getCollaboratorUsers($tenant_id) {
		$this->collaboratorUsers = Array();
		$this->collaboratorUserIds = Array();
		$this->collaboratorUserConfig = $this->tenantConfigManager->getCollaboratorUsers($tenant_id);

		foreach ($this->collaboratorUserConfig as $u) {
			$uid = $u->getConfigValue();
			if ($this->userManager->userExists($uid)) {
				$actual_uid = Util::getRealUID($uid);
				if ($actual_uid && strtolower($uid) == strtolower($actual_uid)) {
					$this->collaboratorUserIds[strtolower($u->getConfigValue())] = $u->getId();
					$this->collaboratorUsers[] = $this->userManager->get($actual_uid);
				}
			}
		}
		return $this->collaboratorUsers;
	}


	/**
	 * Returns a list of project users for the tenant specified
	 * @return Array
	 */
	public function getProjectUsers($tenant_id) {
		$this->projectUsers = Array();
		$this->projectUserIds = Array();
		$this->projectUserConfig = $this->tenantConfigManager->getProjectUsers($tenant_id);

		foreach ($this->projectUserConfig as $u) {
			$uid = $u->getConfigValue();
			if ($this->userManager->userExists($uid)) {
				$actual_uid = Util::getRealUID($uid);
				if ($actual_uid && strtolower($uid) == strtolower($actual_uid)) {
					$this->projectUserIds[strtolower($u->getConfigValue())] = $u->getId();
					$this->projectUsers[] = $this->userManager->get($actual_uid);
				}
			}
		}
		return $this->projectUsers;
	}



	/**
	 * Return user information for array of users
	 * @param User[] $users
	 *
	 * @return Array
	 */
	public function getUserInfo($tenant_id, $query=null, $limit=null, $offset=0) {
		$isAdmin = Util::isAdmin(Util::currentUser());
		$response = Array();

		// Get users matching the domains assigned to the tenant
		// TODO: Improve perforance, this takes ~1.4mins to load 3000 users across 2 domains
		$users = Array();
		$users = array_merge($this->getDomainUsers($tenant_id), $users);
		$users = array_merge($this->getAdditionalUsers($tenant_id), $users);
		$users = array_merge($this->getCollaboratorUsers($tenant_id), $users);
		$users = array_merge($this->getProjectUsers($tenant_id), $users);
		$users = array_unique($users, SORT_REGULAR);
		$users = array_filter($users, function ($u) use ($query) {
			$uid = $u->getUID();
			// Remove user if they are not on the assigned domain or an additional user
			// assigned to the domain
			if (count($this->domains) > 0 && !Util::matchUserDomain($uid, $this->domains)
				&& !in_array($u, $this->additionalUsers)
				&& !in_array($u, $this->collaboratorUsers)
				&& !in_array($u, $this->projectUsers)) {
					return false;
			}
			// Filter out results matching the search query
			if ($query) {
				return (stripos($uid, $query) !== false);
			}
			return true;
		});

		// Sort users by UID
		usort($users, function ($a, $b) {
			return strcmp($a->getUID(), $b->getUID());
		});

		// Limit the amount of users returned if set
		if ($limit) {
			$users = array_slice($users, $offset, $limit);
		}

		// Build storage used cache, if it fails do it cleanly so getUserDetail()
		// Falls back to using the owncloud api
		$this->buildStorageSizeCache($users);

		// Retrieve all information about a user
		array_map(function ($user) use (&$response, $isAdmin) {
			$response[] = $this->getUserDetail($user, $isAdmin);
		}, $users);

		return $response;
	}

	/*
	 * @param OC\User
	 */
	public function getUserDetail($user, $isAdmin=false) {
		$uid = $user->getUID();
		$user_item = Array(
			"user_id" => $uid,
			"display_name" => htmlentities($user->getDisplayName()),
			"last_login" => $this->getFormattedLastLogin($user),
			"last_login_epoch" => $user->getLastLogin(),
			"used_bytes" => $this->getStorageSize($uid),
			"used_human" => "0 B",
			"quota_bytes" => "0",
			"quota_human" => "0 B",
			"type" => "domain",
			"id" => null
		);

		if (in_array($user, $this->additionalUsers)) {
			$user_item["type"] = 'additional';
			if ($isAdmin) {
				$user_item["id"] = $this->additionalUserIds[strtolower($uid)];
			}
		}
		if (in_array($user, $this->collaboratorUsers)) {
			$user_item["type"] = 'collaborator';
		}
		if (in_array($user, $this->projectUsers)) {
			$user_item["type"] = 'project';
			$user_item["display_name"] = "Group Drive User";
		}

		// Get quota
		$quota = self::getUserQuota($uid);
		$user_item['quota_bytes'] = Util::configQuotaToBytes($quota);

		// If it's null, the user's quota is set to unlimited
                if ($quota === null) {
                        $user_item['quota_human'] = "Unlimited";
                } else {
                        $user_item['quota_human'] = \OCP\Util::humanFileSize($quota);
                }

                // Get user's currently used quota
                $user_item['used_human'] = \OCP\Util::humanFileSize($user_item['used_bytes']);
                if ($user_item['used_human'] === ' B') {
                        $user_item['used_human'] = 'Unavailable';
                }

		return $user_item;
	}

	/**
	 * Returns the size of a user's storage in bytes
	 * @param string $uid
	 * @return int
	 */
	public function getStorageSize($uid) {
		$size = 0;
		$storageName = "home::".$uid;

		if (isset($this->cache['storage_used'][$storageName])) {
			$size = $this->cache['storage_used'][$storageName];
		} else {
		/* Fallback method using Owncloud API's this takes 3-4 mins for 3000 users
		 * This method takes 0.05s per user */
			$cache = new \OC\Files\Cache\Cache($storageName);
			$rootfolder = $cache->get('/files'); // Performs a select from the filecache where storage = ? and path_hash = '/files'
			$size = $rootfolder['size'];
			$this->cache['storage_used'][$storageName] = $size;
		}
		return $size;
	}

	/*
	 * Builds cache of storage used
	 * @param \OC\User[]
	 */
	public function buildStorageSizeCache($users) {
		$storageNames = array_map(function ($u) { return 'home::'.$u->getUID(); }, $users);
		try {
			$filesDir = '45b963397aa40d4a0063e0d85e4fe7a1';
			$sql = \OC::$server->getDatabaseConnection()->getQueryBuilder();
			/**
			 * SELECT s.id, f.size FROM filecache f
			 * JOIN storages s ON f.storage = s.numeric_id
			 * WHERE f.path_hash = '45b963397aa40d4a0063e0d85e4fe7a1'
			 * AND f.storage = s.numeric_id AND s.id IN ($storageNames)
			 */
			$sql->select('s.id','f.size')
				->from('*PREFIX*filecache', 'f')
				->join('f', '*PREFIX*storages', 's', 'f.storage = s.numeric_id')
				->where($sql->expr()->eq('f.storage', 's.numeric_id'))
				->andWhere($sql->expr()->eq('f.path_hash', $sql->createNamedParameter($filesDir)))
				->andWhere($sql->expr()->in('s.id', $sql->createParameter('storage_names')))
				->setParameter('storage_names', $storageNames, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
			$result = $sql->execute();
			while ($row = $result->fetch()) {
				$this->cache['storage_used'][$row['id']] = $row['size'];
			}
			// Fill in 0 bytes for users that don't have a /files directory in the
			// filecache
			array_map(function($s) {
				if (!array_key_exists($s, $this->cache['storage_used'])) {
					$this->cache['storage_used'][$s] = 0;
				}
			}, $storageNames);
		} catch (\Exception $e) {
			Util::debugLog("Unable to build the storage_used cache");
		}
	}

	/**
	 * Returns the user's quota limit
	 * @param string $uid
	 * @return int
	 */
        public function getUserQuota($uid) {
                $quota = false;
                $defaultQuota = $this->appConfig->getValue('files', 'default_quota', false);
                $quota = $this->getQuota($uid);
                if ($quota === 'none') {
                        return null;
                }
                if ($quota === false || $quota === 'default') {
                        $quota = $defaultQuota;
                }
                $quota = Util::configQuotaToBytes($quota);
                return ($quota !== null ? $quota : 0);
        }

	/**
	 * Returns the last login of the user
	 * @param string $uid
	 * @return string
	 */
	public function getFormattedLastLogin($user) {
		$lastLogin = date("d-M-Y", $user->getLastLogin());
		if ($lastLogin == "01-Jan-1970") {
			$lastLogin = "Never";
		}
		return $lastLogin;
	}

	/**
	 * Checks to see if a user is on an assigned domain
	 * @param int $tenant_id
	 * @param string $user_id
	 * @return OC\User|false
	 */
	 public function isDomainUser($tenant_id, $user_id) {
		$tenantDomains = $this->tenantConfigManager->getDomains($tenant_id);
		$user = $this->userManager->get($user_id);
		if ($user && Util::matchUserDomain($user->getUID(), $tenantDomains)) {
			return $user;
		}
		return false;
	}

        /**
         * Checks to see if a user is assigned to the tenant as an additional user
         * @param int $tenant_id
         * @param string $user_id
         * @return OC\User|false
         */
        public function isAdditionalUser($tenant_id, $user_id) {
                $additionalUsers = $this->tenantConfigManager->getAdditionalUsers($tenant_id);
                foreach ($additionalUsers as $user) {
                        if (strcasecmp($user->getConfigValue(), $user_id) == 0) {
                                return $this->userManager->get($user_id);
                        }
                }
                return false;
        }

        /**
         * Checks to see if a user is assigned to the tenant as an additional user
         * @param int $tenant_id
         * @param string $user_id
         * @return OC\User|false
         */
        public function isCollaboratorUser($tenant_id, $user_id) {
                $collaboratorUsers = $this->tenantConfigManager->getCollaboratorUsers($tenant_id);
                foreach ($collaboratorUsers as $user) {
                        if (strcasecmp($user->getConfigValue(), $user_id) == 0) {
                                return $this->userManager->get($user_id);
                        }
                }
                return false;
        }

        /**
         * Checks to see if a user is assigned to the tenant as an project user
         * @param int $tenant_id
         * @param string $user_id
         * @return OC\User|false
         */
        public function isProjectUser($tenant_id, $user_id) {
                $projectUsers = $this->tenantConfigManager->getProjectUsers($tenant_id);
                foreach ($projectUsers as $user) {
                        if (strcasecmp($user->getConfigValue(), $user_id) == 0) {
                                return $this->userManager->get($user_id);
                        }
                }
                return false;
        }
}
