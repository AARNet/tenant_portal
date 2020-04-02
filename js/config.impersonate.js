( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.Impersonate = {
		/**
		 * Initialise the option
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.TenantConfig.Impersonate;
			cmd.baseUrl = OC.generateUrl('/apps/tenant_portal/tenants/'+OCA.TenantPortal.Show.tenantId+'/toggleImpersonate');
			cmd.enableButton = $("#impersonateButton");
			cmd.statusField = $("#impersonateStatus");
			cmd.enableButton.on('click', function() { cmd.update(); });
		},

		/**
		 * Update the status of impersonate for the tenant
		 */
		update: function() {
			cmd = OCA.TenantPortal.TenantConfig.Impersonate;
			cmd.enableButton.prop('disable', true);

			$.post(
				cmd.baseUrl,
				null,
				function (result) { }
			)
			.done(function (result) {
				statusText = result.config_value === "true" ? "Enabled" : "Disabled";
				buttonText = result.config_value === "true" ? "Disable" : "Enable";
				cmd.statusField.text(statusText);
				cmd.enableButton.text(buttonText);
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to toggle the impersonate function at this time.", "Unable to toggle impersonate");
			});
			cmd.enableButton.prop('disable', false);
		}
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.Impersonate.initialise();});
