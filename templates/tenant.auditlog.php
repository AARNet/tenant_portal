
<?php
script('tenant_portal', Array(
	'datatables.min',
	'common',
	'tenant.show',
	'tenant.show.auditlog',
));
style('tenant_portal', 'datatables.min');
style('tenant_portal', 'tenant');


$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading" id="tenantId" data-id="<?php p($_['TenantId']);?>">
		<?php p($_['Tenant']->getName()); ?>
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">Audit Log</span>
	</h1>
	<div class="section_description">
		<p>View all actions made by authorised users on your tenant via the tenant portal.</p>
	</div>
	<div class="section">
		<table class="grid hover nowrap" id="logTable" width="100%">
			<thead>
				<th>Timestamp</th>
				<th>User</th>
				<th>Info</th>
			</thead>
			<tbody>
				<tr>
					<td class="userName" colspan="4">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
