jQuery(document).ready(function() {
	console.log('Paytm Loaded - IDPaytm');
});

jQuery(document).bind('idcCheckoutLoaded', function(e) {
	var type = jQuery("#payment-form").data('type');
	var txnType = jQuery("#payment-form").data('txn-type');
	if (type == 'recurring' || txnType == 'preauth') {
		jQuery('#payment-form #pay-with-paytm').remove();
		no_methods();
	}
});

jQuery(document).bind('idcPaySelect', function(e, selector) {
	var buttonID = jQuery(selector).attr('id');
	if (buttonID == 'pay-with-paytm') {
		console.log('Pay with Paytm');
		jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paytm).attr('name', 'submitPaymentPaytm').removeAttr('disabled');
		jQuery('#finaldescPaytm').show();
	}
});

jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
	if (submitName == 'submitPaymentPaytm') {
		jQuery(".payment-errors").text('');
		var customer = idcCheckoutCustomer();
		var fields = idcCheckoutExtraFields();
		var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val());
		var txnType = jQuery("#payment-form").data('txn-type');
		var renewable = jQuery('#payment-form').data('renewable');
		var curURL = window.location.href;
		var queryString = '';
		jQuery.each(fields.posts, function() {
			queryString = queryString + '&' + this.name + '=' + this.value;
		});
		jQuery.ajax({
			url: memberdeck_ajaxurl,
			type: 'POST',
			data: {action: 'id_paytm_submit', customer: customer, Fields: fields.posts, txnType: txnType, Renewable: renewable, pwyw_price: pwywPrice, current_url: curURL},
			success: function(res) {
				console.log(res);
				if (typeof res == 'string') {
					var json = JSON.parse(res);
					console.log(json);
					if (json.response == 'success') {
						console.log('success');
						//return;
						jQuery('#payment-form').after(json.message);
						jQuery('form[name="f1"]').submit();
		    			/*var paykey = json.paykey;
		    			var product = json.product;
		    			var orderID = json.order_id;
		    			var userID = json.user_id;
		    			var type = json.type;
		    			var custID = null;
		    			jQuery(document).trigger('idcPaymentSuccess', [orderID, custID, userID, product, paykey, fields, type]);
		    			jQuery(document).trigger('squareSuccess', [orderID, custID, userID, product, paykey, fields, type]);
		    			setTimeout(function() {
		    				window.location = idcPayVars.redirectURL + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
		    			}, 1000);*/
		    		}
					else {
						jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
    					jQuery('#id-main-submit').text(idc_localization_strings.pay_with_square);
						jQuery('.payment-errors').text(json.message);
					}
				}
			},
			error: function(error) {
				jQuery('#id-main-submit').removeAttr('disabled').text('').removeClass('processing');    			
    			jQuery('#id-main-submit').text(idc_localization_strings.pay_with_paytm);
				jQuery('.payment-errors').text(error);
			}
		});
	}
});