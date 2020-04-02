<?php

namespace OCA\Tenant_Portal\AppInfo;

use \OCP\AppFramework\App;
use \OCA\Tenant_Portal\AppInfo\Application;
use \OCA\Tenant_Portal\Util;

$app = new Application();
$container = $app->getContainer();

// Add background job for updating stats
\OC::$server->getJobList()->add('\OCA\Tenant_Portal\BackgroundJob\StatUpdater');

// Only show the Tenant Portal on the top navigation if the user is an admin or authorised on a tenant
$user = Util::currentUser();
$hasAuth = Util::hasAuthorisation($user);
$isAdmin = Util::isAdmin($user);

// Only show tenant portal option if authorised
if ($hasAuth || $isAdmin) {
	$container->query('OCP\INavigationManager')->add(function () use ($container) {
		$urlGenerator = $container->query('OCP\IURLGenerator');
		return [
			'id' => "tenant_portal",
			'order' => 80,
			'href' => $urlGenerator->linkToRoute('tenant_portal.view.index'),
			'icon' => $urlGenerator->imagePath('tenant_portal', 'app.png'),
			'name' => 'Tenant Portal',
		];
	});
}
