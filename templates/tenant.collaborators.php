<?php
script('tenant_portal', Array(
	'datatables',
	'jquery.colorbox',
	'common',
	'tenant.show',
	'config',
	'config.collaborators',
));
style('tenant_portal', Array(
	'datatables.min',
	'colorbox',
	'tenant',
));

$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading" id="tenantId" data-id="<?php p($_['TenantId']);?>">
		<?php p($_['Tenant']->getName()); ?>
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">Collaborators</span>
	</h1>
	<div class="section_description">
        <p>Collaborators are users that require access to CloudStor but do not exist within your institution's identity provider. These users will be able to login by selecting "CloudStor Virtual Hosted Account" when trying to access CloudStor.</p>
	</div>
	<div class="section">
		<div id="addCollaborator" class="tenantInlineForm">
			<form id="addCollaboratorForm">
				<input type="text" class="addInput" name="addCollaboratorEmail" id="addCollaboratorEmail" placeholder="Email address">
				<input type="text" class="addInput" name="addCollaboratorName" id="addCollaboratorName" placeholder="Display Name">
				<input type="submit" id="addCollaboratorButton" class="tenantButton" value="Create">
			</form>
		</div>
		<table class="grid hover nowrap" id="collaboratorsTable" width="100%">
			<thead>
				<th>Email Address</th>
				<th>Display Name</th>
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
