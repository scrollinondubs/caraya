jQuery(document).ready(function() {
	if (typeof(jQuery('form[name="idc_password_reset"]')) == 'undefined') {
		return;
	}
	var form = jQuery('form[name="idc_password_reset"]');
	if (jQuery(form).data('action') == 'rp') {
		var inputs = jQuery('form[name="idc_password_reset"] input[type="password"]');
		jQuery(inputs).change(function() {
			idc_password_reset_check(inputs);
		});
		jQuery('input[name="idc_submit_password"]').click(function(e) {
			e.preventDefault();
			if (idc_password_reset_check() == 1) {
				jQuery(form).submit();
			}
		});
	}
});

function idc_password_reset_check(inputs) {
	var form = jQuery('form[name="idc_password_reset"]');
	var pw = jQuery('input[name="new_password"]').val();
	var cpw = jQuery('input[name="new_password_confirm"]').val();
	if (pw !== cpw || pw.length < 5) {
		jQuery(inputs).addClass('error');
		jQuery('button[name="idc_submit_password"]').attr('disabled', 'disabled');
		jQuery(form).find('.error').removeClass('hide').text(idc_localization_strings.pw_reset_error);
		return 0;
	}
	else {
		jQuery(inputs).removeClass('error');
		jQuery('button[name="idc_submit_password"]').removeAttr('disabled');
		jQuery(form).find('.error').addClass('hide').text('');
		return 1;
	}
}