/*! Checkout Guard: Block Spam Woo Orders
 * 2023-11-30
 */

/**
 * @summary     Checkout Guard: Block Spam Woo Orders
 * @description Enhance Woo checkout security. Block spam orders and protect your revenue and customer's trust.
 * @version     1.0.0
 * @file        checkout-guard.js
 * @author      Giannis Kipouros
 * @contact     https://gianniskipouros.com
 *
 */


(function ($) {
	'use strict';

	// On load
	$(document).ready(function ($) {
		$("#cg-admin-form .form-ui-toggle").change(function () {
			let toggleID = $(this).attr('id');
			let rowClass = toggleID.replace('cgbs_', '');
			$('.' + rowClass).toggle();
		})

		if($('#cg-admin-form').length > 0) {
			// Select all/none
			$('#cg-admin-form a.select_all').on('click', function () {
				$(this)
					.closest('td')
					.find('select option')
					.prop('selected', true);
				$(this).closest('td').find('select').trigger('change');
				return false;
			});

			$('#cg-admin-form a.select_none').on('click', function () {
				$(this)
					.closest('td')
					.find('select option')
					.prop('selected', false);
				$(this).closest('td').find('select').trigger('change');
				return false;
			});

			if ( $('.forminp .description').length > 0) {
				tippy('.cg-setting-info',	{
					'placement': 'bottom',
					content(reference) {
						let html = reference.closest('fieldset').querySelector('.description').innerHTML;
						return html;
					},
					allowHTML: true
				});
			}
		}
	}); // End document ready
})(jQuery);
