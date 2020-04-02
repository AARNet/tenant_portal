<?php

namespace OCA\Tenant_Portal;

use \OCA\Tenant_Portal\Helpers\Stat;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\StatManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Managers\CollaboratorManager;
use \OCA\Tenant_Portal\Managers\CollaboratorTokenManager;
use \OCA\Tenant_Portal\Managers\ProjectManager;
use \OCA\Tenant_Portal\Managers\AuditLogManager;

use \OCA\Tenant_Portal\Service\TenantService;
use \OCA\Tenant_Portal\Service\TenantUserService;
use \OCA\Tenant_Portal\Service\TenantConfigService;
use \OCA\Tenant_Portal\Service\StatService;
use \OCA\Tenant_Portal\Service\ProjectService;
use \OCA\Tenant_Portal\Service\CollaboratorService;
use \OCA\Tenant_Portal\Service\CollaboratorTokenService;
use \OCA\Tenant_Portal\Service\AuditLogService;

use \OCA\Tenant_Portal\Db\StatMapper;
use \OCA\Tenant_Portal\Db\TenantMapper;
use \OCA\Tenant_Portal\Db\TenantUserMapper;
use \OCA\Tenant_Portal\Db\TenantConfigMapper;
use \OCA\Tenant_Portal\Db\ProjectMapper;
use \OCA\Tenant_Portal\Db\CollaboratorMapper;
use \OCA\Tenant_Portal\Db\CollaboratorTokenMapper;
use \OCA\Tenant_Portal\Db\AuditLogEntryMapper;


require_once 'tenant_portal/vendor/random_compat/lib/random.php';

class Util {
	private static $urlGenerator;
	private static $tenantService;
	private static $tenantUserService;
	private static $tenantConfigService;
	private static $collaboratorService;
	private static $collaboratorTokenService;
	private static $auditLogService;
	private static $statService;
	private static $projectService;
	private static $tenantManager;
	private static $tenantConfigManager;
	private static $tenantUserManager;
	private static $statManager;
	private static $collaboratorManager;
	private static $collaboratorTokenManager;
	private static $projectManager;
	private static $projectFolderManager;
	private static $auditLogManager;

	/**********************
	 * Owncloud Accessors
	 *********************/

	/**
	 * Returns a group manager
	 * @return IGroupManager
	 */
	public static function getGroupManager() {
		return \OC::$server->getGroupManager();
	}

	/**
	 * Returns a URL generator
	 * @return IURLGenerator
	 */
	public static function getUrlGenerator() {
		return \OC::$server->getUrlGenerator();
	}

	/**
	 * Returns the database connection
	 * @return IDBConnection
	 */
	public static function getDatabaseConnection() {
		return \OC::$server->getDatabaseConnection();
	}

	/**
	 * Returns the user manager
	 * @return IUserManager
	 */
	public static function getUserManager() {
		return \OC::$server->getUserManager();
	}

	/**************************
	* Tenant Portal Managers
	**************************/
	
	public static function getAuditLogManager() {
		if (self::$auditLogManager === null) {
			self::$auditLogManager = new AuditLogManager();
		}
		return self::$auditLogManager;
	}

	public static function getTenantManager() {
		if (self::$tenantManager === null) {
			self::$tenantManager = new TenantManager();
		}
		return self::$tenantManager;
	}

	public static function getTenantConfigManager() {
		if (self::$tenantConfigManager === null) {
			self::$tenantConfigManager = new TenantConfigManager();
		}
		return self::$tenantConfigManager;
	}

	public static function getTenantUserManager() {
		if (self::$tenantUserManager === null) {
			self::$tenantUserManager = new TenantUserManager();
		}
		return self::$tenantUserManager;
	}

	public static function getStatManager() {
		if (self::$statManager == null) {
			self::$statManager = new StatManager();
		}
		return self::$statManager;
	}

	/**
	 * Returns a collaborator manager
	 * @return CollaboratorManager
	 */
	public static function getCollaboratorManager() {
		if (self::$collaboratorManager === null) {
			self::$collaboratorManager = new CollaboratorManager();
		}
		return self::$collaboratorManager;
	}

	/**
	 * Returns a collaborator token manager
	 * @return CollaboratorTokenManager
	 */
	public static function getCollaboratorTokenManager() {
		if (self::$collaboratorTokenManager === null) {
			self::$collaboratorTokenManager = new CollaboratorTokenManager();
		}
		return self::$collaboratorTokenManager;
	}


	/**
	 * Returns a project manager
	 * @return ProjectManager
	 */
	public static function getProjectManager() {
		if (self::$projectManager === null) {
			self::$projectManager = new ProjectManager();
		}
		return self::$projectManager;
	}

	/**************************
	 * Tenant Portal Services
	 *************************/

	/**
	 * Returns a AuditLogService object
	 * @return AuditLogService
	 */
	public static function getAuditLogService() {
		if (self::$auditLogService === null) {
			$databaseConnection = self::getDatabaseConnection();
			$auditMapper = new AuditLogEntryMapper($databaseConnection);
			self::$auditLogService = new AuditLogService($auditMapper);
		}
		return self::$auditLogService;
	}

