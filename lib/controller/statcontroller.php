<?php

namespace OCA\Tenant_Portal\Controller;

use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;
use \OCA\Tenant_Portal\Managers\StatManager;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http\DataDownloadResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCA\Tenant_Portal\Util;

class StatController extends Controller {

	protected $request;
	protected $tenantManager;
	protected $tenantUserManager;
	protected $tenantConfigManager;
	protected $statManager;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param TenantConfigManager $tenantConfigManager
	 * @param TenantManager $tenantManager
	 * @param TenantUserManager $tenantUserManager
	 * @param StatManager $statManager
	 */
	public function __construct($appName, IRequest $request, TenantConfigManager $tenantConfigManager, TenantManager $tenantManager, TenantUserManager $tenantUserManager, StatManager $statManager) {
		parent::__construct($appName, $request);
		$this->request = $request;
		$this->tenantConfigManager = $tenantConfigManager;
		$this->tenantManager = $tenantManager;
		$this->tenantUserManager = $tenantUserManager;
		$this->statManager = $statManager;
	}

	/**
	 * Returns a named array containing the total storage used
	 * stats for a tenant
	 *
	 * @param integer $tenant_id
	 * @param integer $pad How many dates to pad out the result to
	 * @param integer $limit
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function getStorageUsed($tenant_id, $pad=null, $limit=12) {
		$stats = $this->statManager->findAllByKey($tenant_id, 'totalStorageUsed');
		$result = $this->statManager->buildChartArray($stats, $pad, $limit);
		$largest = max($result["values"]);
		$largest_human = explode(' ', \OCP\Util::humanFileSize($largest));
		$unit = end($largest_human);

		$result['values'] = array_map(function ($v) use ($unit) { 
			if ($v !== null) {
				return Util::toCommonFilesizeUnit($v, $unit);
			}
			return null;
		}, $result['values']);
		$result['units'] = $unit;

		return new DataResponse($result); 
	}


	/**
	 * Generates a CSV containing the total storage used
	 * stats for a tenant
	 *
	 * @param integer $tenant_id
	 * @param integer $pad How many dates to pad out the result to
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function csvStorageUsed($tenant_id, $pad=null) {
		$stats = $this->statManager->findAllByKey($tenant_id, 'totalStorageUsed');
		$data = $this->statManager->buildCSVArray($stats, $pad);
		array_unshift($data, Array('timestamp', 'bytes_used'));
		$csv = $this->statManager->generateCSV($data);
		return new DataDownloadResponse($csv, "cloudstor-storage-used.csv", "text/csv");
	}

	/**
	 * Returns a named array containing the total user 
	 * count stats for a tenant
	 *
	 * @param integer $tenant_id
	 * @param integer $pad How many dates to pad out the result to
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function getTotalUsers($tenant_id, $pad=null) {
		$stats = $this->statManager->findAllByKey($tenant_id, 'totalUsers');
		$result = $this->statManager->buildChartArray($stats, $pad);
		return new DataResponse($result); 
	}

	/**
	 * Generates a CSV containing the total user 
	 * count stats for a tenant
	 *
	 * @param integer $tenant_id
	 * @param integer $pad How many dates to pad out the result to
	 * @return DataResponse
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function csvTotalUsers($tenant_id, $pad=null) {
		$stats = $this->statManager->findAllByKey($tenant_id, 'totalUsers');
		$data = $this->statManager->buildCSVArray($stats, $pad);
		array_unshift($data, Array('timestamp', 'total_users'));
		$csv = $this->statManager->generateCSV($data);
		return new DataDownloadResponse($csv, "cloudstor-total-users.csv", "text/csv");
	}

	/**
	 * Return a named array containing the stat card information
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 *
	 */
	public function getStatCards($tenant_id) {
		$result = Array();
		$users = $this->tenantUserManager->getUserInfo($tenant_id);

		$defaultquota = 1099511627776; // 1 TiB in bytes
		$usedStorage = 0;
		$usedPurchasedStorage = 0;
		$purchasedStorage = $this->tenantManager->getQuota($tenant_id);

		foreach ($users as $user) {
            $usedStorage += $user["used_bytes"];
            if ($user["type"] == "additional" || $user["type"] == "collaborator" || $user["type"] == "project") {
                $usedPurchasedStorage += $user["used_bytes"];
            }
            elseif ($user["used_bytes"] > $defaultquota) {
                $usedPurchasedStorage += ($user["used_bytes"] - $defaultquota);
            }
		}

		$result = Array(
			'total_users' => count($users),
			'storage_used_bytes' => $usedStorage,
			'storage_used_human' => \OCP\Util::humanFileSize($usedStorage),
			'purchased_storage' => \OCP\Util::humanFileSize($purchasedStorage),
			'purchased_storage_used' => \OCP\Util::humanFileSize($usedPurchasedStorage),
			'purchased_storage_remaining' => Util::toHumanFileSize(($purchasedStorage - $usedPurchasedStorage)),
			'purchased_storage_used_bytes' => $usedPurchasedStorage,
			'purchased_storage_remaining_bytes' => $purchasedStorage - $usedPurchasedStorage
		);

		return new DataResponse($result);	
	}

}
