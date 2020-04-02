<?php
script('tenant_portal', Array(
	'datatables',
	'jquery.colorbox',
	'common',
	'tenant.show',
	'projects',
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
		<span class="breadcrumb_page">Group Drives</span>
	</h1>
	<div class="section_description">
		<p>Manage and view all group drives that are currently assigned to this account.</p>
	</div>
	<div class="section">
		<div id="addProject" class="tenantInlineForm">
			<form id="addProjectForm">
				<input type="text" class="addInput" name="addProjectName" id="addProjectName" placeholder="Create group drive">
				<input type="text" class="addInput" name="addProjectQuota" id="addProjectQuota" placeholder="1 TB">
				<input type="submit" id="addProjectButton" class="tenantButton" value="Create">
			</form>
		</div>
		<table class="grid hover nowrap" id="projectTable" width="100%">
			<thead>
				<th>Name</th>
				<th>Used Quota</th>
				<th>Assigned Quota</th>
				<th>Actions</th>
			</thead>
			<tbody>
				<tr>
					<td class="projectName" colspan="6">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