	/**
	 * Returns a TenantService object
	 * @return TenantService
	 */
	public static function getTenantService() {
		if (self::$tenantService === null) {
			$databaseConnection = self::getDatabaseConnection();
			$tenantMapper = new TenantMapper($databaseConnection);
			self::$tenantService = new TenantService($tenantMapper);
		}
		return self::$tenantService;
	}

	/**
	 * Returns a TenantConfigService object
	 * @return TenantConfigService
	 */
	public static function getTenantConfigService() {
		if (self::$tenantConfigService === null) {
			$databaseConnection = self::getDatabaseConnection();
			$tenantConfigMapper = new TenantConfigMapper($databaseConnection);
			self::$tenantConfigService = new TenantConfigService($tenantConfigMapper);
		}
		return self::$tenantConfigService;
	}

	/**
	 * Returns a TenantUserService object
	 * @return TenantUserService
	 */
	public static function getTenantUserService() {
		if (self::$tenantUserService === null) {
			$databaseConnection = self::getDatabaseConnection();
			$userManager = self::getUserManager();
			$tenantUserMapper = new TenantUserMapper($databaseConnection);
			self::$tenantUserService = new TenantUserService($tenantUserMapper, $userManager);
		}
		return self::$tenantUserService;
	}

	/**
	 * Returns a StatService object
	 * @return StatService
	 */
	public static function getStatService() {
		if (self::$statService == null) {
			$databaseConnection = self::getDatabaseConnection();
			$statMapper = new StatMapper($databaseConnection);
			self::$statService = new StatService($statMapper);
		}
		return self::$statService;
	}

	/**
	 * Returns a ProjectService object
	 * @return ProjectService
	 */
	public static function getProjectService() {
		if (self::$projectService == null) {
			$databaseConnection = self::getDatabaseConnection();
			$projectMapper = new ProjectMapper($databaseConnection);
			self::$projectService = new ProjectService($projectMapper);
		}
		return self::$projectService;
	}

	/**
	 * Returns a CollaboratorService object
	 * @return CollaboratorService
	 */
	public static function getCollaboratorService() {
		if (self::$collaboratorService == null) {
			$databaseConnection = self::getDatabaseConnection();
			$collaboratorMapper = new CollaboratorMapper($databaseConnection);
			$userManager = self::getUserManager();
			$tenantService = self::getTenantService();
			$tenantConfigService = self::getTenantConfigService();
			$collaboratorTokenService = self::getCollaboratorTokenService();
			self::$collaboratorService = new CollaboratorService($collaboratorMapper, $userManager, $tenantService, $tenantConfigService, $collaboratorTokenService);
		}
		return self::$collaboratorService;
	}

	/**
	 * Returns a CollaboratorService object
	 * @return CollaboratorService
	 */
	public static function getCollaboratorTokenService() {
		if (self::$collaboratorService == null) {
			$databaseConnection = self::getDatabaseConnection();
			$collaboratorTokenMapper = new CollaboratorTokenMapper($databaseConnection);
			$collaboratorMapper = new CollaboratorMapper($databaseConnection);
			self::$collaboratorTokenService = new CollaboratorTokenService($collaboratorTokenMapper, $collaboratorMapper);
		}
		return self::$collaboratorTokenService;
	}

	/**
	 * Return UID of current user
	 * @return string
	 */
	public static function currentUser() {
		$user = \OC::$server->getUserSession()->getUser();
		return ($user ? $user->getUID() : NULL);
	}

	/**
	 * Return UID in it's proper case
	 * @param string $uid user id
	 * @return string
	 */
	public static function getRealUID($uid) {
		$userManager = self::getUserManager();
		$users = $userManager->search($uid, 1);

		if (count($users) > 0) {
			$uids = array_keys($users);
			$user = $users[$uids[0]];
			if (strtolower($uid) === strtolower($user->getUID())) {
				return $user->getUID();
			}
		}
		return FALSE;
	}

	/**
	 * Check if a user has authorisation for any tenant
	 * @param string $uid user id
	 * @return boolean
	 */
	public static function hasAuthorisation($uid) {
		$tenantUserService = self::getTenantUserService();
		return $tenantUserService->hasAuthorisation($uid);
	}

	/*************************
	 * Tenant Portal Helpers
	 ************************/

	/**
	 * Check if a user is authorised on a tenant
	 * @param string $tid tenant id
	 * @param string $uid user id
	 * @return boolean
	 */
	public static function isAuthorised($tid, $uid) {
		$tenantUserService = self::getTenantUserService();
		return $tenantUserService->isAuthorised($tid, $uid);
	}

	/**
	 * Check if a user is an admin
	 * @param string $uid user id
	 * @return boolean
	 */
	public static function isAdmin($uid) {
		$groupManager = self::getGroupManager();
		return $groupManager->isAdmin($uid);
	}

