idcPayVars.feeMods = {
	donation: idfParseFloat('0'),
	fee: idfParseFloat('0')
};
jQuery(document).ready(function() {
	// donations on checkout
	jQuery(document).bind('idcCheckoutLoaded', function (e) {
		idcGetDonation();
	});
	jQuery('input[name="checkout_donation"]').change(function(e) {
		jQuery(document).trigger('idcBeforeDonationChange', jQuery(this).val());
	});
	jQuery(document).bind('idcBeforeDonationChange', function(e, donation) {
		idcSetDonation(donation);
	});
	jQuery(document).bind('idcPaySelect', function(e, selection) {
		var showForm = idcSelClass(selection) == 'stripe';
		toggleDonationForm(showForm)
		idcGetDonation();
	});
	jQuery(document).bind('idcDonationChange', function(e, donation) {
		var total = feeModsCalcDonation();
		var gateway = jQuery('.pay_selector').hasClass('active');
		var symbol = jQuery('.currency-symbol sup').text();
		idcSetPriceText(gateway, symbol, idfPriceFormat(total));
	});
	jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
		if (submitName == 'submitPaymentStripe') {
			total = feeModsCalcDonation();
			jQuery('input[name="pwyw-price"]').val(total);
		}
	});
	// cover fees
	jQuery('input[name="cover_fees_on_checkout"]').change(function() {
		jQuery(document).trigger('idcBeforeCoverFeeChange', [jQuery(this).attr('checked'), jQuery(this).val()]);
	});
	jQuery(document).bind('idcBeforeCoverFeeChange', function(e, checked, fee) {
		switch(checked) {
			case 'checked':
				idcSetFee(fee);
				break;
			default:
				idcSetFee('0');
				break;
		}
	});
	jQuery(document).bind('idcCoverFeeChange', function(e, checked, fee) {
		var total = feeModsCalcDonation(idcPayVars.feeMods.fee);
		var gateway = jQuery('.pay_selector').hasClass('active');
		var symbol = jQuery('.currency-symbol sup').text();
		idcSetPriceText(gateway, symbol, idfPriceFormat(total));
	});
});

jQuery(document).bind('idcSetSubmitName', function(e, name) {
	var gatewayCount = jQuery('#payment-form .pay_selector').length;
	if (gatewayCount <= 1 && name == 'submitPaymentStripe') {
		toggleDonationForm(true)
	}
});

function toggleDonationForm(display) {
	switch(display) {
		case true:
			jQuery('#checkout-form-extra-fields-donations').removeClass('hide');
			jQuery('#checkout-form-extra-fields-fees').removeClass('hide');
			break;
		case false:
			jQuery('#checkout-form-extra-fields-donations').addClass('hide');
			jQuery('#checkout-form-extra-fields-fees').addClass('hide');
			break;
	}
}

function feeModsCalcDonation() {
	var regPrice = parseFloat(jQuery('input[name="reg-price"]').val()) || 0;
	var pwywPrice = parseFloat(jQuery('input[name="pwyw-price"]').val()) || 0;
	var price = Math.max(regPrice,pwywPrice);
	var total = price + parseFloat(idcPayVars.feeMods.donation) + parseFloat(idcPayVars.feeMods.fee);
	return Number(total.toFixed(2));
}

function idcSetDonation(donation) {
	if (typeof(donation) == 'undefined' || donation < '0') {
		donation = '0';
		var donationInput = jQuery('input[name="checkout_donation"]');
		jQuery(donationInput).val('0');
	}
	idcPayVars.feeMods.donation = idfParseFloat(donation);
	jQuery(document).trigger('idcDonationChange', donation);
}

function idcGetDonation() {
	var donationInput = jQuery('input[name="checkout_donation"]');
	donation = jQuery(donationInput).val();
	idcSetDonation(donation);
}

function idcSetFee(fee) {
	idcPayVars.feeMods.fee = idfParseFloat(fee);
	jQuery(document).trigger('idcCoverFeeChange');
}