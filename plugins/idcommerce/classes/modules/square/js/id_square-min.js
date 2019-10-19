function idSquareCalcExp(){var e=jQuery("#stripe-input .card-expiry-month").val(),t=jQuery("#stripe-input .card-expiry-year").val();(t.length=4)&&(t=t.slice(-2));var r=e+t;jQuery("#sq-expiration-date").val(r)}function idSquareGenerateNonce(e){e.preventDefault(),nonce=paymentForm.requestCardNonce()}function idSquarePaymentForm(){return new SqPaymentForm({applicationId:id_square_vars.application_id,locationId:id_square_vars.location_id,inputClass:"sq-input",inputStyles:[{}],cardNumber:{elementId:"sq-card-number",placeholder:"•••• •••• •••• ••••"},cvv:{elementId:"sq-cvv",placeholder:"CVV"},expirationDate:{elementId:"sq-expiration-date",placeholder:"MM/YY"},postalCode:{elementId:"sq-postal-code"},callbacks:{methodsSupported:function(e){},createPaymentRequest:function(e){var t=idcCheckoutCustomer(),r=idcCheckoutExtraFields(),a=parseFloat(jQuery('input[name="pwyw-price"]').val()),i=jQuery("#payment-form").data("txn-type"),n=jQuery("#payment-form").data("renewable"),s=window.location.href,o="";jQuery.each(r.posts,function(){o=o+"&"+this.name+"="+this.value}),jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"id_square_submit",nonce:e,customer:t,Fields:r.posts,txnType:i,Renewable:n,pwyw_price:a,current_url:s},success:function(e){if(console.log(e),"string"==typeof e){var t=JSON.parse(e);if("success"==t.response){var a=t.paykey,i=t.product,n=t.order_id,s=t.user_id,u=t.type,c=null;jQuery(document).trigger("idcPaymentSuccess",[n,null,s,i,a,r,u]),jQuery(document).trigger("squareSuccess",[n,null,s,i,a,r,u]),setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+i+"&paykey="+a+o},1e3)}else jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_square),jQuery(".payment-errors").text(t.message)}},error:function(e){jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_square),jQuery(".payment-errors").text(e)}});var u;return{requestShippingAddress:!1,currencyCode:"USD",countryCode:"US",total:{label:"Merchant Name",amount:"1.01",pending:!1},lineItems:[{label:"Subtotal",amount:"1.00",pending:!1},{label:"Tax",amount:"0.01",pending:!1}]}},validateShippingContact:function(e){var t},cardNonceResponseReceived:function(e,t,r){if(e){console.log("Encountered errors:");var a="";return jQuery("#id-main-submit").text(idc_localization_strings.pay_with_square).removeAttr("disabled").removeClass("processing"),e.forEach(function(e){a=""==a?e.message+".":a+" "+e.message+"."}),void jQuery(".payment-errors").text(a)}document.getElementById("card-nonce").value=t,this.createPaymentRequest(t)},unsupportedBrowserDetected:function(){console.log("unsupportedBrowserDetected")},inputEventReceived:function(e){switch(e.eventType){case"focusClassAdded":break;case"focusClassRemoved":break;case"errorClassAdded":break;case"errorClassRemoved":break;case"cardBrandChanged":break;case"postalCodeChanged":break}},paymentFormLoaded:function(){}}})}var paymentForm=idSquarePaymentForm();jQuery(document).ready(function(){console.log("Square Loaded - IDSquare")}),jQuery(document).bind("idcPaySelect",function(e,t){"pay-with-square"==jQuery(t).attr("id")?(jQuery("#stripe-input .card-expiry-year, #stripe-input .card-exp-slash").hide(),jQuery("#stripe-input .date label").html(idc_localization_strings.expiration),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_square).attr("name","submitPaymentSquare").removeAttr("disabled"),jQuery("#stripe-input").show(),jQuery("#finaldescStripe").show()):(jQuery("#stripe-input .date label").html(jQuery("#stripe-input .date label").data("label")),jQuery("#stripe-input").is(":visible")&&jQuery("#stripe-input .card-expiry-year, #stripe-input .card-exp-slash").show())}),jQuery(document).bind("idcCheckoutSubmit",function(e,t){"submitPaymentSquare"==t&&(paymentForm.recalculateSize(),jQuery(".payment-errors").text(""),idSquareGenerateNonce(e))});