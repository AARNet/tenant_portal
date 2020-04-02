( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.Show) {
		OCA.TenantPortal.Show = {};
	}

	OCA.TenantPortal.Charts = {
		/**
		 * Initialises the stat charts
		 */
		initialise: function() {
			charts = OCA.TenantPortal.Charts;
			charts.totalUsersChartId = '#usersChart';
			charts.storageUsedChartId = '#storageChart';
			charts.renderTotalUsersChart();
			charts.renderStorageUsedChart();

		},

		/**
		 * Returns the URL for Ajax requests
		 * @param {string} path
		 * @return {string} URL
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') { path = '/'+path; }
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/stats'+path);
		},

		/**
		 * Renders the Total Users chart
		 */
		renderTotalUsersChart: function() {
			charts = OCA.TenantPortal.Charts;
			charts.totalUsersData = {};
			$.ajax({
				url: charts.baseUrl('/getTotalUsers?pad=12'),
				method: 'GET'
			}).done(function (data) {
				charts.totalUsersChart = charts.chartConfig(charts.totalUsersChartId,
															 "Total Users for Last 12 Months",
															 data.labels,
															 data.values);
			});
		},

		/**
		 * Renders the Storage used chart
		 */
		renderStorageUsedChart: function() {
			charts = OCA.TenantPortal.Charts;
			charts.storageUsedData = {};
			$.ajax({
				url: charts.baseUrl('/getStorageUsed?pad=12'),
				method: 'GET'
			}).done(function (data) {
				charts.storageUsedChart = charts.chartConfig(charts.storageUsedChartId,
															 "Storage Used ("+data.units+") for Last 12 Months",
															 data.labels,
															 data.values);
			});
		},

		/**
		 * Renders a Chart.js chart
		 * @param {string} id
		 * @param {Object} data
		 * @param {Object} options
		 */
		renderChart: function(id, data, options) {
			return new Chart($(id).get(0).getContext("2d"), {
				type: 'line',
				data: data,
				options: options
			});
		},

		/**
		 * Returns a config for Chart.js line chart
		 * @param {string} chartid
		 * @param {string} title
		 * @param {Array} labels
		 * @param {Array} values
		 * @return {Object}
		 */
		chartConfig: function(chartid, title, labels, values) {
			return OCA.TenantPortal.Charts.renderChart(
					chartid,
					{
						labels: labels,
						datasets: [{
								data: values,
								spanGaps: true,
								backgroundColor: "rgba(255,147,2, 0.2)",
								borderColor: "#FF9302",
								pointColor: "#FF9302",
								pointStrokeColor: "#fff",
								pointHighlightFill: "#fff",
								pointHighlightStroke: "#97bbcd",
								lineTension: 0.1
							}]
					},
					{
						title: {
							display: true,
							text: title
						},
						responsive: true,
						legend: false,
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true,
									min: 0,
									max: charts.maxToNearest(10, values)
								}
							}]
						}
					}
				);


		},

		/**
		 * Finds the maximum value and rounds it up to the nearest x (defaults to 10)
		 * @param {integer} nearest
		 * @param {Array} values
		 * @return {integer}
		 */
		maxToNearest: function(nearest, values) {
			if (typeof nearest === 'undefined') {
				nearest = 10;
			}
			// Get max value in array
			maxValue = Math.max.apply(Math, values.filter(function(n) { return n != undefined }));
			// Return value rounded up to the nearest 10
			return Math.ceil(maxValue/nearest)*nearest;
		}
	}
})();

$(document).ready(function(){ OCA.TenantPortal.Charts.initialise(); });
