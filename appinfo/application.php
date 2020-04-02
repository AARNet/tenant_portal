<?php

namespace OCA\Tenant_Portal\AppInfo;

use \OCP\AppFramework\App;
use OCA\Tenant_Portal\Middleware\AuthorisedMiddleware;
use OCA\Tenant_Portal\Util;

class Application extends App {
	public function __construct(array $urlParams=array()) {
		parent::__construct('tenant_portal', $urlParams);

		$container = $this->getContainer();

		// Register the AuthorisedMiddleware for extra annotations
		$container->registerService('AuthorisedMiddleware', function ($c) use ($urlParams) {
			return new AuthorisedMiddleware(
				$c->query('Request'),
				$c->query('ControllerMethodReflector'),
				Util::currentUser()
			);
		});
		$container->registerMiddleware('AuthorisedMiddleware');
	}
}
