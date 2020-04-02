<?php

namespace OCA\Tenant_Portal\Middleware\Exceptions;

use \OCP\AppFramework\Http;
use Exception;

class TenantNotFoundException extends Exception {
	public function __construct() {
		parent::__Construct('Unable to find the requested tenant', Http::STATUS_NOT_FOUND);
	}
}
