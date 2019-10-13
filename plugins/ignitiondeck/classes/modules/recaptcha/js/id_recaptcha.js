function idRecaptchaLoad() {
	console.log('reCAPTCHA Loaded');
	if (jQuery('.id_recaptcha_placeholder').length > 0) {
		jQuery('input[name="wp-submit"], button.idc_reg_submit').attr('disabled', 'disabled');
		jQuery.each(jQuery('.id_recaptcha_placeholder'), function(i, el) {
			jQuery(this).attr('id', 'id_recaptcha_placeholder-' + i);
			var thisForm = jQuery(this).closest('form').attr('id');
			var size = 'normal';
			grecaptcha.render('id_recaptcha_placeholder-' + i, {
		      'sitekey' : id_recaptcha_site_id,
		      'size' : size
		    });
		});
	}
}

function idRecaptchaCallback() {
	jQuery('input[name="wp-submit"], button.idc_reg_submit').removeAttr('disabled');
}