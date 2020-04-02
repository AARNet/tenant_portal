(function(){
	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	if (!OCA.TenantPortal.User) {
		OCA.TenantPortal.User = {};
	}

	OCA.TenantPortal.User.Impersonate = {
		initialise: function () {
			$('#usersTable').on('click', '.impersonate', function() {
				var user_id = $(this).data('id');
				OCdialogs.confirm(t('tenant_portal','Are you sure you want to impersonate {userid}?', {userid: user_id}), '', function(result) {
					if (result) {
						OCA.TenantPortal.User.Impersonate.changeUser(user_id);
					}
				}, true);
			});
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
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+path);
		},

		/**
		 * Impersonate the user
		 */
		changeUser: function (user_id) {
			$.post(
				OCA.TenantPortal.User.Impersonate.baseUrl('user/impersonate'),
				{ user_id: user_id }
			).done(function( result ) {
				window.location = OC.generateUrl('apps/files');
			}).fail(function( result ) {
				message = result.responseJSON || result.responseJSON.message;
				OC.dialogs.alert(message, 'Could not impersonate user');
			});
		}
	}
})();

$(document).ready(function () { OCA.TenantPortal.User.Impersonate.initialise(); });
