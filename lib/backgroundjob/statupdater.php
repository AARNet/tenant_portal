<?php

namespace OCA\Tenant_Portal\BackgroundJob;

use \OCA\Tenant_Portal\Managers\StatManager;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;

use \OCA\Tenant_Portal\AppInfo\Application;
use \OC\BackgroundJob\TimedJob;

class StatUpdater extends TimedJob {

	public function __construct() {
		// Run once a day
		$this->interval = 86400;
	}

	/**
	 *  Gathers user and storage stats
	 *  @param mixed $argument
	 */
	public function run($argument) {
		$config = \OC::$server->getConfig();
        $statManager = new StatManager();
        $tenantManager = new TenantManager();
        $tenantUserManager = new TenantUserManager();
		$timestamp = date("Y-m-d H:i:s");

        // Get list of tenant ids
        $tenants = array_map(function ($tenant) {
            return $tenant->getId();
        }, $tenantManager->findAll());

        // Get user information for tenants
        foreach ($tenants as $tenant) {
            // Get user information
            $userInfo = $tenantUserManager->getUserInfo($tenant);

            // Calculate total storage used
            $totalStorage = array_sum(array_column($userInfo, "used_bytes"));
            // Calculate total users
            $totalUsers = count($userInfo);

            // Insert records into stats table
            $statManager->create($tenant, 'totalUsers', $totalUsers, $timestamp);
            $statManager->create($tenant, 'totalStorageUsed', $totalStorage, $timestamp);
        }
    }
}
