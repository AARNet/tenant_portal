<?php

return [
	'resources' => [
		'tenant_config' => 	['url' => '/tenants/{tenant_id}/config'],
		'tenant_user' =>	['url' => '/tenants/{tenant_id}/user'],
		'stats' => 			['url' => '/tenants/{tenant_id}/stats'],
		'user_group' => 	['url' => '/tenants/{tenant_id}/groups'],
		'collaborator' => 	['url' => '/tenants/{tenant_id}/collaborators'],
		'project' => 		['url' => '/tenants/{tenant_id}/projects'],
		'tenant' => 		['url' => '/tenants' ],
	],
	'routes' => [
		// Main views
		// ------------
		['name' => 'view#index', 'url' => '/', 'verb' => 'GET'],
                ['name' => 'view#tenant_authorised_users', 'url' => '/admin/authorised-users', 'verb' => 'GET'],
		['name' => 'view#tenant_show', 'url' => '/show/{tenant_id}', 'verb' => 'GET'],
		['name' => 'view#tenant_config', 'url' => '/show/{tenant_id}/admin', 'verb' => 'GET'],
		['name' => 'view#tenant_users', 'url' => '/show/{tenant_id}/users', 'verb' => 'GET'],
		['name' => 'view#tenant_user_groups', 'url' => '/show/{tenant_id}/groups', 'verb' => 'GET'],
		['name' => 'view#tenant_collaborators', 'url' => '/show/{tenant_id}/collaborators', 'verb' => 'GET'],
		['name' => 'view#tenant_projects', 'url' => '/show/{tenant_id}/projects', 'verb' => 'GET'],
		['name' => 'view#tenant_audit_log', 'url' => '/show/{tenant_id}/auditlog', 'verb' => 'GET'],
		['name' => 'view#collaborator_request_reset', 'url' => '/collaborators/reset/', 'verb' => 'GET'],
		['name' => 'view#collaborator_reset_password', 'url' => '/collaborators/reset/{token}', 'verb' => 'GET'],
		// Ajax-y things
		// -------------
		// User list
		['name' => 'user#get_users', 'url' => '/tenants/{tenant_id}/getUsers', 'verb' => 'GET'],
		// Domain list
		['name' => 'tenant_config#get_assigned_domains', 'url' => '/tenants/{tenant_id}/getAssignedDomains', 'verb' => 'GET'],
		// Stat charts
		['name' => 'stat#get_total_users', 'url' => '/tenants/{tenant_id}/stats/getTotalUsers', 'verb' => 'GET'],
		['name' => 'stat#get_storage_used', 'url' => '/tenants/{tenant_id}/stats/getStorageUsed', 'verb' => 'GET'],
		// Stat CSVs
		['name' => 'stat#csv_total_users', 'url' => '/tenants/{tenant_id}/stats/csv/totalUsers', 'verb' => 'GET'],
		['name' => 'stat#csv_storage_used', 'url' => '/tenants/{tenant_id}/stats/csv/storageUsed', 'verb' => 'GET'],
		['name' => 'user#csv_users', 'url' => '/tenants/{tenant_id}/stats/csv/users', 'verb' => 'GET'],
		// Stat cards
		['name' => 'stat#get_stat_cards', 'url' => '/tenants/{tenant_id}/stats/getStatCards', 'verb' => 'GET'],
		// Set Tenant Quota
		['name' => 'tenant_config#set_quota', 'url' => '/tenants/{tenant_id}/setQuota', 'verb' => 'POST'],
		// Impersonate
		['name' => 'user#impersonate', 'url' => '/tenants/{tenant_id}/user/impersonate', 'verb' => 'POST', 'requirements' => ['user_id' => '.+']],
		['name' => 'user#set_quota', 'url' => '/tenants/{tenant_id}/user/setQuota', 'verb' => 'POST', 'requirements' => ['user_id' => '.+']],
		['name' => 'tenant_config#toggle_impersonate', 'url' => '/tenants/{tenant_id}/toggleImpersonate', 'verb' => 'POST'],
		// User groups
		['name' => 'user_group#add_member', 'url' => '/tenants/{tenant_id}/groups/{group_id}/addMember', 'verb' => 'POST'],
		['name' => 'user_group#remove_member', 'url' => '/tenants/{tenant_id}/groups/{group_id}/removeMember', 'verb' => 'POST'],
		// Collaborators
		['name' => 'collaborator#generate_password_reset_token', 'url' => '/collaborators/generateResetToken', 'verb' => 'POST', 'requirements' => ['collaborator_uid' => '.+']],
		['name' => 'collaborator#reset_password', 'url' => '/collaborators/resetPassword', 'verb' => 'POST', 'requirements' => ['token' => '[a-zA-Z0-9]+', 'password']],
		// Projects
		['name' => 'project#get_members', 'url' => '/tenants/{tenant_id}/projects/{project_id}/getMembers', 'verb' => 'GET'],
		['name' => 'project#add_member', 'url' => '/tenants/{tenant_id}/projects/{project_id}/addMember', 'verb' => 'POST'],
		['name' => 'project#remove_member', 'url' => '/tenants/{tenant_id}/projects/{project_id}/removeMember', 'verb' => 'DELETE'],
		['name' => 'project#set_quota', 'url' => '/tenants/{tenant_id}/projects/{project_id}/setQuota', 'verb' => 'POST'],
		// Audit log
		['name' => 'audit_log#show', 'url' => '/tenants/{tenant_id}/auditlog', 'verb' => 'GET'],
	]
];

