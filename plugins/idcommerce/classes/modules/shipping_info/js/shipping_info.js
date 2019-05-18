jQuery(document).ready(function() {
	var gatewayCount = jQuery('#payment-form .pay_selector').length;
	if (gatewayCount <= 1) {
		jQuery('#checkout-form-extra-fields-shipping').removeClass('hide');
	}
	else {
		jQuery('.pay_selector').click(function() {
			jQuery('#checkout-form-extra-fields-shipping').removeClass('hide');
		});
	}
});