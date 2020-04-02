( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}

	OCA.TenantPortal.Common = {
		/**
		 * Destroys the DataTable if it is one
		 * @param {string} tableName
		 */
		ajaxDataTable: function (tableName, table_config) {
			if ($.fn.dataTable.isDataTable(tableName)) {
				$(tableName).DataTable().ajax.reload(null, false);
			} else {
				default_config = {
					language: {
						loading: "Loading...",
						emptyTable: "No results shown"
					},
					order: [0, "desc"],
					ordering: false,
					info: false,
					pageLength: 50,
					pagingType: "numbers",
					searching: true,
					lengthChange: false,
				}
				config = $.extend({}, default_config, table_config);
				$(tableName).DataTable(config);
			}
		},

		/**
		 * Unbind and rebind an event
		 * @param {string} selector
		 * @param {string} event
		 * @param {*} callback
		 */
		rebind: function (selector, event, callback) {
			$(selector).unbind(event);
			$(selector).on(event, callback);
		}
	};
})();

