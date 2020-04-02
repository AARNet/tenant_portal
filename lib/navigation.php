<?php

namespace OCA\Tenant_Portal;

use \OCP\Template;
use \OCP\IURLGenerator;
use \OCA\Tenant_Portal\Util;

class Navigation {
	protected $URLGenerator;
	protected $active;

	/**
	 * @param IURLGenerator $URLGenerator
	 */
	public function __construct(IURLGenerator $URLGenerator) {
		$this->URLGenerator = $URLGenerator;
	}

	/**
	 * Returns template for rendering
	 * @param integer $tenant_id
	 * @param Array $params
	 * @return Template
	 */
	public function getTemplate($tenant_id, $params = null) {
		$template = new Template('tenant_portal', 'navigation');
		$entries = $this->generateNavItems($tenant_id, Util::isAdmin(Util::currentUser()));
		$template->assign('itemsNav', $entries);
		$template->assign('active', $params['route']);
		return $template;
	}

	/**
	 * Returns an array of navigation items
	 * @param integer $tenant_id
	 * @param boolean $isAdmin
	 * @return Array
	 */
	public function generateNavItems($tenant_id, $isAdmin = false) {
		$navItems = Array();
		if ($tenant_id) {
			if ($tenant_id != 'admin') {
				$navItems[] = Array(
					'id' => 'tenant-stats',
					'name' => 'Statistics',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_show', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-users',
					'name' => 'Users',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_users', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-usergroups',
					'name' => 'User Groups',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_user_groups', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-collaborators',
					'name' => 'Collaborators',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_collaborators', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-projects',
					'name' => 'Group Drives',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_projects', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-config',
					'name' => 'Administration',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_config', Array("tenant_id" => $tenant_id))
				);
				$navItems[] = Array(
					'id' => 'tenant-audit-log',
					'name' => 'Audit Log',
					'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_audit_log', Array("tenant_id" => $tenant_id))
				);
				if ($isAdmin) {
					$navItems[] = Array(
						'id' => 'tenant-list',
						'name' => 'Back to Tenant Index',
						'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.index')
					);
				}
			} else {
				if ($tenant_id == 'admin') {
					$navItems[] = Array(
						'id' => 'tenant-list',
						'name' => 'Tenant Index',
						'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.index')
					);
					$navItems[] = Array(
						'id' => 'tenant-admin-list',
						'name' => 'Authorised Users',
						'url' => $this->URLGenerator->linkToRoute('tenant_portal.view.tenant_authorised_users')
					);
				}
			}
		}
		return $navItems;
	}
}

