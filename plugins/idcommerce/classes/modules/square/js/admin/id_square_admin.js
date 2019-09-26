jQuery(document).ready(function() {
	console.log('Square Admin Loaded');
	jQuery.getJSON(idc_global_currencies, function(json, textStatus) {
		jQuery.each(json, function() {
			jQuery('#id_square_admin_settings select[name="currency"]').append('<option value="' + this.Currency_Code + '">' + this.Country_and_Currency + '</option>');
		});
		// Selecting the currency that is stored in db
		var selCurrency = jQuery('#id_square_admin_settings select[name="currency"]').data('selected');
		jQuery('#id_square_admin_settings select[name="currency"]').val(selCurrency);
	});
});