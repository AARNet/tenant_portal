( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.AuthUser = {
		/**
		 * Initialises the Authorised Users table
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.AuthUser;
			cmd.tableName = '#authorisedUsersTable';
			cmd.addForm = $("#addAuthorisedUserForm");
			cmd.addButton = $('#addAuthorisedUserButton');
			cmd.userField = $('#addAuthorisedUserName');
			cmd.updateTable();
			cmd.addForm.on('submit', function(e) { OCA.TenantPortal.AuthUser.create(); e.preventDefault(); });
		},

		/**
		 * Intitialises mouse click events for dynamic content
		 */
		initClickEvents: function() {
			$(".authorisedUserName.action.delete").off();
			$(".authorisedUserName.action.delete").on('click', function() { OCA.TenantPortal.AuthUser.remove(this); });
		},

		/**
		 * Returns URL for Ajax requests
		 * @param {string} path
		 * @return {string} URL
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') { path = '/'+path; }
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/user'+path);
		},

		/**
		 * Creates a new authorised user
		 */
		create: function() {
			authUser = OCA.TenantPortal.AuthUser;
			if ($.trim(authUser.userField.val()).length == 0) {
				return false;
			}
			authUser.addButton.val("Adding..");
			authUser.addForm.children('input').prop('disabled', true);
			$.post(
				authUser.baseUrl(),
				{ user_id: $.trim(authUser.userField.val()) },
				function(result) {
					if (typeof result.message === 'undefined') {
						authUser.userField.val('');
						authUser.updateTable();
					} else {
						OC.dialogs.alert(result.message, "Unable to authorise user");
					}
				}
			)
			.fail(function () {
				OC.dialogs.alert("Error: unable to add new authorised user. The user may not exist or is already assigned to an account.", "Unable to Authorise User"); //TODO: Better error
			})
			.always(function () {
				authUser.addButton.val("Add");
				authUser.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Deletes an authorised user
		 * @param {Object} el
		 */
		remove: function(el) {
			user_id = $(el).parent().parent().children("td:first-child").text();
			authUser = OCA.TenantPortal.AuthUser;
			OC.dialogs.confirm(t('tenant_portal','Are you sure you want to remove {userid} as an authorised user?', {userid: user_id}), '', function(result) {
				if (result) {
					$.ajax({
						url: authUser.baseUrl($(el).data('id')),
						method: 'DELETE',
						contentType: 'application/json'
					})
					.done(function(result) { authUser.updateTable(); })
					.fail(function() {
						OC.dialogs.alert("Error: unable to remove the authorised user", "Unknown Error");
					});
				}
			}, true);
		},
		/**
		 * Performs Ajax request and loads data into Authorised Users table
		 */
		updateTable: function() {
			authUser = OCA.TenantPortal.AuthUser;
			OCA.TenantPortal.Common.ajaxDataTable(authUser.tableName,{
				ajax:{
					url: authUser.baseUrl(),
                                        dataSrc: function (json) {
                                                authorised_users = json.authorised_users.map(function(d) { d["showAdminActions"] = json.admin; return d; });
                                                return authorised_users;
                                        }
				},
				columns:[
					{data: "user_id"},
					{
						className: 'actions',
						orderable: false,
						data: "id",
						width: "15rem",
						render: function (data, type, full, meta) {
                                                        actions = '';
                                                        if (full.showAdminActions) {
                                                                actions = '<a class="authorisedUserName action permanent delete" title="Remove authorised user" data-id="'+data+'">'+
                                                                          '<img src="'+OC.imagePath('tenant_portal', 'remove.svg')+'">'+
                                                                          '</a>';
                                                        }
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.AuthUser.initClickEvents(); },
				language: {
					loading: "Loading...",
					emptyTable: "No authorised users found for this tenant."
				},
				order: [0, "desc"],
				ordering: false,
				info: false,
				pageLength: 10,
				pagingType: "numbers",
				searching: false,
				lengthChange: false,
			});
		},
	};
})();

$(document).ready(function(){OCA.TenantPortal.AuthUser.initialise();});
