( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.UserGroup = {
		/**
		 * Initialises form for adding user groups
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.TenantConfig.UserGroup;
			cmd.tableName = "#userGroupsTable";
			cmd.addForm = $("#addUserGroupForm");
			cmd.addButton = $("#addUserGroupButton");
			cmd.groupField = $("#addUserGroupName");
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
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/groups'+path);
		},

		/**
		 * Create a user group
		 */
		create: function() {
			userGroup = OCA.TenantPortal.TenantConfig.UserGroup;
			if ($.trim(userGroup.groupField.val()).length == 0) {
				return false;
			}
			userGroup.addButton.val("Creating...");
			userGroup.addForm.children('input').prop('disabled', true);
			$.post(
				OCA.TenantPortal.TenantConfig.UserGroup.baseUrl(),
				{ group: userGroup.groupField.val() },
				function(result) {
				}
			)
			.done(function (result) {
				userGroup.groupField.val('');
				userGroup.updateTable();
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to create a new user group or the group already exists.", "Unable to create user group");
			})
			.always(function() {
				userGroup.addButton.val("Create");
				userGroup.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Remove an user group
		 * @param {Object} el
		 */
		remove: function(el) {
			$.ajax({
				url: OCA.TenantPortal.TenantConfig.UserGroup.baseUrl($(el).data('id')),
				method: 'DELETE',
				contentType: 'application/json'
			})
			.done(function(result) {
				OCA.TenantPortal.TenantConfig.UserGroup.updateTable();
			})
			.fail(function() {
				OC.dialogs.alert("Error: unable to remove the user group", "Unknown Error"); //TODO: Better error
			});
		},

		/**
		 * Update the user groups table
		 */
		updateTable: function(data) {
			userGroup = OCA.TenantPortal.TenantConfig.UserGroup;
			OCA.TenantPortal.Common.ajaxDataTable(userGroup.tableName,{
				ajax: {
					url: userGroup.baseUrl(),
					dataSrc: ""
				},
				autowidth: false,
				columns:[
					{data: "config_value", width: "40%"},
					{
						className: 'actions',
						orderable: false,
						data: "id",
						width: "10%",
						render: function (data, type, full, meta) {
							var actions = '<a class="userGroupName action permanent view" title="Edit group" data-id="'+data+'">'+
										  '<img src="'+OC.imagePath('tenant_portal', 'edit.svg')+'">'+
										  '</a>'+
										  '<a class="userGroupName action permanent delete" title="Delete group" data-id="'+data+'">'+
										  '<img src="'+OC.imagePath('tenant_portal', 'delete.svg')+'">'+
										  '</a>';
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.TenantConfig.UserGroup.initClickEvents(); },
				language: {
					loading: "Loading...",
					emptyTable: "No user groups assigned to this tenant."
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
		 * Initialise the click events for the user groups table
		 */
		initClickEvents: function () {
			common = OCA.TenantPortal.Common;
			common.rebind(".userGroupName.action.delete",'click', function() { OCA.TenantPortal.TenantConfig.UserGroup.remove(this); });
			common.rebind(".userGroupName.action.view",'click', function() { OCA.TenantPortal.TenantConfig.UserGroup.show(this); });
			$("#userGroupsTable tr td:not(:last-child)").on('click', function() {
				$(this).parent().find('td:last-child .action.view').click();
			});
		},

		/**
		 * Show the members for a group
		 * @param {Object} el
		 */
		show: function (el) {
			tenant_id = OCA.TenantPortal.Show.tenantId;
			group_id = $(el).data('id');
			if ((typeof id !== "undefined")) {
				url = OC.generateUrl('apps/tenant_portal/show/'+tenant_id+'/groups/'+group_id);
				$.colorbox({
					open: true,
					opacity: 0.4,
					speed: 100,
					width: "70%",
					height: "70%",
					html: ''+
'		<div class="colorbox-content" data-id="'+group_id+'">'+
'			<div id="addUserGroupMember">'+
'				<input type="text" class="addInput" name="addUserGroupMemberName" id="addUserGroupMemberName" placeholder="Add Member">'+
'				<input type="button" type="submit" id="addUserGroupMemberButton" class="tenantButton" value="Add">'+
'			</div>'+
'			<table class="grid hover nowrap" id="userGroupMembersTable" width="100%">'+
'				<thead>'+
'					<th>User</th>'+
'					<th>&nbsp;</th>'+
'				</thead>'+
'				<tbody>'+
'					<tr>'+
'						<td class="userName" colspan="6">Loading...</td>'+
'					</tr>'+
'				</tbody>'+
'			</table>'+
'		</div>'
				});
				OCA.TenantPortal.TenantConfig.UserGroup.updateMembersTable();
				$("#addUserGroupMemberButton").on('click', function() { OCA.TenantPortal.TenantConfig.UserGroup.addMember(this); });
			}
		},

		/**
		 * Update the members table when viewing a group
		 */
		updateMembersTable: function() {
			tableName = "#userGroupMembersTable";
			cbox = $('.colorbox-content');
			if (cbox.length > 0) {
				groupId = cbox.data("id");
				if ($.fn.dataTable.isDataTable(tableName)) {
					$(tableName).DataTable().ajax.reload(null, false);
				} else {
					$(tableName).DataTable({
						ajax: {
							url: OCA.TenantPortal.TenantConfig.UserGroup.baseUrl(groupId),
							dataSrc: ""
						},
						autowidth: false,
						columns:[
							{data: "username", width: "90%"},
							{
								className: 'actions',
								orderable: false,
								data: "username",
								width: "10%",
								render: function (data, type, full, meta) {
									group_id = $('.colorbox-content').data("id");
									var actions = '<a class="userGroupMemberName action permanent delete" data-group-id="'+group_id+'" data-id="'+data+'" href="#">'+
												  '<img src="'+OC.imagePath('tenant_portal', 'remove.svg')+'">'+
												  '</a>';
									return actions;
								}
							}
						],
						drawCallback: function (settings) { OCA.TenantPortal.TenantConfig.UserGroup.initMemberClickEvents(); },
						language: {
							loading: "Loading...",
							emptyTable: "No users in this group."
						},
					});
				}
			}
		},

		/**
		 * Add a member to a group
		 */
		addMember: function() {
			cbox = $('.colorbox-content');
			group_id = cbox.data("id");
			$.post(
				OCA.TenantPortal.TenantConfig.UserGroup.baseUrl(group_id+'/addMember'),
				{user: $("#addUserGroupMemberName").val()},
				function () {}
			).done(function (result) {
				if (result) {
					$("#addUserGroupMemberName").val('');
					OCA.TenantPortal.TenantConfig.UserGroup.updateMembersTable();
				}
			}).fail(function (result) {
				OC.dialogs.alert("Error: unable to add the user to the user group", "Unknown Error"); //TODO: Better error
			});
		},

		/**
		 * Remove a member from a group
		 * @param {Object} el
		 */
		removeMember: function(el) {
			user_id = $(el).data("id");
			cbox = $('.colorbox-content');
			group_id = cbox.data("id");
			OCdialogs.confirm(t('tenant_portal','Are you sure you want to remove {userid}?', {userid: user_id}), '', function(result) {
				if (result) {
					$.post(
						OCA.TenantPortal.TenantConfig.UserGroup.baseUrl(group_id+'/removeMember'),
						{user: $(el).data("id")},
						function () {}
					).done(function (users) {
						OCA.TenantPortal.TenantConfig.UserGroup.updateMembersTable();
					}).fail(function (result) {
						OC.dialogs.alert("Error: unable to remove the user from the user group", "Unknown Error"); //TODO: Better error
					});
				}
			}, true);
		},

		/**
		 * Initialise the click events for the members table
		 */
		initMemberClickEvents: function () {
			$(".userGroupMemberName.action.delete").off();
			$(".userGroupMemberName.action.delete").on('click', function() { OCA.TenantPortal.TenantConfig.UserGroup.removeMember(this); });
		},
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.UserGroup.initialise();});
