( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.Collaborator = {
		/**
		 * Initialises form for adding collaborators
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.TenantConfig.Collaborator;
			cmd.tableName = "#collaboratorsTable";
			cmd.addForm = $("#addCollaboratorForm");
			cmd.addButton = $("#addCollaboratorButton");
			cmd.nameField = $("#addCollaboratorName");
			cmd.emailField = $("#addCollaboratorEmail");
			cmd.addForm.on('submit', function(e) { cmd.create(); e.preventDefault(); });
			cmd.updateTable();
		},

		/**
		 * Builds the URL for ajax calls
		 * @param {string} path
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') {
				path = '/'+path;
			}
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/collaborators'+path);
		},

		/**
		 * Add a collaborator
		 */
		create: function() {
			user = OCA.TenantPortal.TenantConfig.Collaborator;
			if (($.trim(user.emailField.val()).length == 0) || ($.trim(user.nameField.val()).length == 0)) {
				return false;
			}
			user.addButton.val("Creating...");
			user.addForm.children('input').prop('disabled', true);
			$.post(
				OCA.TenantPortal.TenantConfig.Collaborator.baseUrl(),
				{ email: user.emailField.val(), name: user.nameField.val() },
				function(result) { }
			)
			.done(function (result) {
				user.emailField.val('');
				user.nameField.val('');
				user.updateTable();
			})
			.fail(function (result) {
				message = "unable to add collaborator as the user is invalid or already exists.";
				if (typeof result.responseJSON !== 'undefined') {
					message = result.responseJSON.message;
				}
				OC.dialogs.alert("Error: "+message, "Unable to add collaborator");
			})
			.always(function() {
				user.addButton.val("Create");
				user.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Remove a collaborator
		 * @param {Object} el
		 */
		remove: function(el) {
			user_id = $(el).parent().parent().children("td:first-child").text();
			OC.dialogs.confirm(t('tenant_portal','Are you sure you want to remove {userid} as a collaborator?', {userid: user_id}), '', function(result) {
				if (result) {
					$.ajax({
						url: OCA.TenantPortal.TenantConfig.Collaborator.baseUrl($(el).data('id')),
						method: 'DELETE',
						contentType: 'application/json'
					})
					.done(function(result) {
						OCA.TenantPortal.TenantConfig.Collaborator.updateTable();
					})
					.fail(function() {
						OC.dialogs.alert("Error: unable to remove collaborator", "Unknown Error"); //TODO: Better error
					});
				}
			});
		},

		/**
		* Updates the collaborator table
		* @param {Array} data
		*/
		updateTable: function(data) {
			user = OCA.TenantPortal.TenantConfig.Collaborator;
			OCA.TenantPortal.Common.ajaxDataTable(user.tableName,{
				ajax: {
					url: user.baseUrl(),
					dataSrc: ""
				},
				autowidth: false,
				columns:[
					{data: "mail", width: "40%"},
					{data: "cn", width: "40%"},
					{
						className: 'actions',
						orderable: false,
						data: "id",
						width: "10%",
						render: function (data, type, full, meta) {
							var actions = ''+
										  '<a class="userName action permanent delete" title="Delete collaborator" data-id="'+data+'">'+
										  '<img src="'+OC.imagePath('tenant_portal', 'delete.svg')+'">'+
										  '</a>';
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.TenantConfig.Collaborator.initClickEvents(); },
				language: {
					loading: "Loading...",
					emptyTable: "No collaborators assigned to this tenant."
				},
				order: [0, "asc"],
				info: false,
				pageLength: 100,
				pagingType: "numbers",
				lengthChange: true,
				responsive: false
			});
		},

		/**
		 * Initialise the click events for the collaborator table
		 */
		initClickEvents: function () {
			$(".userName.action.delete").off();
			$(".userName.action.view").off();
			$(".userName.action.delete").on('click', function() { OCA.TenantPortal.TenantConfig.Collaborator.remove(this); });
			$(".userName.action.view").on('click', function() { OCA.TenantPortal.TenantConfig.Collaborator.show(this); });
		},
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.Collaborator.initialise();});
