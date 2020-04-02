( function() {
	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.AuditLog = {
		/**
		 * Initialises the AuditLog table
		 */
		initialise: function() {
			log = OCA.TenantPortal.AuditLog;
			// Initialise static vars
			log.tableName = "#logTable";
			log.baseUrl = OC.generateUrl('/apps/tenant_portal/tenants/'+OCA.TenantPortal.Show.tenantId+'/auditlog');
			// Load users into table
			log.updateTable();
		},

		/**
		 * Renders the users table body
		 * @param {Object} data
		 */
		updateTable: function(data) {
			log = OCA.TenantPortal.AuditLog;
			if ($.fn.dataTable.isDataTable(log.tableName)) {
				$(log.tableName).DataTable().ajax.reload(null, false);
			} else {
				$(log.tableName).DataTable({
					processing: true,
					serverSide: true,
					ajax: log.baseUrl,
					autowidth: false,
					columns: [
						{ width: "200px" },
						{ width: "300px" },
						null
					],
					language: {
						loading: "Loading...",
						emptyTable: "No log entries found for this tenant."
					},
					order: [0, "asc"],
					info: false,
					pageLength: 100,
					pagingType: "numbers",
					lengthChange: true,				
					responsive: false,
					ordering: false
				});
			}
		}
	};
})();
$(document).ready(function(){OCA.TenantPortal.AuditLog.initialise();});
