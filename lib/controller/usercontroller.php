<?php

namespace OCA\Tenant_Portal\Controller;

use \OC\Authentication\Token\DefaultTokenProvider;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http;
use \OCP\AppFramework\Http\DataResponse;
use \OCP\AppFramework\Http\DataDownloadResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\IUserManager;
use \OCP\IUserSession;
use \OCP\ISession;

use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Navigation;

use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\StatManager;
use \OCA\Tenant_Portal\Managers\AuditLogManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;

class UserController extends Controller {
	protected $navigation;
	protected $session;
	protected $tokenProvider;
	protected $groupManager;
	protected $userManager;
	protected $userSession;
	protected $tenantManager;
	protected $statManager;
	protected $auditLogManager;
	protected $tenantUserManager;
	protected $tenantConfigManager;
	protected $user;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Navigation $navigation
	 */
	public function __construct($appName, IRequest $request, IUserManager $userManager, ISession $session, IUserSession $userSession, DefaultTokenProvider $tokenProvider, Navigation $navigation, AuditLogManager $auditLogManager, TenantManager $tenantManager, StatManager $statManager, TenantUserManager $tenantUserManager, TenantConfigManager $tenantConfigManager) {
	        parent::__construct($appName, $request);
	        $this->navigation = $navigation;
	        $this->session = $session;
	        $this->tokenProvider = $tokenProvider;
	        $this->userManager = $userManager;
	        $this->userSession = $userSession;
	        $this->auditLogManager = $auditLogManager;
	        $this->tenantManager = $tenantManager;
	        $this->tenantUserManager = $tenantUserManager;
	        $this->tenantConfigManager = $tenantConfigManager;
	        $this->statManager = $statManager;
	        $this->user = Util::currentUser();
	}


	/**
	 * Returns details for users assigned to a tenant
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function getUsers($tenant_id) {
		$result = $this->tenantUserManager->getUserInfo($tenant_id);
		$impersonate = $this->tenantConfigManager->getImpersonateStatus($tenant_id);
		$result = array_map(function($user) use ($impersonate) {
			$user["impersonate"] = $impersonate;
			return $user;
		}, $result);
		return new DataResponse($result);
	}

	/**
	 * Returns details for users assigned to a tenant
	 *
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 */
	public function csvUsers($tenant_id) {
		$users = Array();
		$result = $this->tenantUserManager->getUserInfo($tenant_id);
		$data = Array(Array("user_id", "display_name", "last_login", "quota_used_bytes", "quota_used_human", "quota_limit_bytes", "quota_limit_human"));
		foreach ($result as $user) {
			$data[] = Array(
				$user["user_id"],
				$user["display_name"],
				$user["last_login"],
				$user["used_bytes"],
				$user["used_human"],
				$user["quota_bytes"],
				$user["quota_human"]
			);
		}
		return new DataDownloadResponse($this->statManager->generateCSV($data), "cloudstor-users.csv", "text/csv");
	}

	/**
	 * Allow authorised users to impersonate other users on the tenant
	 *
	 * @UseSession
	 * @NoAdminRequired
	 * @AuthorisedTenantUser
	 *
	 * @return JSONResponse
	 */
	public function impersonate($tenant_id, $user_id) {
		$isAdmin = Util::isAdmin($this->user);
		$tenantUsers = Array();

		if (!$this->tenantConfigManager->getImpersonateStatus($tenant_id) && !$isAdmin) {
			return new JSONResponse("Impersonate is disabled on this tenant", Http::STATUS_UNAUTHORIZED);
		}
		$hasUser = $this->tenantManager->hasUser($tenant_id, $user_id);
		// Check to see if the user is part of the tenant
		if ($hasUser) { // || $isAdmin) {
			$user = $this->userManager->get($user_id);
			if ($user === null) {
				return new JSONResponse("No user found for $user_id", Http::STATUS_NOT_FOUND);
			}
			elseif (($user->getLastLogin() === 0) && (!$this->tenantUserManager->isProjectUser($tenant_id, $user_id))) {
				return new JSONResponse("User needs to login at least once before you can impersonate them.", Http::STATUS_FORBIDDEN);
			} else {
				$this->auditLogManager->logUpdate($tenant_id, "Impersonated '{$user->getUID()}'");
				$this->tokenProvider->invalidateToken($this->session->getId());
				$this->userSession->setUser($user);
				$this->userSession->createSessionToken($this->request, $user->getUID(), $user->getUID());
			}
		} else {
			return new JSONResponse("Unauthorized to impersonate user $user_id", Http::STATUS_UNAUTHORIZED);
		}
		return new JSONResponse();
	}

        /**
         * Set quota for user
         *
         * @param integer $tenant_id
         * @param string $user_id
         * @param string $quota
         * @return DataResponse
         *
         * @NoAdminRequired
         * @AuthorisedTenantUser
         */
        public function setQuota($tenant_id, $user_id, $quota) {
		$getQuota = Util::toHumanFileSize($this->tenantUserManager->getQuota($user_id));	
                $setQuota = $this->tenantUserManager->setQuota($user_id, $quota);
                $this->auditLogManager->logUpdate($tenant_id, "Quota for '{$user_id}' changed from '{$getQuota}' to '".Util::toHumanFilesize($quota)."'");
                return new DataResponse(Array("user" => $user_id, "success"=>$setQuota));
        }
}
