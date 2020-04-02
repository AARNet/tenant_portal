(function () {
	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	/*
	Project management
	*/
	OCA.TenantPortal.Project = {
		/**
		 * Initialise the table, forms and events
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.Project;
			cmd.tableName = "#projectTable";
			cmd.addNameField = $("#addProjectName");
			cmd.addQuotaField=  $("#addProjectQuota");
			cmd.addForm = $("#addProjectForm");
			cmd.addButton = $("#addProjectButton");

			cmd.addForm.on('submit', function(e) {
				e.preventDefault();
				OCA.TenantPortal.Project.create();

			});
			cmd.updateTable();
		},

		/**
		 * Provides URL for ajax queries
		 * @param {string} path
		 * @return {string}
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') {
				path = '/'+path;
			}
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/projects'+path);
		},

		/**
		 * Create a new project
		 */
		create: function() {
			cmd = OCA.TenantPortal.Project;
			if ($.trim(cmd.addNameField.val()).length == 0) {
				return false;
			}
			cmd.addButton.val("Creating...");
			cmd.addForm.children('input').prop('disabled', true);
			$.post(
				cmd.baseUrl(),
				{ project_name: cmd.addNameField.val(), quota: cmd.addQuotaField.val() }
			)
			.done(function(result) {
				cmd.addNameField.val('');
				cmd.addQuotaField.val('');
				cmd.updateTable();
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to add new group drive. The name may be invalid or already in use.", "Invalid Group Drive");
			})
			.always(function () {
				cmd.addButton.val("Create");
				cmd.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Remove a project
		 * @param {Object} el
		 */
		remove: function(el) {
			name = $(el).parent().parent().children("td:first-child").text();
			cmd = OCA.TenantPortal.Project;
			OC.dialogs.confirm(
				t('tenant_portal', 'Are you sure you want to remove {name}?', {name: name}),
				'', function(result) {
						if (result) {
							$.ajax({
							url: cmd.baseUrl($(el).data('id')),
							method: 'DELETE',
							contentType: 'application/json'
						})
						.done(function(result) {
							cmd.updateTable();
						})
						.fail(function() {
							OC.dialogs.alert("Error: unable to remove the group drive","Unable to remove group drive"); //TODO: Better error
						});
					}
				},
			true);
		},

		/**
		 * Initailise click events for the projects table
		 */
		initClickEvents: function() {
			common = OCA.TenantPortal.Common;
			common.rebind(".projectName.action.delete", "click", function() { OCA.TenantPortal.Project.remove(this); });
			common.rebind(".projectName.action.view", "click", function() { OCA.TenantPortal.Project.Members.show(this); });
			common.rebind("#projectTable tr td:not(:last-child)", "click", function() { $(this).parent().find('td:last-child .action.view').click(); });
		},

		/**
		 * Update the projects table
		 */
		updateTable: function() {
			cmd = OCA.TenantPortal.Project;
			OCA.TenantPortal.Common.ajaxDataTable(cmd.tableName, {
				ajax: {
					url: cmd.baseUrl(),
					dataSrc: ""
				},
				columns:[
					{data: "folder", width: "40%"},
					{data: "used_quota", width: "10%"},
					{data: "quota", width: "10%"},
					{
						className: 'actions',
						orderable: false,
						data: "id",
						width: "10%",
						render: function (data, type, full, meta) {
							var actions = ''
									actions += '<a class="projectName action permanent view" title="View" data-id="'+data+'" data-quota="'+full.quota+'">'+
													   '<img src="'+OC.imagePath('tenant_portal', 'edit.svg')+'">'+
					   								 '</a>';
									actions += '<a class="projectName action permanent delete" title="Delete" data-id="'+data+'">'+
														 '<img src="'+OC.imagePath('tenant_portal', 'delete.svg')+'">'+
														 '</a>';
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.Project.initClickEvents(); },
				autowidth: false,
				language: {
					emptyTable: "No group drives assigned to this tenant."
				},
				order: [0, "asc"],
				pageLength: 100,
				lengthChange: true,
				responsive: false
			});
		}
	}

	/*
	  Project member management
	*/
	OCA.TenantPortal.Project.Members = {
		/**
		 * Initialise the members table and click events
		 */
		initialise: function (el) {
			cmd = OCA.TenantPortal.Project.Members;
			cmd.projectId = $(el).data('id');
			cmd.projectQuota = $(el).data('quota');
			cmd.projectName = $(el).parent().parent().children('td').first().text();
			cmd.quotaField = $('#setQuotaField');
			cmd.quotaButton = $('#setQuotaButton');
			cmd.tableName = "#projectMembersTable";
			cmd.addForm = $('#addProjectMemberForm');
			cmd.addButton = $("#addProjectMemberButton");
		},

		/**
		 * Shows the emember list for a project
		 * @param {ObjecT} el
		 */
		show: function (el) {
			tenant_id = OCA.TenantPortal.Show.tenantId;
			project_id = $(el).data('id');
			if ((typeof project_id !== "undefined")) {
				url = OCA.TenantPortal.Project.baseUrl();
				cmd = OCA.TenantPortal.Project.Members;
				cmd.initialise(el);
				cmd.createColorbox();
				cmd.updateTable();
				OCA.TenantPortal.Common.rebind('#addProjectMemberForm', 'submit', function(e) { OCA.TenantPortal.Project.Members.add(); e.preventDefault(); });
				OCA.TenantPortal.Common.rebind("#projectSetQuotaForm", "submit", function(e) { OCA.TenantPortal.Project.Members.setQuota(this); e.preventDefault() });
			}
		},

		/**
		 * Creates the colourbox for a project
		 */
		createColorbox: function() {
			cmd = OCA.TenantPortal.Project.Members;
			$.colorbox({
				open: true,
				opacity: 0.4,
				speed: 100,
				width: "70%",
				height: "75%",
				html: ''+
							'		<div class="colorbox-content projectView" data-id="'+cmd.projectId+'">'+
							'			<div>'+
							'				<h2>'+cmd.projectName+'</h2>'+
							'				<table class="projectDetails">'+
							'					<tr>'+
							'						<td>Quota:</td>'+
							'						<td>'+
							'							<form id="projectSetQuotaForm">'+
							'								<input type="text" class="" id="setQuotaField" value="'+cmd.projectQuota+'">'+
							'								<input type="submit" id="setQuotaButton" class="tenantButton" value="Set">'+
							'							</form>'+
							'						</td>'+
							'					</tr>'+
							'				</table>'+
							'			</div>'+
							'			<div id="addProjectMember">'+
							'				<h2>Group Members</h2>'+
							'				<form id="addProjectMemberForm">'+
							'					<input type="text" class="addInput" name="addProjectMemberName" id="addProjectMemberName" placeholder="Add Member">'+
							'					<input type="submit" id="addProjectMemberButton" class="tenantButton" value="Add">'+
							'				</form>'+
							'			</div>'+
							'			<table class="grid hover nowrap" id="projectMembersTable" width="100%">'+
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
		},

		/**
		 * Updates the member table
		 */
		updateTable: function (project_id) {
			cmd = OCA.TenantPortal.Project;
			OCA.TenantPortal.Common.ajaxDataTable('#projectMembersTable', {
				ajax: {
					url: cmd.baseUrl(cmd.Members.projectId+'/getMembers'),
					dataSrc: ""
				},
				columns:[
					{data: "uid", width: "40%"},
					{
						className: 'actions',
						orderable: false,
						data: "uid",
						width: "10%",
						render: function (data, type, full, meta) {
							var actions = '';
									actions += '<a class="projectMember action permanent delete" title="Delete" data-id="'+data+'">'+
														 '<img src="'+OC.imagePath('tenant_portal', 'delete.svg')+'">'+
														 '</a>';
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.Project.Members.initClickEvents(); },
				autowidth: false,
				language: {
					emptyTable: "No users assigned to this group drive."
				},
				order: [0, "asc"],
				pageLength: 10,
				lengthChange: false,
				responsive: false
			});
		},

		/**
		 * Initailise click events for a member
		 */
		initClickEvents: function () {
			common = OCA.TenantPortal.Common;
			common.rebind(".projectMember.action.delete", "click", function() { OCA.TenantPortal.Project.Members.remove(this); });
		},

		/**
		 * Add a member
		 */
		add: function() {
			cmd = OCA.TenantPortal.Project.Members;
			uid = $("#addProjectMemberName").val();
			if ($.trim(uid).length == 0) {
				return false;
			}
			$('#addProjectMemberButton').val("Adding...")
			$('#addProjectMemberForm').children("input").prop("disabled", true);
			$.ajax({
				url: OCA.TenantPortal.Project.baseUrl(cmd.projectId+"/addMember"),
				method: 'POST',
				data: { "uid": uid }
			})
			.done(function(result) {
				cmd.updateTable();
				$('#addProjectMemberName').val('');
			})
			.fail(function(result) {
				error_message = "Error: unable to add user";
				if (result.responseJSON) {
					if (result.responseJSON.message.indexOf('already shared') >= 0) {
						error_message = "Error: user is already a member of the group drive";
					}
					if (result.responseJSON.message.indexOf('does not exist') >= 0) {
						error_message = "Error: user does not exist or has not logged in before.";
					}
				}
				OC.dialogs.alert(error_message,"Unable to add user"); //TODO: Better error
			})
			.always(function() {
				$('#addProjectMemberButton').val("Add")
				$('#addProjectMemberForm').children("input").prop("disabled", false);
			});
		},

		/**
		 * Remove a member
		 * @param {Object} el
		 */
		remove: function(el) {
			name = $(el).parent().parent().children("td:first-child").text();
			cmd = OCA.TenantPortal.Project.Members;
			uid = $(el).data('id');
			OC.dialogs.confirm(
				t('tenant_portal', 'Are you sure you want to remove {name}?', {name: name}),
				'', function(result) {
						if (result) {
							$.ajax({
							url: OCA.TenantPortal.Project.baseUrl(cmd.projectId+"/removeMember"),
							method: 'DELETE',
							data: { "uid": uid }
						})
						.done(function(result) {
							cmd.updateTable();
						})
						.fail(function() {
							OC.dialogs.alert("Error: unable to remove this user from the group drive","Unable to remove user"); //TODO: Better error
						});
					}
				},
			true);
		},

		/**
		 * Set quota for the project
		 */
		setQuota: function() {
			cmd = OCA.TenantPortal.Project.Members;
			quota = $('#setQuotaField').val();
			if ($.trim(quota).length == 0) {
				return false;
			}

			$('#setQuotaButton').prop('disabled', true);
			currentVal = $('#setQuotaButton').val("Saving...");
			$.post(
				OCA.TenantPortal.Project.baseUrl(cmd.projectId+"/setQuota"),
				{ quota: quota }
			)
			.done(function (result) {
				$('#setQuotaButton').val("Saved!");
				OCA.TenantPortal.Project.updateTable();
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to set quota at this time.", "Unable to set quota");
			})
			.always(function () {
				$('#setQuotaButton').prop('disabled', false);
				setTimeout(function(){$('#setQuotaButton').val("Set");} , 3000);
			});
		}
	}
})();

$(document).ready(function() {
	OCA.TenantPortal.Project.initialise();
});
