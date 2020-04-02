<?php

namespace OCA\Tenant_Portal\Middleware;

use \OCP\AppFramework\Middleware;
use \OCP\AppFramework\Http\JSONResponse;
use \OCP\AppFramework\Http\TemplateResponse;
use \OC\AppFramework\Utility\ControllerMethodReflector;

use \OCA\Tenant_Portal\Util;
use \OCA\Tenant_Portal\Managers\TenantManager;
use \OCA\Tenant_Portal\Middleware\Exceptions\NotAuthorisedOnTenantException;
use \OCA\Tenant_Portal\Middleware\Exceptions\TenantNotFoundException;
use \OCP\IRequest;

class AuthorisedMiddleware extends Middleware {

	/* @var ControllerMethodReflector */
	private $reflector;

	/* @var Array */
	private $urlParams;

	/* @var User */
	private $user;

	/* @var boolean */
	private $isAuthorised;

	/* @var boolean */
	private $isAdmin;

	/* @var IRequest */
	private $request;

	private $tenantManager;

	/*
	 * @param IRequest $request
	 * @param ControllerMethodReflector $reflector
	 * @param User $currentUser
	 */
	public function __construct(IRequest $request, ControllerMethodReflector $reflector, $currentUser) {
		$this->request = $request;
		$this->reflector = $reflector;
		$this->urlParams = $request->urlParams;
		$this->user = $currentUser;
		$this->tenantManager = new TenantManager();
		$this->tid = isset($this->urlParams["tenant_id"]) ? $this->urlParams["tenant_id"] : $this->tenantManager->getIdByUser($this->user);
		$this->isAdmin = Util::isAdmin($this->user);
		$this->isAuthorised = $this->tid ? Util::isAuthorised($this->tid, $this->user) : FALSE;
	}

	/**
	 * @param Controller $controller
	 * @param string $methodName
	 */
	public function beforeController($controller, $methodName) {
		if ($this->reflector->hasAnnotation('AuthorisedTenantUser')) {
			// Util::debugLog("@AuthorisedTenantUser [tid=>".$this->tid.", user=>".$this->user.", authorised=>".$this->isAuthorised.", admin=>".$this->isAdmin."]");
			// Throw exception if the user is not authorised or an admin
			if (!$this->isAuthorised && !$this->isAdmin)  {
				throw new NotAuthorisedOnTenantException();
			}
			// Throw exception if there is no tenant ID and the user is not an admin
			if (!$this->isAdmin && !$this->tid) {
				throw new TenantNotFoundException();
			}
		}
	}

	/**
	 * @param Controller $controller
	 * @param string $methodName
	 * @param Exception $exception
	 */
	public function afterException($controller, $methodName, \Exception $exception) {
		// If it's not a HTML response, return JSON
		if ($this->request && stripos($this->request->getHeader('Accept'), 'html') === false) {
			$response = new JSONResponse(
				['message' => $exception->getMessage()],
				$exception->getCode()
			);
		} else {
			// Show Forbidden on NotAuthorised Exception
			if ($exception instanceof NotAuthorisedOnTenantException) {
				$response = new TemplateResponse('core', '403', ['file' => $exception->getMessage()], 'guest');
				$response->setStatus($exception->getCode());
			}
			// Show not found if tenant doesn't exist
			if ($exception instanceof TenantNotFoundException) {
				$response = new TemplateResponse('core', '404', ['file' => $exception->getMessage()], 'guest');
				$response->setStatus($exception->getCode());
			}
		}
		if (isset($response)) {
			return $response;
		}
		throw $exception;
	}
}
