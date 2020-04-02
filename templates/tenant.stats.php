<?php
script('tenant_portal', Array(
	'Chart.min',
	'datatables',
	'common',
	'tenant.show',
	'tenant.show.charts',
	'tenant.show.statcards',
));
style('tenant_portal', 'datatables.min');
style('tenant_portal', 'tenant');

$_['Navigation']->printPage();
?>
<div id="app-content">
	<h1 class="pageHeading" id="tenantId" data-id="<?php p($_['TenantId']);?>">
		<?php p($_['Tenant']->getName()); ?>
		<span class="breadcrumb_divider">/</span>
		<span class="breadcrumb_page">Statistics</span></h2>
	</h1>
	<div class="section">
		<div id="statCards">
		</div>
		<div class="statChart">
			<canvas id="usersChart"></canvas>
		</div>
		<div class="statChart">
			<canvas id="storageChart"></canvas>
		</div>
		<div class="statCSVs">
			<div class="statCSV">
				<h3>Download data as CSV</h3>
				<span>
					<a href="<?php p($_['URLGenerator']->linkToRoute('tenant_portal.stat.csv_total_users', Array("tenant_id"=>$_['TenantId'])));?>" class="tenantButton">Total Users Chart</a>
					<a href="<?php p($_['URLGenerator']->linkToRoute('tenant_portal.stat.csv_storage_used', Array("tenant_id"=>$_['TenantId'])));?>" class="tenantButton">Storage Used Chart</a>
					<a href="<?php p($_['URLGenerator']->linkToRoute('tenant_portal.user.csv_users', Array("tenant_id"=>$_['TenantId'])));?>" class="tenantButton">User Information</a>
				</span>
			</div>
		</div>
	</div>
</div>

