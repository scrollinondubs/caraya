var paymentForm = idSquarePaymentForm();

jQuery(document).ready(function() {
	console.log('Square Loaded - IDSquare');
});

jQuery(document).bind('idcPaySelect', function(e, selector) {
	var buttonID = jQuery(selector).attr('id');
	if (buttonID == 'pay-with-square') {
		//console.log('Pay with Square');
		jQuery('#stripe-input .card-expiry-year, #stripe-input .card-exp-slash').hide();
		jQuery('#stripe-input .date label').html(idc_localization_strings.expiration);
		jQuery('#id-main-submit').text(idc_localization_strings.pay_with_square).attr('name', 'submitPaymentSquare').removeAttr('disabled');
		jQuery("#stripe-input").show();
		jQuery('#finaldescStripe').show();
	}
	else {
		jQuery('#stripe-input .date label').html(jQuery('#stripe-input .date label').data('label'));
		if (jQuery('#stripe-input').is(':visible')) {
			jQuery('#stripe-input .card-expiry-year, #stripe-input .card-exp-slash').show();
		}
	}
});

jQuery(document).bind('idcCheckoutSubmit', function(e, submitName) {
	if (submitName == 'submitPaymentSquare') {
		paymentForm.recalculateSize();
		jQuery(".payment-errors").text('');
		idSquareGenerateNonce(e);
	}
});

function idSquareCalcExp() {
	var month = jQuery('#stripe-input .card-expiry-month').val();
	var year = jQuery('#stripe-input .card-expiry-year').val();
	if (year.length = 4) {
		year = year.slice(-2);
	}
	var exp = month + year;
	jQuery('#sq-expiration-date').val(exp);
}

function idSquareGenerateNonce(e) {
	e.preventDefault();
	//console.log(paymentForm);
	nonce = paymentForm.requestCardNonce();
}

