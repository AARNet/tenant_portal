<?php
script('tenant_portal', Array(
	'datatables',
	'jquery.colorbox',
	'common',
	'tenant.show',
	'config',
	'config.usergroup',
));
style('tenant_portal', 'datatables.min');
style('tenant_portal', 'colorbox');
style('tenant_portal', 'tenant');

$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading" id="tenantId" data-id="<?php p($_['TenantId']);?>">
		<?php p($_['Tenant']->getName()); ?>
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">User Groups</span>
	</h1>
	<div class="section_description">
		<p>Manage and view all user groups that are currently assigned to this account. All groups will be prefixed with your tenant short code.</p>
	</div>
	<div class="section">
		<div id="addUserGroup" class="tenantInlineForm">
			<form id="addUserGroupForm">
				<input type="text" class="addInput" name="addUserGroupName" id="addUserGroupName" placeholder="Create user group">
				<input type="submit" id="addUserGroupButton" class="tenantButton" value="Create">
			</form>
		</div>
		<table class="grid hover nowrap" id="userGroupsTable" width="100%">
			<thead>
				<th>Group Name</th>
				<th>Actions</th>
			</thead>
			<tbody>
				<tr>
					<td class="userName" colspan="6">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
