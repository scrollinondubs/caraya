jQuery(document).ready(function() {
	secupayObj = setSecupayObject();
	//console.log(secupayObj);
	// #devnote simplify this
	if (secupayObj.txnVars.type == 'recurring' || secupayObj.txnVars.free == 'free') {
		jQuery.each(secupayObj.payTypes, function(index, value) {
			jQuery('#' + value).remove();
		});
	}
	else if (secupayObj.txnVars.txn_type == 'preauth') {
		jQuery.each(secupayObj.payTypes, function(index, value) {
			if (value !== 'pay-with-secupaycc' && value !== 'pay-with-secupay') {
				jQuery('#' + value).remove();
			}
		});	
	}
	jQuery(document).bind('idcPaySelect', function(e, selector) {
		var selClass = jQuery(selector).attr('id').replace('pay-with-', '');
		var payText;
		switch(selClass) {
			case 'prepay':
				payText = idc_localization_strings.pay_with_prepay;
				runSecupaySelector(selClass, payText);
				jQuery("#stripe-input").hide();
				jQuery(".pw").parents('.form-row').hide();
				jQuery(".cpw").parents('.form-row').hide();
				jQuery('#finaldescPrepay').show();
				break;
			case 'secupay':
				payText = idc_localization_strings.pay_with_secupay;
				runSecupaySelector(selClass, payText);
				// do we show cc?
				jQuery("#stripe-input").hide();
				jQuery(".pw").parents('.form-row').hide();
				jQuery(".cpw").parents('.form-row').hide();
				jQuery('#finaldescSecupay').show();
				break;
			case 'secupaycc':
				payText = idc_localization_strings.pay_with_cc;
				runSecupaySelector(selClass, payText);
				jQuery("#stripe-input").hide();
				jQuery(".pw").parents('.form-row').hide();
				jQuery(".cpw").parents('.form-row').hide();
				jQuery('#finaldescSecupay').show();
				break;
		}
		return;
	});

	jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
		e.preventDefault();
		if (submitName.includes('Secupay') == false && submitName.includes('Prepay') == false) {
			return false;
		}
		var customer = idcCheckoutCustomer();
		var fields = idcCheckoutExtraFields();
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		var curURL = window.location.href;
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'id_secupay_submit', submit_name: submitName, customer: customer, fields: fields.posts, pwyw_price: pwywPrice, current_url: curURL},
			success: function(res) {
				//console.log(res);
				if (typeof res == 'string') {
					var json = JSON.parse(res);
					//console.log(json);
					if (json.message !== undefined && json.message.iframe_url !== undefined) {
						//var secupay_frame = window.open(json.message.iframe_url, '_blank', "menubar=0, width=720, height=480, left=200, top=200");
						var lbSource = {'type': 'iframe', 'source': json.message.iframe_url};
						openLBGlobal(lbSource);
					}
					else {
						//console.log('input[name="' + submitName + '"]');
						jQuery('button[name="' + submitName + '"]').removeAttr('disabled').removeClass('processing');
						jQuery('.payment-errors').text(json.message);
					}
				}
			}
		});
	});

	function setSecupaySelectors() {
		jQuery(document).trigger('idc_no_methods');
		jQuery(document).trigger('idc_tps_option');
	}

	function runSecupaySelector(selClass, payText) {
		var logged = jQuery("#payment-form #logged-input").hasClass('yes');
		var selClassSubmit = selClass.substring(0,1).toUpperCase() + selClass.substring(1);
		jQuery('#id-main-submit').removeAttr('disabled').attr('name', 'submitPayment' + selClassSubmit).text(payText);
		var formattedPrice = jQuery(".product-price").text();
		idcSetPriceText('prepay', '€', formattedPrice);
		jQuery('.finaldesc').hide();
		// show description?
	}

	jQuery(document).bind('idc_lightbox_global', function(e, lbSource) {
		if (typeof lbSource == 'object') {
			if (lbSource.source.indexOf('secupay') > 0) {
			}
		}
	});

	jQuery(document).bind('idc_close_lightbox_global', function(e, lbSource) {
		if (typeof lbSource == 'object') {
			if (typeof lbSource.source !== "undefined" && lbSource.source.indexOf('secupay') > 0) {
				jQuery('#id-main-submit').removeAttr('disabled').removeClass('processing');
			}
		}
	});
});

function setSecupayObject() {
	var payTypes = {
		'prepay': 'pay-with-prepay',
		'secupay': 'pay-with-secupay',
		'secupaycc': 'pay-with-secupaycc'
	}
	var form = jQuery('form#payment-form');
	var txnVars = {
		'type': jQuery(form).data('type'),
		'free': jQuery(form).data('free'),
		'txn_type': jQuery(form).data('txn-type')
	}
	return {
		'payTypes': payTypes,
		'txnVars': txnVars
	}
}