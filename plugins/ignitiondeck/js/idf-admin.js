jQuery(document).ready(function() {
	jQuery('.openLBGlobal').click(function(e) {
		// #devnote move to replace all openLBGlobal calls with this
		e.preventDefault();
		var lbSource = jQuery(this).attr('href');
		openLBGlobal(lbSource);
	});

	if (jQuery('#id_account') !== undefined) {
		jQuery('#id_account').magnificPopup({
			type: 'iframe',

			iframe: {
				patterns: {
					ignitiondeck: {
						index: 'ignitiondeck.com',

						//id: 'http://ignitiondeck.com/id/id-free-registration/',

						src: launchpad_link,
					}

					//srcAction: '#id_account'
				}
			}
		});
		jQuery('#id_account').click(function(e) {
			//e.preventDefault();
		});
	}

	window.addEventListener('message', idfRegister, false);

	// disconnect account
	jQuery('#idf_reset_account').click(function(e) {
		e.preventDefault();
		var dcButton = jQuery(this);
		jQuery(dcButton).addClass('processing');
		jQuery.ajax({
			url: idf_admin_ajaxurl,
			type: 'POST',
			data: {action: 'idf_reset_account'},
			success: function(res) {
				console.log(res);
				jQuery(document).trigger('idfResetAccount');
				jQuery(dcButton).removeClass('processing');
				location.reload();

			},
			error: function(error) {
				jQuery(dcButton).removeClass('processing');
			}
		});
	});

	// License Selection
	var license_option = jQuery('select[name="idf_license_entry_options"]').find('option:selected').val();
	idfSetupLicenseOption(license_option);
	jQuery('select[name="idf_license_entry_options"]').change(function() {
		license_option = jQuery(this).find('option:selected').val();
		idfSetupLicenseOption(license_option);
	});

	function idfSetupLicenseOption(license_option) {
		jQuery('li.license_option').hide();
		switch(license_option) {
			case 'email':
				jQuery('li.id_account_options').show();
				break;
			case 'keys':
				jQuery('li.license_key_options').show();
				break;
			default:
				jQuery('li.id_account_options').show();
				break;
		}
	}

	// Themes Page

	jQuery('.activate_theme').click(function(e) {
		e.preventDefault();
		var slug = jQuery(this).data('theme');
		jQuery.ajax({
			url: idf_admin_ajaxurl,
			type: 'POST',
			data: {action: 'idf_activate_theme', theme: slug},
			success: function(res) {
				if (res == 1) {
					location.reload();
					//location.href= idf_siteurl + '/wp-admin/themes.php?page=theme-settings';
				}
			}
		});
	});
	// Extensions Page
	jQuery('.extension_subheader select[name="module_filter"]').change(function() {
		var filter = jQuery(this).val();
		var modules = jQuery('.extension');
		jQuery.each(modules, function() {
			switch(jQuery(this).hasClass(filter)) {
				case false:
					jQuery(this).hide();
					break;
				default:
					jQuery(this).show();
					break;
			}
		});
	});
	jQuery('.extension_subheader input[name="hide_locked"]').change(function() {
		if (jQuery(this).attr('checked')) {
			jQuery('.extension[data-locked="1"]').hide();
		}
		else {
			jQuery('.extension').show();
		}
	});
	jQuery('.extension[data-locked="1"]').hover(
		function() {
			jQuery(this).find('.fa.fa-lock').removeClass('fa-lock').addClass('fa-unlock');
	}, 
		function() {
			jQuery(this).find('.fa.fa-unlock').removeClass('fa-unlock').addClass('fa-lock');
	});
	jQuery('.extension-link .active-installed').click(function(e) {
		e.preventDefault();
		var extension = jQuery(this).data('extension');
		jQuery.ajax({
			url: idf_admin_ajaxurl,
			type: 'POST',
			data: {action: 'idf_activate_extension', extension: extension},
			success: function(res) {
				if (res == 1) {
					location.reload();
				}
			}
		});
	});
	// new / edit post page
	if (idf_platform !== 'legacy') {
		jQuery('input[value="pwyw"]').attr('disabled', 'disabled');
	}

});
function idf_flush_object(object) {
	jQuery.ajax({
		url: idf_admin_ajaxurl,
		type: 'POST',
		data: {action: 'idf_flush_object', object: object},
		success: function(res) {
			console.log(res);
		}
	});
}
function idfRegister(e) {
	resVar = e.data;
	if (e.origin == 'https://ignitiondeck.com') {
		if (resVar !== undefined) {
			if (resVar.response == 'idf: registered') {
				// they have completed registration
				var email;
				if (e.data.customer !== undefined) {
					email = e.data.customer;
				}
				setTimeout(function() {
					jQuery.magnificPopup.close();
					jQuery.ajax({
						url: idf_admin_ajaxurl,
						type: 'POST',
						data: {action: 'idf_do_register', Email: email},
						success: function(res) {
							//console.log(res);
							window.location.href = idf_current_url + '&amp;action=idf_registered';
						}
					});
				}, 1500);
			}
		}
	}
}

function openLBGlobal(lbSource, openCallback, closeCallback) {
	jQuery.magnificPopup.open({
		type: 'inline',
		items: {
			src: jQuery(lbSource)
		},
		callbacks: {
			open: function() {
				if (openCallback !== undefined && openCallback !== null)
					openCallback();
			},
			close: function() {
				if (closeCallback !== undefined && closeCallback !== null)
					closeCallback();
			}
		}
	});
}

function closeLBGlobal() {
	jQuery.magnificPopup.close();
}