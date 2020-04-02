<?php
script('tenant_portal', Array(
	'datatables',
	'common',
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
		<span class="breadcrumb_page">All Authorised Users</span></h2>
	</h1>
	<div class="section_description">
		<p>This is just a full list of all authorised users in the Tenant Portal.</p>
	</div>
	<div class="section">
		<table id="tenantsTable" class="grid hover nowrap" width="100%">
			<thead>
				<tr>
					<th>Authorised Users</th>
				</tr>
			</thead>
			<tbody>
<?php 
			foreach ($_['tenantAuthorisedUsers'] as $email) {
?>
				<tr><td><?php p($email); ?></td></tr>
<?php
			}
?>
			</tbody>
		</table>
	</div>
</div>