function idSquarePaymentForm() {
	// Create and initialize a payment form object
	var paymentForm = new SqPaymentForm({
		// Initialize the payment form elements
		applicationId: id_square_vars.application_id,
		locationId: id_square_vars.location_id,
		inputClass: 'sq-input',

		// Customize the CSS for SqPaymentForm iframe elements
		inputStyles: [{
			
		}],

		/*// Initialize Apple Pay placeholder ID
		applePay: {
		elementId: 'sq-apple-pay'
		},

		// Initialize Masterpass placeholder ID
		masterpass: {
		elementId: 'sq-masterpass'
		},*/

		// Initialize the credit card placeholders
		cardNumber: {
			elementId: 'sq-card-number',
			placeholder: '•••• •••• •••• ••••'
		},
		cvv: {
			elementId: 'sq-cvv',
			placeholder: 'CVV'
		},
		expirationDate: {
			elementId: 'sq-expiration-date',
			placeholder: 'MM/YY'
		},
		postalCode: {
			elementId: 'sq-postal-code'
		},

		// SqPaymentForm callback functions
		callbacks: {

			/*
			 * callback function: methodsSupported
			 * Triggered when: the page is loaded.
			 */
			methodsSupported: function (methods) {
				/*console.log(methods);
				var applePayBtn = document.getElementById('sq-apple-pay');
				var applePayLabel = document.getElementById('sq-apple-pay-label');
				var masterpassBtn = document.getElementById('sq-masterpass');
				var masterpassLabel = document.getElementById('sq-masterpass-label');

				// Only show the button if Apple Pay for Web is enabled
				// Otherwise, display the wallet not enabled message.
				if (methods.applePay === true) {
					applePayBtn.style.display = 'inline-block';
					applePayLabel.style.display = 'none' ;
				}
				// Only show the button if Masterpass is enabled
				// Otherwise, display the wallet not enabled message.
				if (methods.masterpass === true) {
					masterpassBtn.style.display = 'inline-block';
					masterpassLabel.style.display = 'none';
				}*/
			},

			/*
			 * callback function: createPaymentRequest
			 * Triggered when: a digital wallet payment button is clicked.
			 */
			createPaymentRequest: function (nonce) {
				//console.log('createPaymentRequest');
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
					data: {action: 'id_square_submit', nonce: nonce, customer: customer, Fields: fields.posts, txnType: txnType, Renewable: renewable, pwyw_price: pwywPrice, current_url: curURL},
					success: function(res) {
						console.log(res);
						if (typeof res == 'string') {
							var json = JSON.parse(res);
							//console.log(json);
							if (json.response == 'success') {
				    			var paykey = json.paykey;
				    			var product = json.product;
				    			var orderID = json.order_id;
				    			var userID = json.user_id;
				    			var type = json.type;
				    			var custID = null;
				    			jQuery(document).trigger('idcPaymentSuccess', [orderID, custID, userID, product, paykey, fields, type]);
				    			jQuery(document).trigger('squareSuccess', [orderID, custID, userID, product, paykey, fields, type]);
				    			setTimeout(function() {
				    				window.location = idcPayVars.redirectURL + permalink_prefix + "idc_product=" + product + "&paykey=" + paykey + queryString;
				    			}, 1000);
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
		    			jQuery('#id-main-submit').text(idc_localization_strings.pay_with_square);
						jQuery('.payment-errors').text(error);
					}
				});

				var paymentRequestJson ;
			  	/* ADD CODE TO SET/CREATE paymentRequestJson */
			  	//return paymentRequestJson ;
			  	// The payment request below is provided as
				// guidance. You should add code to create the object
				// programmatically.
				// #devnote set product data here
				return {
					requestShippingAddress: false,
					currencyCode: "USD",
					countryCode: "US",

				total: {
				  	label: "Merchant Name",
				  	amount: "1.01",
				  	pending: false,
				},

				lineItems: [
				  	{
				    	label: "Subtotal",
				    	amount: "1.00",
				    	pending: false,
				  	},
				  	{
				    	label: "Tax",
				    	amount: "0.01",
				    	pending: false,
				  	}
				]
				};
			},

			/*
			 * callback function: validateShippingContact
			 * Triggered when: a shipping address is selected/changed in a digital
			 *                 wallet UI that supports address selection.
			 */
			validateShippingContact: function (contact) {
				//console.log('validateShippingContact');
			  	var validationErrorObj ;
			  	/* ADD CODE TO SET validationErrorObj IF ERRORS ARE FOUND */
			  	return validationErrorObj ;
			},

			/*
			 * callback function: cardNonceResponseReceived
			 * Triggered when: SqPaymentForm completes a card nonce request
			 */
			cardNonceResponseReceived: function(errors, nonce, cardData) {
				//console.log(cardData);
				//console.log('cardNonceResponseReceived');
				if (errors) {
					// #devnote return error to checkout form
			   		// Log errors from nonce generation to the Javascript console
			    	console.log("Encountered errors:");
			    	var errorText = '';
			    	jQuery('#id-main-submit').text(idc_localization_strings.pay_with_square).removeAttr('disabled').removeClass('processing');
			    	errors.forEach(function(error) {
			      		//console.log('  ' + error.message);
			      		if (errorText == '') {
			      			errorText = error.message + '.';
			      		}
			      		else {
			      			errorText = errorText + ' ' + error.message + '.';
			      		}
			    	});
			    	jQuery(".payment-errors").text(errorText);
			    	return;
			  	}
			  	//console.log(nonce);
			  	// Assign the nonce value to the hidden form field
			  	document.getElementById('card-nonce').value = nonce;

			  	// #devnote ajax payment submission
			  	this.createPaymentRequest(nonce);

			},

			/*
			 * callback function: unsupportedBrowserDetected
			 * Triggered when: the page loads and an unsupported browser is detected
			 */
			unsupportedBrowserDetected: function() {
				console.log('unsupportedBrowserDetected');
				// #devnote return error to checkout form
			},

			/*
			 * callback function: inputEventReceived
			 * Triggered when: visitors interact with SqPaymentForm iframe elements.
			 */
			inputEventReceived: function(inputEvent) {
				//console.log('inputEventReceived');
			  	switch (inputEvent.eventType) {
				    case 'focusClassAdded':
				      	/* HANDLE AS DESIRED */
				      	break;
				    case 'focusClassRemoved':
				      	/* HANDLE AS DESIRED */
				      	break;
				    case 'errorClassAdded':
				      	/* HANDLE AS DESIRED */
				      	break;
				    case 'errorClassRemoved':
				      	/* HANDLE AS DESIRED */
				      	break;
				    case 'cardBrandChanged':
				      	/* HANDLE AS DESIRED */
				      	break;
				    case 'postalCodeChanged':
				      	/* HANDLE AS DESIRED */
				      	break;
			  	}
			},

			/*
			 * callback function: paymentFormLoaded
			 * Triggered when: SqPaymentForm is fully loaded
			 */
			paymentFormLoaded: function() {
				//console.log('paymentFormLoaded');
			  	/* HANDLE AS DESIRED */
			}
	  	}
	});
	return paymentForm;
}