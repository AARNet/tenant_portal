<?php
script('tenant_portal', Array(
	'Chart.min',
	'datatables',
	'common',
	'tenant.show',
	'config.auth_user',
	'config',
	'config.domain',
	'config.setquota',
	'config.impersonate'
));
style('tenant_portal', 'datatables.min');
style('tenant_portal', 'tenant');

$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading" id="tenantId" data-id="<?php p($_['TenantId']);?>">
		<?php p($_['Tenant']->getName()); ?>
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">Administration</span>
	</h1>
	<div class="section_description">
    </div>

    <div class="section">
        <h2 id="tenantDetails">Tenant Details</h2>
        <table class="tenantDetails">
            <tr>
                <td>Name:</td>
                <td><?php p($_['Tenant']->getName()); ?></td>
            </tr>
            <?php if ($_['AllowAdd']) { ?>
            <tr>
                <td>Short Code:</td>
                <td><?php p($_['Tenant']->getCode()); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td>Purchased Quota:</td>
                <?php if ($_['AllowAdd']) { ?>
				<td>
					<form id="setQuotaForm">
						<input type="text" value="<?php p($_['TenantQuotaHuman']); ?>" id="setQuotaField">
						<button type="submit" id="setQuotaButton" class="tenantButton">Set</button>
					</form>
				</td>
                <?php } else { ?>
                <td><?php p($_['TenantQuotaHuman']); ?></td>
                <?php } ?>
			</tr>
			<?php if ($_['AllowAdd']) { ?>
			<tr>
				<td>Impersonate Users</td>
				<td>
				<span id="impersonateStatus"><?php p($_['TenantImpersonateStatus'] ? "Enabled" : "Disabled"); ?></span>
				<button id="impersonateButton" class="tenantButton"><?php p($_['TenantImpersonateStatus'] ? "Disable" : "Enable"); ?></button>
				</td>
			</tr>
			<?php } ?>
        </table>
    </div>
	<div class="section">
		<h2 id="tenantAuthUsers">Authorised Users</h2>
		<p>These are users that are authorised to view this tenant account.</p>
		<?php if ($_['AllowAdd']) { ?>
		<div id="addAuthorisedUser" class="tenantInlineForm">
			<form id="addAuthorisedUserForm">
				<input type="text" class="addInput" name="addAuthorisedUserName" id="addAuthorisedUserName" placeholder="Authorise User">
				<input type="submit" id="addAuthorisedUserButton" class="tenantButton" value="Add">
			</form>
		</div>
		<?php } ?>
		<table class="grid hover nowrap" id="authorisedUsersTable" width="100%">
			<thead>
				<th>Authorised Users</th>
				<th>&nbsp;</th>
			</thead>
			<tbody>
				<tr>
					<td class="userName" colspan="2">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="section">
		<h2 id="tenantDomains">Assigned Domains</h2>
		<p>These are the domain names that are assigned to this tenant account, only the top-level domain is required as any sub-domains will be included as well. These domains are used to provide statistics for users who have an email address at the specified domain.</p>
		<?php if ($_['AllowAdd']) { ?>
		<div id="add_domain" class="tenantInlineForm">
			<form id="addDomainForm">
				<input type="text" class="addInput" name="addDomainName" id="addDomainName" placeholder="Assign domain">
				<input type="submit" id="addDomainButton" data-value='addDomainName' class="tenantButton" value="Assign">
			</form>
		</div>
		<?php } ?>
		<table class="grid hover nowrap" id="domainsTable" width="100%">
			<thead>
				<th>Domain Name</th>
				<th>&nbsp;</th>
			</thead>
			<tbody>
				<tr>
					<td class="domainName" colspan="2">Loading...</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