	/**
	 * Check if a user is authorised on a tenant
	 * @param integer $tid tenant id
	 */
	public static function checkAuthorised($tid) {
		$tenantManager = new TenantManager();
		$tenant = $tenantManager->get($tid);
		$uid = self::currentUser();
		if ($tenantManager->exists($tid) &&
			(self::isAuthorised($tid, $uid) || self::isAdmin($uid))
		) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Returns whether a user matches a domain
	 * @return bool
	 */
	public static function matchUserDomain($user, $domains=[]) {
		$user_split = explode('@', $user);
		if (count($user_split) > 1) {
			$user_domain = end($user_split);
			foreach ($domains as $domain) {
				$user_domain_regex = "/^(?:(?:.+\.)+)?".preg_quote($domain, '/')."$/i";
				if (preg_match($user_domain_regex, $user_domain)) {
					return true;
				}
			}
		}
		return false;
	}

  /**
  * Returns filesize in a human readable format and allows negative numbers
  * @param int $bytes
  * @return string
  */
	public static function toHumanFilesize($bytes) {
		$bytes =  \OCP\Util::computerFileSize($bytes);
		$negative = (substr(trim($bytes), 0, 1) === "-");
		if ($negative) {
			$bytes = $bytes * -1;
		}
		$human = \OCP\Util::humanFileSize($bytes);
		if ($negative) {
			$human = "-".$human;
		}
		return $human;
	}

	/**
	 * Converts a filesize in bytes to the specified unit
	 * @param integer $bytes
	 * @param string $unit
	 * @throws Exception
	 * @return string
	 */
	public static function toCommonFilesizeUnit($bytes, $unit) {
		$unit = strtoupper($unit);
		$result = null;
		switch ($unit) {
			case "B":
				$result = $bytes;
				break;
			case "KB":
				$result = round($bytes / 1024, 0);
				break;
			case "MB":
				$result = round($bytes / pow(1024, 2), 2);
				break;
			case "GB":
				$result = round($bytes / pow(1024, 3), 2);
				break;
			case "TB":
				$result = round($bytes / pow(1024, 4), 2);
				break;
			case "PB";
				$result = round($bytes / pow(1024, 5), 2);
				break;
			default:
				throw new Exception("Invalid unit specified");

		}
		return $result;
	}

	/**
	 * Converts a filesize string to bytes
	 *
	 * @param string $quota
	 * @return int|FALSE
	 */
	public static function configQuotaToBytes($quota) {
		if (preg_match("/^[0-9]+$/", $quota)) {
			return $quota;
		} else {
			return \OCP\Util::computerFileSize($quota);
		}
	}

	/**
	 * Generate random string
	 * @param int $len (max of 128 in worst case)
	 * @return string
	 */
    public static function randomString($len=32, $special_chars=null, $uppercase_required=false) {
        # generate a random string
        $random = bin2hex(random_bytes($len));
        $random = substr($random, 0, $len);
        # make sure we have a lower case character
        $add_chars = chr(rand(97,122));
        # make sure we have a number
        $add_chars = random_int(0,9);

        # add a special character if required
        if ($special_chars) {
            $special_chars = str_split($special_chars);
            $random_char = array_rand($special_chars);
            $add_chars .= $special_chars[$random_char];
        }

        # add an uppercase character if required
        if ($uppercase_required) {
                $add_chars .= chr(rand(65,90));
        }

        # trim our random string to length and add our extra characters
        $random = substr($random, 0, $len-strlen($add_chars)) . $add_chars;

        # shuffle the string
        $random = str_shuffle($random);
        return $random;
    }


	/**
	 * Generate token
	 * @return string
	 */
	public static function generateToken() {
		return bin2hex(random_bytes(32));
	}

	/**
	 * Check that string is a valid email address
	 * @param string $mail
	 * @return boolean
	 */
	public static function validEmail($mail) {
		return \OC::$server->getMailer()->validateMailAddress($mail);
	}

	/**
	 * Quick accessor to do debug messages
	 * @param string $message
	 */
	public static function debugLog($message) {
		\OCP\Util::writeLog('tenant_portal', $message, \OCP\Util::DEBUG);
	}

	/*************************
	 * Third party functions *
	 *************************/

	/**
	 * Formats data for server side processing in DataTables.js
	 *
         * Copied from the DataTables server side processing example
         * https://github.com/DataTables/DataTablesSrc/blob/master/examples/server_side/scripts/ssp.class.php
	 */
    public static function formatDataTables($columns, $data) {
                $out = array();
                for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
                        $row = array();
                        for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) {
                                $column = $columns[$j];
                                // Is there a formatter?
                                if ( isset( $column['formatter'] ) ) {
                                        $row[ $column['dt'] ] = $column['formatter']( $data[$i][ $column['db'] ], $data[$i] );
                                }
                                else {
                                        $row[ $column['dt'] ] = $data[$i][ $columns[$j]['db'] ];
                                }
                        }
                        $out[] = $row;
                }
                return $out;
        }
}

