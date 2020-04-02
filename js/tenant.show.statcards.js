( function() {

	if (!OCA.TenantPortal) {
		OCA.TenantPortal = {};
	}
	if (!OCA.TenantPortal.Show) {
		OCA.TenantPortal.Show = {};
	}

	OCA.TenantPortal.StatCards = {
		/**
		 * Initialises the stat charts
		 */
		initialise: function() {
			show = OCA.TenantPortal.StatCards;
			show.statCardContainer = $('#statCards');
			show.addStatCard("usedStorage", "Total Storage Used");
			show.addStatCard("totalUsers", "Users");
			show.addStatCard("purchasedStorage", "Purchased Storage");
			show.addStatCard("usedPurchasedStorage", "Purchased Storage Used");
			show.addStatCard("remaingPurchasedStorage", "Purchased Storage Remaining");
			show.populateStatCards();
		},

		/**
		 * Returns the URL for Ajax requests
		 * @param {string} path
		 * @return {string} URL
		 */
		baseUrl: function(path) {
			path = $.trim(path);
			if (path && path[0] != '/') { path = '/'+path; }
			id = OCA.TenantPortal.Show.tenantId;
			return OC.generateUrl('/apps/tenant_portal/tenants/'+id+'/stats'+path);
		},

		/**
		 * Adds a stat card to the stats container
		 * @param {string} name
		 * @param {string} title
		 * @param {string} value
		 */
		addStatCard: function(name, title, value) {
			show = OCA.TenantPortal.StatCards;
			cardId = "statCard-"+name;
			card = $('<div>').addClass("statCard")
							 .attr("id", cardId)
							 .append($("<h3>").text(title));

			if (typeof value !== "undefined") {
				card.append($("<span>").text(value));
			} else {
				card.append($("<span>").html("<img src='"+OC.imagePath('tenant_portal','loading.gif') +"' />"));
			}

			if ($("#"+cardId).length > 0) {
				$("#"+cardId).replaceWith(card);
			} else {
				show.statCardContainer.append(card);
			}
		},

		/**
		 * Populates data into the stat cards
		 */
		populateStatCards: function() {
			show = OCA.TenantPortal.StatCards;
			$.ajax({
				url: show.baseUrl('getStatCards'),
				method: 'GET',
			}).done(function(stats) {
				show.addStatCard("usedStorage", "Total Storage Used", stats.storage_used_human);
				show.addStatCard("totalUsers", "Users", stats.total_users);
				show.addStatCard("purchasedStorage", "Purchased Storage", stats.purchased_storage);
				show.addStatCard("usedPurchasedStorage", "Purchased Storage Used", stats.purchased_storage_used);
				show.addStatCard("remaingPurchasedStorage", "Purchased Storage Remaining", stats.purchased_storage_remaining);
			});
		}
	}
})();


$(document).ready(function(){ OCA.TenantPortal.StatCards.initialise(); });
