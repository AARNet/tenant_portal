<?php
script('tenant_portal', Array(
	'datatables',
	'common',
	'tenant.index',
));
style('tenant_portal', Array(
	'datatables.min',
	'tenant',
));

$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading">
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">Tenant Index</span></h2>
	</h1>
	<div class="section">

		<div id="addTenant" class="tenantInlineForm">
			<form id="addTenantForm">
				<input type="text" name="addTenantCode" id="addTenantCode" placeholder="Tenant Short Code">
				<input type="text" name="addTenantName" id="addTenantName" placeholder="Tenant Name">
				<input type="submit" class="tenantButton" id="addTenantButton" value="Add">
			</form>
		</div>
		<table id="tenantsTable" class="grid hover nowrap" width="100%">
			<thead>
				<tr>
					<th>Tenant Code</th>
					<th>Tenant Name</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
