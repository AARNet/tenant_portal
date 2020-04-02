( function() {
	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.User = {
		/**
		 * Initialises the Users table
		 */
		initialise: function() {
			user = OCA.TenantPortal.User;
			// Initialise static vars
			user.tableName = "#usersTable";
			// Load users into table
			user.updateTable();
		},

		/**
		 * Initialises click events on dynamic content
		 */
		initClickEvents: function() {
                        common = OCA.TenantPortal.Common;
			common.rebind(".additionalUserName.action.delete", 'click', function() { OCA.TenantPortal.TenantConfig.AdditionalUser.remove(this); });
                        common.rebind(".additionalUserName.action.edit", "click", function() { OCA.TenantPortal.User.showDetailedView(this); });
			common.rebind("#usersTable tr td:not(:last-child)", "click", function() { $(this).parent().find('td:last-child .action.edit').click(); });
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
                        return OC.generateUrl('/apps/tenant_portal/tenants/'+id+path);
                },


		/**
		 * Renders the users table body
		 * @param {Object} data
		 */
		updateTable: function(data) {
			user = OCA.TenantPortal.User;
			user.lastData = {};
			if ($.fn.dataTable.isDataTable(user.tableName)) {
				$(user.tableName).DataTable().ajax.reload(null, false);
			} else {
				$(user.tableName).DataTable({
					ajax: {
						url: user.baseUrl('/getUsers'),
						dataSrc: ""
					},
					autowidth: false,
					columns:[
						{data: "user_id", width: "30%"},
						{data: "display_name", width: "30%"},
						{
							width: "10%",
							data: {
								"_": "used_bytes",
								"display": "used_human"
							},
						},
						{
							width: "10%",
							data: {
								"_": "quota_bytes",
								"display": "quota_human"
							}
						},
						{
							width: "10%",
							data: {
								"_": "last_login_epoch",
								"display": "last_login"
							},
						},
						{
							className: 'actions',
							orderable: false,
							data: "user_id",
							width: "10%",
							render: function (data, type, full, meta) { return OCA.TenantPortal.User.renderActions(full); }
						},
					],
					language: {
						loading: "Loading...",
						emptyTable: "No users found for this tenant."
					},
					drawCallback: function (settings) { OCA.TenantPortal.User.initClickEvents(); },
					order: [0, "asc"],
					info: false,
					pageLength: 100,
					pagingType: "numbers",
					lengthChange: true,
					responsive: false
				});
			}
		},
		renderActions: function (full) {
			var actions = '';
			OCA.TenantPortal.User.lastData[full.user_id] = {
				userName: full.user_id,
				displayName: full.display_name,
				quota: full.quota_human,
				usedQuota: full.used_human,
				lastLogin: full.last_login,
				impersonate: full.impersonate
			};
			actions += '<a class="additionalUserName action permanent edit" title="Edit" data-id="'+full.user_id+'">'+
					'<img src="'+OC.imagePath('tenant_portal', 'edit.svg')+'">'+
					'</a>';

			if (full.impersonate) {
				actions += '<a class="additionalUserName action permanent impersonate" title="Switch to this user" data-id="'+full.user_id+'">'+
						   '<img src="'+OC.imagePath('core', 'actions/user.svg')+'">'+
						   '</a>';
			}
			if (full.type === "additional" && typeof full.id !== "undefined") {
				actions += '<a class="additionalUserName action permanent delete" title="Remove additional user" data-id="'+full.id+'">'+
					       '<img src="'+OC.imagePath('core', 'actions/delete.svg')+'">'+
							   '</a>';
			} else {
				actions += '<a class="additionalUserName action permanent" style="visibility: hidden;" title="Remove additional user (disabled)">'+
					       '<img src="'+OC.imagePath('core', 'actions/delete.svg')+'">'+
							   '</a>';
			}
			return actions;
		},
                /**
                 * Creates the detailed view for a member
                 */
                showDetailedView: function(obj) {
			userId=$(obj).data('id');
			user = OCA.TenantPortal.User.lastData[userId];
                        $.colorbox({
                                open: true,
                                opacity: 0.4,
                                speed: 100,
                                width: "500px",
                                height: "300px",
                                html: ''+
                                      '               <div class="colorbox-content userView" data-id="'+user.userName+'">'+
                                      '                       <div>'+
                                      '                               <h2>User Details</h2>'+
                                      '                               <table class="userDetails">'+
                                      '                                       <tr>'+
                                      '                                               <td>Username:</td>'+
                                      '                                               <td>'+user.userName+'</td>'+
                                      '                                       </tr>'+
                                      '                                       <tr>'+
                                      '                                               <td>Display Name:</td>'+
                                      '                                               <td>'+user.displayName+'</td>'+
                                      '                                       </tr>'+
                                      '                                       <tr>'+
                                      '                                               <td>Used Quota:</td>'+
                                      '                                               <td>'+user.usedQuota+'</td>'+
                                      '                                       </tr>'+
                                      '                                       <tr>'+
                                      '                                               <td>Quota:</td>'+
                                      '                                               <td>'+
                                      '                                                       <form id="setQuotaForm">'+
                                      '                                                               <input type="text" class="" id="setQuotaField" value="'+user.quota+'">'+
                                      '                                                               <input type="submit" id="setQuotaButton" class="tenantButton" value="Set">'+
                                      '                                                       </form>'+
                                      '                                               </td>'+
                                      '                                       </tr>'+
                                      '                                       <tr>'+
                                      '                                               <td>Last Login:</td>'+
                                      '                                               <td>'+user.lastLogin+'</td>'+
                                      '                                       </tr>'+
                                      '                               </table>'+
                                      '                       </div>'+
                                      '               </div>'
                        });
			OCA.TenantPortal.Common.rebind("#setQuotaForm", "submit", function(e) { OCA.TenantPortal.User.setQuota(); e.preventDefault() });
                },
                /**
                 * Set quota for the project
                 */
                setQuota: function() {
                        cmd = OCA.TenantPortal.User;
			uid = $('.userView').data('id');
                        quota = $('#setQuotaField').val();
                        if ($.trim(quota).length == 0) {
                                return false;
                        }

                        $('#setQuotaButton').prop('disabled', true);
                        currentVal = $('#setQuotaButton').val("Saving...");
                        $.post(
                                OCA.TenantPortal.User.baseUrl("user/setQuota"),
                                { user_id: uid, quota: quota }
                        )
                        .done(function (result) {
                                $('#setQuotaButton').val("Saved!");
                                cmd.updateTable();
                        })
                        .fail(function () {
                                OC.dialogs.alert("Error: unable to set quota at this time.", "Unable to set quota");
                        })
                        .always(function () {
                                $('#setQuotaButton').prop('disabled', false);
                                setTimeout(function(){$('#setQuotaButton').val("Set");} , 3000);
                        });
                }
	
	};
})();
$(document).ready(function(){OCA.TenantPortal.User.initialise();});
