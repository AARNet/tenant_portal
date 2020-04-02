
<?php
script('tenant_portal', Array(
	'datatables',
	'common',
        'jquery.colorbox',
	'tenant.show',
	'tenant.show.user',
	'config',
	'config.additional_user',
	'user.impersonate',
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
		<span class="breadcrumb_page">Users</span>
	</h1>
	<div class="section_description">
		<p>Manage and view all users that are currently assigned to this account.</p>
	</div>
	<div class="section">
		<?php if ($_['AllowAdd']) { ?>
		<div id="addAdditonalUser" class="tenantInlineForm">
			<form id="addAdditionalUserForm">
				<input type="text" class="addInput" name="addAdditionalUserName" id="addAdditionalUserName" placeholder="Assign additional user">
				<input type="submit" id="addAdditionalUserButton" class="tenantButton" value="Assign">
			</form>
		</div>
		<?php } ?>
		<table class="grid hover nowrap" id="usersTable" width="100%">
			<thead>
				<th>Username</th>
				<th>Display Name</th>
				<th>Used Quota</th>
				<th>Quota Limit</th>
				<th>Last Login</th>
				<th>&nbsp;</th>
			</thead>
			<tbody>
				<tr>
					<td class="userName" colspan="6">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
