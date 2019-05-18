jQuery(document).ready(function() {
	var gatewayCount = jQuery('#payment-form .pay_selector').length;
	if (gatewayCount <= 1) {
		jQuery('#idc_checkout_extra_fields_anon').removeClass('hide');
	}
	else {
		jQuery('.pay_selector').click(function() {
			jQuery('#idc_checkout_extra_fields_anon').removeClass('hide');
		});
	}
});