<?php
namespace OCA\Tenant_Portal\Controller;

use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\TenantUserManager;
use \OCA\Tenant_Portal\Managers\TenantConfigManager;

use \OCP\IRequest;
use \OCP\IGroupManager;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\RedirectResponse;
use \OCA\Tenant_Portal\Navigation;
use \OCA\Tenant_Portal\Util;

class ViewController extends Controller {

        protected $navigation;
        protected $groupManager;
        protected $tenantManager;
        protected $user;

        /**
         * @param string $appName
         * @param IRequest $request
         * @param Navigation $navigation
         * @param TenantManager $tenantManager
         */
        public function __construct($appName, IRequest $request, Navigation $navigation, TenantManager $tenantManager, TenantUserManager $tenantUserManager) {
                parent::__construct($appName, $request);
                $this->navigation = $navigation;
                $this->user = \OC::$server->getUserSession()->getUser();
                $this->tenantManager = $tenantManager;
                $this->tenantUserManager = $tenantUserManager;
        }

        /**
         * Returns the tenant list or a tenant depending
         * on if the current user is an admin or not
         *
         * @return TemplateResponse
         *
         * @NoAdminRequired
         * @NoCSRFRequired
         * @AuthorisedTenantUser
         */
        public function index() {
                $params = Array(
                        'URLGenerator' => \OC::$server->getURLGenerator(),
                        'isAdmin' => Util::isAdmin($this->user->getUID()),
                        'route' => 'tenant-list'
                );
                if (Util::isAdmin($this->user->getUID())) {
                        $params['Navigation'] = $this->navigation->getTemplate('admin', $params);
                        return new TemplateResponse($this->appName, 'tenant.index', $params);
                } else {
                        $tenantId = $this->tenantManager->getIdByUser($this->user->getUID());
                        return self::tenantShow($tenantId);
                }
        }


        /**
         * Returns a list of all authorised users
         *
         * @return TemplateResponse
         *
         * @NoAdminRequired
         * @NoCSRFRequired
         * @AuthorisedTenantUser
         */
        public function tenantAuthorisedUsers() {
                $params = Array(
                        'URLGenerator' => \OC::$server->getURLGenerator(),
                        'isAdmin' => Util::isAdmin($this->user->getUID()),
                        'route' => 'tenant-authuser-list',
                        'tenantAuthorisedUsers' => []
                );
                if (Util::isAdmin($this->user->getUID())) {
			$tenants = $this->tenantManager->findAll();
			foreach ($tenants as $tenant) {
				$users = $this->tenantUserManager->findAllByTenant($tenant->getId());
				foreach ($users as $user) {
					$params['tenantAuthorisedUsers'][] = $user->getUserId();
				}
			}
                        $params['Navigation'] = $this->navigation->getTemplate('admin', $params);
                        return new TemplateResponse($this->appName, 'tenant.admin.list', $params);
                } else {
                        $tenantId = $this->tenantManager->getIdByUser($this->user->getUID());
                        return self::tenantShow($tenantId);
                }
        }


	/**
	 * Renders the page.tenant.show page view if
	 * the user is authorised on a tenant
	 *
	 * @param integer $tenant_id
	 * @return TemplateResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
     */
	public function tenantShow($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-stats')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.stats', $params);
		}
	}

	/**
	 * Tenant Config Page
	 * @param int $tenant_id
	 * @return TemplateResponse|null
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
	 */
	public function tenantConfig($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$tenant_quota = $this->tenantManager->getQuota($tenant_id);
			$tenantConfigManager = new TenantConfigManager($tenant_id);
			$tenant_impersonate = $tenantConfigManager->getImpersonateStatus($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-config')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'TenantQuota' => $tenant_quota,
				'TenantQuotaHuman' => \OCP\Util::humanFileSize($tenant_quota),
				'TenantImpersonateStatus' => $tenant_impersonate,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.config', $params);
		}
	}

	/**
	 * Tenant Audit Log Page
	 * @param int $tenant_id
	 * @return TemplateResponse|null
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
	 */
	public function tenantAuditLog($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-audit-log')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
			);
			return new TemplateResponse($this->appName, 'tenant.auditlog', $params);
		}
	}

	/**
	 * Renders the page.tenant.show page view if
	 * the user is authorised on a tenant
	 *
	 * @param integer $tenant_id
	 * @return TemplateResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
   */
	public function tenantUsers($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-users')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.users', $params);
		}
	}

	/**
	 * Renders the tenant.groups page view if
	 * the user is authorised on a tenant
	 *
	 * @param integer $tenant_id
	 * @return TemplateResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
   */
	public function tenantUserGroups($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-usergroups')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.usergroups', $params);
		}
	}

	/**
	 * Renders the tenant.projects page view if
	 * the user is authorised on a tenant
	 *
	 * @param integer $tenant_id
	 * @return TemplateResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
   */
	public function tenantProjects($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-projects')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.projects', $params);
		}
	}

	/**
	 * Renders the tenant.collaborators page view if
	 * the user is authorised on a tenant
	 *
	 * @param integer $tenant_id
	 * @return TemplateResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
   */
	public function tenantCollaborators($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$tenant = $this->tenantManager->get($tenant_id);
			$params = Array(
				'Navigation' => $this->navigation->getTemplate($tenant_id, Array('route' => 'tenant-collaborators')),
				'URLGenerator' => \OC::$server->getURLGenerator(),
				'Tenant' => $tenant,
				'TenantId' => $tenant_id,
				'AllowAdd' => Util::isAdmin($this->user->getUID())
			);
			return new TemplateResponse($this->appName, 'tenant.collaborators', $params);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function collaboratorResetPassword($token=null) {
		if ($token) {
			$collaboratorTokenService = Util::getCollaboratorTokenService();
			$collaboratorManager = Util::getCollaboratorManager();
			$token = $collaboratorTokenService->validToken($token);
			if ($token && $collaboratorManager->collaboratorExistsById($token->getCollaboratorId())) {
				$params = Array(
					'token' => $token->getToken(),
					'user' => $collaboratorManager->getUserById($token->getCollaboratorId())
				);
			} else {
				$params = Array('token' => null);
			}
		}
		return new TemplateResponse($this->appName, 'collaborators.resetpass', $params, 'base');
	}
	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @PublicPage
	 */
	public function collaboratorRequestReset() {
		$params = Array();
		$response = new TemplateResponse($this->appName, 'collaborators.requestresetpass', $params, 'base');
		// Add exception to content security policy to allow loading of Recaptcha
		$policy = new \OCP\AppFramework\Http\ContentSecurityPolicy();
		$policy->addAllowedScriptDomain('www.google.com');
		$policy->addAllowedScriptDomain('www.gstatic.com');
		$policy->addAllowedFrameDomain('www.google.com');
		$response->setContentSecurityPolicy($policy);
		return $response;
	}
}
