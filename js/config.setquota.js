
( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.Quota = {
		initialise: function() {
			quota = OCA.TenantPortal.TenantConfig.Quota;
			quota.baseUrl = OC.generateUrl('/apps/tenant_portal/tenants/'+OCA.TenantPortal.Show.tenantId+'/setQuota');
			quota.setButton = $("#setQuotaButton");
			quota.quotaField = $("#setQuotaField");
			quota.setButton.on('click', function() { quota.update(); });
		},

		/**
		 * Updates the tenants purchased quota
		 */
		update: function() {
			quota = OCA.TenantPortal.TenantConfig.Quota;
			if ($.trim(quota.quotaField.val()).length == 0) {
				return false;
			}

			quota.setButton.prop('disabled', true);
			$.post(
				quota.baseUrl,
				{ quota: quota.quotaField.val() },
				function (result) { }
			)
			.done(function (result) {
				currentVal = quota.setButton.text();
				quota.setButton.text("Saved!");
				setTimeout(function(){quota.setButton.text(currentVal);} , 3000);
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to set quota at this time.", "Unable to set quota");
			})
			.always(function () {
				quota.setButton.prop('disabled', false);
			});
		}
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.Quota.initialise();});
