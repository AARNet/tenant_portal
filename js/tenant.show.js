( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.Show = {
		/**
		 * Initialises objects for page.tenant.show template
		 */
		initialise: function() {
			show = OCA.TenantPortal.Show;
			show.tenantId = $('#tenantId').data('id');
		},

		/**
		 * Renders an empty table body with message
		 * @param {string} table
		 * @param {string} message
		 * @param {integer} colspan
		 */
		renderEmptyTableBody: function(table, message, colspan) {
			if (typeof colspan === 'undefined') {
				colspan = 1;
			}
			table = $(table).children("tbody");
			newBody = $("<tbody>").append(
						$("<tr>").append(
							$("<td>").attr("colspan", colspan)
									 .addClass("noItems")
									 .text(message)
						)
					);
			table.replaceWith(newBody)
		},
		/**
		 * Destroys the DataTable if it is one
		 * @param {string} tableName
		 */
		destroyTable: function (tableName) {
			if (typeof table !== 'undefined') {
				if ($.fn.DataTable.isDataTable(tableName)) {
					$(table).dataTable().fnDestroy();
				}
			}
		},

	};
})();

$(document).ready(function() { OCA.TenantPortal.Show.initialise(); });
