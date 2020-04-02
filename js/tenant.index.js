( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.Tenant = {
		/**
		 * Initialises the Tenant object and table
		 */
		initialise: function() {
			tenant = OCA.TenantPortal.Tenant;
			tenant.tableName = "#tenantsTable";
			tenant.addForm = $("#addTenantForm");
			tenant.addButton = $("#addTenantButton");
			tenant.nameField = $("#addTenantName");
			tenant.codeField = $("#addTenantCode");
			tenant.updateTable();
			tenant.addForm.on('submit', function(e) { OCA.TenantPortal.Tenant.create(); e.preventDefault(); });
		},

		/**
		 * Initialises click events for dynamic content
		 */
		initClickEvents: function() {
			$(".tenantName.action.view").on('click', function() {
				window.location.href = OC.generateUrl('/apps/tenant_portal/show/'+$(this).data('id'));
			});
			$("#tenantsTable tr td:not(:last-child)").on('click', function() {
				id = $(this).parent().find('td:last-child a').data('id');
				window.location.href = OC.generateUrl('/apps/tenant_portal/show/'+id);
			});
			$(".tenantName.action.delete").on('click', function() { tenant.remove(this); });
		},

		/**
		 * Returns the base URL for Ajax requests
		 * @param {string} path
		 * @return {string} URL
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') {
				path = '/'+path;
			}
			return OC.generateUrl('/apps/tenant_portal/tenants'+path);
		},

		/**
		 * Create a new tenant
		 */
		create: function() {
			tenant = OCA.TenantPortal.Tenant;
			var name = tenant.nameField.val();
			var code = tenant.codeField.val();

			if ($.trim(name).length == 0) {
				OC.dialogs.alert("Error: The tenant name cannot be blank.",
								 "Invalid Tenant Name");
				return false;
			}
			$.post(
				tenant.baseUrl(),
				{ name: name, code: code },
				function(result) {
					tenant.nameField.val('');
					tenant.codeField.val('');
				}
			)
			.done(function (result) {
                                if (typeof result.ocs !== 'undefined' && result.ocs.meta.status == 'failure') {
                                        message = result.ocs.meta.message.charAt(0).toUpperCase() + result.ocs.meta.message.replace(/_/g, ' ').slice(1);
                                        OC.dialogs.alert("Error: " + message, "Error creating tenant");
                                }
				OCA.TenantPortal.Tenant.updateTable();
			})
			.fail(function (result) {
				if (typeof result.responseJSON !== 'undefined') {
					OC.dialogs.alert("Error: "+result.responseJSON.message, "Error Creating Tenant");
				} else {
					OC.dialogs.alert("Error: creation of the new tenant failed. This may be "+
								 "because the tenant name is already taken or invalid.",
								 "Invalid Tenant Name");
				}
			});
		},

		/**
		 * Remove a tenant
		 * @param {Object} el
		 */
		remove: function(el) {
			tenant_name = $(el).parent().parent().children("td:nth-child(2)").text();
			OCdialogs.confirm(t('tenant_portal','Are you sure you want to remove {tenant_name}?', {tenant_name: tenant_name}), '', function(result) {
				if (result) {
					tenant = OCA.TenantPortal.Tenant;
					$.ajax({
						url: tenant.baseUrl($(el).data('id')),
						method: 'DELETE',
						contentType: 'application/json'
					})
					.done(function(result) {
						tenant.updateTable();
					})
					.fail(function() {
						OC.dialogs.alert("Error: unable to remove the tenant"); //TODO: Better error
					});
				}
			}, true);
		},

		/**
		 * Renders the tenant table
		 * @param {Object} tenants
		 */
		updateTable: function(tenants) {
			tenant = OCA.TenantPortal.Tenant;
			if ($.fn.dataTable.isDataTable(tenant.tableName)) {
				$(tenant.tableName).DataTable().ajax.reload(null, false);
			} else {
				$(tenant.tableName).DataTable({
					ajax: {
						url: tenant.baseUrl(),
						dataSrc: ""
					},
					columns:[
						{data: "code", width: "7rem"},
						{data: "name"},
						{
							className: 'actions',
							orderable: false,
							data: "id",
							width: "15rem",
							render: function (data, type, full, meta) { return OCA.TenantPortal.Tenant.renderActions(data); }
						},
					],
					drawCallback: function (settings) { OCA.TenantPortal.Tenant.initClickEvents(); },
					language: {
						loading: "Loading...",
						emptyTable: "There are no tenants..."
					},
					order: [0, "asc"],
					ordering: true,
					info: false,
					pageLength: 50,
					pagingType: "numbers",
					searching: true,
					lengthChange: false,
				});
			}
		},

		/**
		 * Renders actions for each tenant
		 * @param {string} data
		 * @return {string}
		 */
		renderActions: function(data) {
			actions = '';
			actions += '<a class="tenantName action permanent view" title="View tenant" data-id="'+data+'">'+
					   '<img src="'+OC.imagePath('tenant_portal', 'edit.svg')+'">'+
					   '</a>';
			actions += '<a class="tenantName action permanent delete" title="Delete tenant" data-id="'+data+'">'+
					   '<img src="'+OC.imagePath('tenant_portal', 'delete.svg')+'">'+
					   '</a>';
			return actions;
		},

		/**
		 * Renders an empty table body with a message
		 * @param {string} message
		 * @param {integer} colspan
		 */
		renderEmptyTableBody: function(message, colspan) {
			if (typeof colspan === 'undefined') {
				colspan = 1;
			}
			table = $(OCA.TenantPortal.Tenant.tableName).children("tbody");
			newBody = $("<tbody>").append(
						$("<tr>").append(
							$("<td>").attr("colspan", colspan)
									 .addClass("noItems")
									 .text(message)
						)
					);
			table.replaceWith(newBody)
		},
	};
})();

$(document).ready(function(){OCA.TenantPortal.Tenant.initialise();});
