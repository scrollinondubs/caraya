jQuery(document).ready(function() {
	var ddownMarkup = 
		'<p class="wp-clearfix">' +
			'<label class="howto" for="idc_custom_links">' + ignitiondeck_links + '</label>' +
			'<select name="idc_custom_links" id="idc_custom_links" class="">' +
				'<option value="0">' + none_selected + '</option>' +
				'<option value="' + create_account + '" data-url="' + durl + idf_prefix + 'action=register">' + create_account + '</option>' +
				'<option value="' + my_account + '" data-url="'+ durl + '">' + my_account + '</option>' +
				'<option value="' + login + '" data-url="'+ durl + '">' + login + '</option>' +
				'<option value="' + logout + '" data-url="'+ logout_url + '">' + logout + '</option>' +
			'</select>' +
		'</p>';
	jQuery('.customlinkdiv').prepend(ddownMarkup);

	jQuery('select[name="idc_custom_links"]').change(function(e) {
		var selected = jQuery('select[name="idc_custom_links"] option:selected');
		var urlField = jQuery('#custom-menu-item-url');
		var nameField = jQuery('#custom-menu-item-name');
		if (jQuery(selected).val() == 0) {
			jQuery(urlField).val('http://');
			jQuery(nameField).val('');
		}
		else {
			jQuery(urlField).val(jQuery(selected).data('url'));
			jQuery(nameField).val(jQuery(selected).val());
		}
	});
});