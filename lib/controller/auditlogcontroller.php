<?php
namespace OCA\Tenant_Portal\Controller;

use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Managers\AuditLogManager;

use \OCP\IRequest;
use \OCP\AppFramework\Controller;
use \OCP\AppFramework\Http\TemplateResponse;
use \OCP\AppFramework\Http\DataResponse;
use \OCA\Tenant_Portal\Navigation;
use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Service\TenantService;
use \OCA\Tenant_Portal\Service\TenantUserService;

class AuditLogController extends Controller {

	protected $navigation;
	protected $tenantManager;
	protected $auditLogManager;
	protected $user;
	protected $request;

	/**
	 * @param string $appName
	 * @param IRequest $request
	 * @param Navigation $navigation
	 * @param TenantManager $tenantManager
	 * @param AuditLogMangaer $auditLogManager
	 */
	public function __construct($appName, IRequest $request, Navigation $navigation, TenantManager $tenantManager, AuditLogManager $auditLogManager) {
		parent::__construct($appName, $request);
		$this->navigation = $navigation;
		$this->user = \OC::$server->getUserSession()->getUser();
		$this->tenantManager = $tenantManager;
		$this->auditLogManager = $auditLogManager;
		$this->request = $request;
	}

	/**
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
	 */
	public function index() {
		$tenantId = $this->tenantManager->getIdByUser($this->user->getUID());
		return self::show($tenantId);
	}

	/**
	 * @param integer $tenant_id
	 * @return DataResponse
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @AuthorisedTenantUser
	 */
	public function show($tenant_id) {
		if (Util::checkAuthorised($tenant_id)) {
			$result = $this->auditLogManager->get($tenant_id, $this->request->getParam('length'), $this->request->getParam('start'), $this->request->getParam('search')['value'], $this->request);
			return new DataResponse($result);
		}
	}
}
