<?php

namespace OCA\Tenant_Portal\Middleware\Exceptions;

use \OCP\AppFramework\Http;
use Exception;

class NotAuthorisedOnTenantException extends Exception {
	public function __construct() {
		parent::__Construct('Current user is not authorised on tenant', Http::STATUS_UNAUTHORIZED);
	}
}
