( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.TenantConfig = {
		initialise: function() {
		},

		/**
		 * Returns URL for Ajax requests
		 * @param {string} path
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') {
				path = '/'+path;
			}
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/config'+path);
		}
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.initialise();});
