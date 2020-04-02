( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.AdditionalUser = {
		/**
		 * Initialises form for adding Additional Users
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.TenantConfig.AdditionalUser;
			cmd.addForm = $("#addAdditionalUserForm");
			cmd.addButton = $("#addAdditionalUserButton");
			cmd.userField = $("#addAdditionalUserName");
			cmd.addForm.on('submit', function(e) { cmd.create(); e.preventDefault(); });
		},

		/**
		 * Create an additional user
		 */
		create: function() {
			additionalUser = OCA.TenantPortal.TenantConfig.AdditionalUser;
			if ($.trim(additionalUser.userField.val()).length == 0) {
				return false;
			}
			additionalUser.addButton.val("Assigning...");
			additionalUser.addForm.children('input').prop('disabled', true);
			$.post(
				OCA.TenantPortal.TenantConfig.baseUrl(),
				{ name: 'additional_user', value: additionalUser.userField.val() },
				function(result) {
				}
			)
			.done(function (result) {
				additionalUser.userField.val('');
				additionalUser.updateTable();
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to assign an additional user to this account. The user may not exist or already be assigned to the account.", "Unable to assign additional user");
			})
			.always(function () {
				additionalUser.addButton.val("Assign");
				additionalUser.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Remove an additional user
		 * @param {Object} el
		 */
		remove: function(el) {
			user_id = $(el).parent().parent().children("td:first-child").text()
			OC.dialogs.confirm(t('tenant_portal','Are you sure you want to remove {userid} as an additional user from this tenant?', {userid: user_id}), '', function(result) {
				if (result) {
					$.ajax({
						url: OCA.TenantPortal.TenantConfig.baseUrl($(el).data('id')),
						method: 'DELETE',
						contentType: 'application/json'
					})
					.done(function(result) {
						OCA.TenantPortal.TenantConfig.AdditionalUser.updateTable();
					})
					.fail(function() {
						OC.dialogs.alert("Error: unable to remove the additional user", "Unknown Error"); //TODO: Better error
					});
				}
			}, true);
		},

		/**
		 * Updates the users table
		 */
		updateTable: function () { OCA.TenantPortal.User.updateTable(); },
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.AdditionalUser.initialise();});
