( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.TenantConfig) {
		OCA.TenantPortal.TenantConfig = {};
	}

	OCA.TenantPortal.TenantConfig.Domain = {

		/**
		 * Initialises the domains table and add domain form
		 */
		initialise: function() {
			cmd = OCA.TenantPortal.TenantConfig.Domain;
			cmd.addForm = $("#addDomainForm");
			cmd.addButton = $("#addDomainButton");
			cmd.domainField = $("#addDomainName");
			cmd.tableName = "#domainsTable";
			cmd.data = [];

			cmd.updateTable();
			cmd.addForm.on('submit', function(e) { OCA.TenantPortal.TenantConfig.Domain.create(); e.preventDefault(); });
		},

		/**
		 * Initialises the click events for dynamic content]
		 */
		initClickEvents: function() {
			$(".domainName.action.delete").off();
			$(".domainName.action.delete").on('click', function() { OCA.TenantPortal.TenantConfig.Domain.remove(this); });
		},

		/**
		 * Adds a new domain to a tenant
		 */
		create: function() {
			domain = OCA.TenantPortal.TenantConfig.Domain;
			if ($.trim(domain.domainField.val()).length == 0) {
				return false;
			}
			domain.addButton.val("Assigning");
			domain.addForm.children('input').prop('disabled', true);
			$.post(
				OCA.TenantPortal.TenantConfig.baseUrl(),
				{ name: 'domain', value: domain.domainField.val() },
				function(result) {
				}
			)
			.done(function (result) {
				domain.domainField.val('');
				domain.updateTable();
			})
			.fail(function () {
				OC.dialogs.alert("Error: unable to add new domain. The domain may be invalid or already be assigned.", "Invalid Domain");
			})
			.always(function () {
				domain.addButton.val("Assign");
				domain.addForm.children('input').prop('disabled', false);
			});
		},

		/**
		 * Removes a domain from a tenant
		 * @param {Object} el
		 */
		remove: function(el) {
			domain_name = $(el).parent().parent().children("td:first-child").text()
			tenantConfig = OCA.TenantPortal.TenantConfig;
			OC.dialogs.confirm(t('tenant_portal','Are you sure you want to remove {domain} from this tenant?', {domain: domain_name}), '', function(result) {
				if (result) {
					$.ajax({
						url: tenantConfig.baseUrl($(el).data('id')),
						method: 'DELETE',
						contentType: 'application/json'
					})
					.done(function(result) {
						tenantConfig.Domain.updateTable();
					})
					.fail(function() {
						OC.dialogs.alert("Error: unable to remove the domain. You may not have permission to perform this action.", "Unknown Error");
					});
				}
			}, true);
		},

		/**
		 * Returns the URL for Ajax requests
		   @return string
		 */
		baseUrl: function() {
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/getAssignedDomains');
		},

		/**
		 * Renders the domains table
		 * @param {Object} data
		 */
		updateTable: function() {
			domain = OCA.TenantPortal.TenantConfig.Domain;
			OCA.TenantPortal.Common.ajaxDataTable(domain.tableName,{
				ajax: {
					url: domain.baseUrl(),
					dataSrc: function (json) {
						domains = json.domains.map(function(d) { d["showAdminActions"] = json.admin; return d; });
						return domains;
					}
				},
				columns:[
					{data: "config_value"},
					{
						className: 'actions',
						orderable: false,
						data: "id",
						width: "15rem",
						render: function (data, type, full, meta) {
							actions = '';
							if (full.showAdminActions) {
								actions += '<a class="domainName action permanent delete" title="Remove domain" data-id="'+data+'">'+
										   '<img src="'+OC.imagePath('tenant_portal', 'remove.svg')+'">'+
										   '</a>';
							}
							return actions;
						}
					},
				],
				drawCallback: function (settings) { OCA.TenantPortal.TenantConfig.Domain.initClickEvents(); },
				language: {
					loading: "Loading...",
					emptyTable: "No domains found for this tenant."
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
	}
})();

$(document).ready(function(){OCA.TenantPortal.TenantConfig.Domain.initialise();});
