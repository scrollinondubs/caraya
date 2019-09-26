function idcIsLoggedIn(){return jQuery("#payment-form #logged-input").hasClass("yes")}function setGuestCheckout(){var e;idcPayVars.isGuestCheckout=0,jQuery("#payment-form").data("guest-checkout")&&!idcIsLoggedIn()&&jQuery("#payment-form input.pw").is(":hidden")&&(idcPayVars.isGuestCheckout=1,idcPayVars.redirectURL=idfStripUrlQuery(idf_current_url))}function setIdcPayObj(){idcPayVars.idSet=jQuery("#payment-form #stripe-input").data("idset"),idcPayVars.isFree=jQuery("#payment-form").data("free")}function no_methods(){var e=jQuery("#payment-form .pay_selector").length;if(e<1)"free"!==idcPayVars.isFree&&(jQuery(".finaldesc").hide(),jQuery("#stripe-input").hide(),jQuery("#payment-form #id-main-submit").text(idc_localization_strings.no_payments_available).attr("disabled","disabled"));else if(1==e){var r=jQuery("#payment-form .pay_selector");jQuery(document).trigger("idcPaySelect",r),jQuery(".payment-type-selector").hide();var t=0;1==es?(idcSetSubmitName("Stripe"),t=1):"pay-with-fd"==jQuery("#payment-form .pay_selector").attr("id")?(idcSetSubmitName("FD"),t=1):1!=eauthnet||idcPayVars.isGuestCheckout||(idcSetSubmitName("Authorize"),t=1),idcPayVars.idSet||1!=t||(
// #devnote showCC should show cc form
jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#payment-form #stripe-input").show(),jQuery("#finaldescStripe").show(),jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required"),jQuery("#id-main-submit").removeAttr("disabled"))}return e}function idcCheckoutErrorClass(){return"error"}function idcCheckoutFormData(){var e;return{fname:jQuery(".first-name").val(),lname:jQuery(".last-name").val(),email:jQuery("#payment-form .email").val(),pw:jQuery(".pw").val(),cpw:jQuery(".cpw").val(),pid:jQuery("#payment-form").data("product")}}function idcCheckoutExtraFields(){var e=jQuery("#extra_fields input, #extra_fields select"),i={posts:{}};jQuery.each(e,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),i.posts[e]={},i.posts[e].name=t,i.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),i.posts[e]={},i.posts[e].name=t,i.posts[e].value=value)});var r="";return jQuery.each(i.posts,function(){r=r+"&"+this.name+"="+this.value}),i}function idcCheckoutCustomer(){var e,r,t,a,i,s;return{product_id:jQuery("#payment-form").data("product"),first_name:jQuery(".first-name").val(),last_name:jQuery(".last-name").val(),email:jQuery("#payment-form .email").val(),pw:jQuery(".pw").val()}}function idcSelClass(e){var r;return jQuery(e).attr("id").replace("pay-with-","")}function idcPaySelectActions(e){
//e.preventDefault();
var r=jQuery(".currency-symbol").children("sup").text(),t=jQuery(".currency-symbol .product-price").text(),a=jQuery("#payment-form").data("type"),i=jQuery("#payment-form").data("txn-type");// #devnote move to object
switch(// #devnote move to object
selClass=idcSelClass(e),selClass){case"stripe":r=jQuery("#stripe-input").data("symbol"),// #devnote move to object?
idcSetSubmitName("Stripe"),jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery(".finaldesc").hide(),jQuery("#finaldescStripe").show(),idcIdSet();break;case"paypal":idcSetSubmitName("Paypal"),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal),jQuery("#stripe-input, .finaldesc").hide(),jQuery("#finaldescPayPal").show(),jQuery(".card-number, .card-cvc, .card-expiry-month, .card-expiry-year").removeClass("required"),"recurring"==a?jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppSubForm.php"):jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppForm.php"),idcHideRegistration();break;case"ppadaptive":idcSetSubmitName("PPAdaptive"),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal),jQuery("#stripe-input, .finaldesc").hide(),jQuery("#finaldescPayPal").show(),"recurring"==a||"preauth"==i?jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppAdaptiveSubForm.php"):jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppAdaptiveForm.php"),idcHideRegistration();break;case"fd":r="$",idcSetSubmitName("FD"),jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery(".finaldesc").hide(),jQuery("#finaldescStripe").show(),idcIdSet();break;case"mc":r=jQuery("#finaldescOffline").data("currency-symbol"),idcSetSubmitName("MC"),jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#stripe-input, .finaldesc").hide(),jQuery("#finaldescOffline").show();break;case"credits":idcSetSubmitName("Credits"),jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#stripe-input, .finaldesc").hide(),jQuery("#finaldescCredits").show();break;case"coinbase":r=jQuery("#finaldescCoinbase").data("cb-symbol"),idcSetSubmitName("Coinbase"),jQuery("#id-main-submit").text(idc_localization_strings.pay_with_coinbase),jQuery("#stripe-input, .finaldesc").hide(),jQuery("#finaldescCoinbase").show(),jQuery(".card-number, .card-cvc, .card-expiry-month, .card-expiry-year").removeClass("required"),idcHideRegistration();break;case"authorize":
// #integrate with guest checkout
r="$",idcSetSubmitName("Authorize"),jQuery("#id-main-submit").text(idc_localization_strings.complete_checkout),jQuery(".finaldesc").hide(),jQuery("#finaldescStripe").show(),idcIdSet();break}1==idcPayVars.trial.trialPeriod?jQuery("#finaldescTrial").show():jQuery("#finaldescTrial").hide(),jQuery("#id-main-submit").removeAttr("disabled"),idcSetPriceText(selClass,r,t)}function idcSetSubmitName(e){var r="submitPayment"+e;jQuery("#id-main-submit").attr("name",r),jQuery(document).trigger("idcSetSubmitName",r)}function idcIdSet(){idcPayVars.idSet||(jQuery("#stripe-input").show(),idcShowRegistration(),jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required"))}function idcHideRegistration(){jQuery(".pw").parents(".form-row").hide(),jQuery(".cpw").parents(".form-row").hide()}function idcShowRegistration(){jQuery(".pw").parents(".form-row").show(),jQuery(".cpw").parents(".form-row").show()}function idcSetPriceText(e,r,t){if("credits"==e){var a=jQuery("#finaldescCredits .credit-value").text();jQuery(".currency-symbol").children("sup").text(r),jQuery("#payment-form .product-price").text(a)}else jQuery(".currency-symbol").children("sup").text()!==r&&jQuery(".currency-symbol").children("sup").text(r),jQuery("#payment-form .product-price").text(t)}function setTrialObj(){idcPayVars.trial={trialPeriod:parseInt(jQuery("#payment-form").data("trial-period")),trialLength:parseInt(jQuery("#payment-form").data("trial-length")),trialType:jQuery("#payment-form").data("trial-type")}}function isTerms(){return 0<jQuery(".idc-terms-checkbox").length}function isTermsChecked(){return jQuery(".terms-checkbox-input").is(":checked")}var idcPayVars={isGuestCheckout:0,redirectURL:memberdeck_durl,idSet:0,isFree:0,trial:{trialPeriod:"",trialLength:"",trialType:""}},mc=memberdeck_mc,epp=memberdeck_epp,es=memberdeck_es,ecb=memberdeck_ecb,eauthnet=memberdeck_eauthnet,eppadap=memberdeck_eppadap,onlyStripe="1"===es&&"1"!==mc&&"1"!==ecb&&"1"!==epp&&"1"!==eauthnet&&"1"!==eppadap,scpk;
// payment form stuff
jQuery(document).ready(function(){function e(){var e=jQuery("#payment-form").data("scpk");return idcPayVars.scpk=e}function r(){var e=jQuery("#payment-form").data("claimedpp");return idcPayVars.claim_paypal=e}function t(){if(jQuery("#payment-form .pay_selector").hide(),jQuery("#payment-form .checkout-header").hide(),jQuery("#id-main-submit").removeAttr("disabled"),jQuery(".checkout-payment").hasClass("active")&&(jQuery(".checkout-payment").removeClass("active"),jQuery(".checkout-confirmation").addClass("active")),
// Adding a class to .pay_selector children div, to fix an issue of selector going towards left or right in some themes
jQuery(".pay_selector").parent("div").addClass("single-payment-selector"),
// Showing the terms and checkout button as there is no other payment gateway to be selected
0<jQuery(".idc-terms-checkbox").length&&jQuery(".idc-terms-checkbox").show(),jQuery(".main-submit-wrapper").show(),jQuery(".confirm-screen").show(),1==epp&&"preauth"!==S)jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_paypal),jQuery("#payment-form #id-main-submit").attr("name","submitPaymentPaypal"),"recurring"==x?jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppSubForm.php"):jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppForm.php"),idcHideRegistration(),jQuery("#payment-form #finaldescPayPal").show(),jQuery("#payment-form #finaldescCredits").hide(),jQuery("#payment-form #finaldescOffline").hide(),jQuery("#payment-form .reveal-account").hide();else if("1"==mc&&"recurring"!==x){
// var globalCurrency = jQuery("#finaldescOffline").data('currency');
var e;jQuery("#payment-form #pay-with-paypal").parent("div").remove(),jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#payment-form #id-main-submit").attr("name","submitPaymentMC"),jQuery("#finaldescStripe").hide(),jQuery("#finaldescPayPal").hide(),jQuery("#finaldescCredits").hide(),jQuery("#finaldescOffline").show(),idcSetPriceText("mc",jQuery("#finaldescOffline").data("currency-symbol"),O)}else if(1===u){jQuery("#payment-form #pay-with-paypal").parent("div").remove(),jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#payment-form #id-main-submit").attr("name","submitPaymentCredits"),jQuery("#payment-form #finaldescCredits").show(),jQuery("#payment-form #finaldescCoinbase").hide(),jQuery("#payment-form #finaldescOffline").hide();var r=jQuery("#finaldescCredits .credit-value").text();jQuery(".currency-symbol").children("sup").html(_),// +'</sup>' + _credits_value);
jQuery(".currency-symbol").children(".product-price").html(r)}else"1"===ecb?(jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_coinbase),jQuery("#payment-form #id-main-submit").attr("name","submitPaymentCoinbase"),jQuery("#payment-form #finaldescCoinbase").show(),jQuery("#payment-form #finaldescCredits").hide(),jQuery("#payment-form #finaldescOffline").hide(),jQuery("#finaldescPayPal").hide(),jQuery(".currency-symbol").children("sup").text(C)):"1"===eppadap?(jQuery("#payment-form #id-main-submit").text(idc_localization_strings.pay_with_paypal),jQuery("#payment-form #id-main-submit").attr("name","submitPaymentPPAdaptive"),idcHideRegistration(),
// Loading the form and setting the payment key
"recurring"==x||"preauth"==S?jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppAdaptiveSubForm.php"):jQuery("#ppload").load(memberdeck_pluginsurl+"/templates/_ppAdaptiveForm.php"),jQuery("#payment-form #finaldescCoinbase").hide(),jQuery("#payment-form #finaldescStripe").hide(),jQuery("#payment-form #finaldescOffline").hide(),jQuery("#payment-form #finaldescPayPal").show(),jQuery("#payment-form .reveal-account").hide()):(jQuery("#payment-form #pay-with-paypal").parent("div").remove(),jQuery("#payment-form #id-main-submit").text(idc_localization_strings.complete_checkout),jQuery("#payment-form #finaldescCoinbase").hide(),jQuery("#payment-form #finaldescOffline").hide(),jQuery(".card-number, .card-cvc, card-expiry-month, card-expiry-year").addClass("required"),idcPayVars.idSet||idcShowRegistration(),"pay-with-stripe"==jQuery("#payment-form .pay_selector").attr("id")?(jQuery("#payment-form #id-main-submit").attr("name","submitPaymentStripe"),jQuery(".currency-symbol").children("sup").text(y)):"pay-with-fd"==jQuery("#payment-form .pay_selector").attr("id")&&jQuery("#payment-form #id-main-submit").attr("name","submitPaymentFD"));no_methods()}function a(){jQuery("#payment-form #pay-with-coinbase").parent("div").remove(),jQuery("#finaldescCoinbase").remove()}function i(){var a=!1,i=idcCheckoutErrorClass(),e="";jQuery("#id-main-submit").attr("disabled","disabled").addClass("processing"),jQuery(".payment-errors").html(""),jQuery("#payment-form input, #payment-form select").removeClass(i),jQuery("#stripe-input").is(":visible")&&(a=c());var r=jQuery("#payment-form input.required:visible, #payment-form select.required:visible"),s=!1;if(jQuery.each(r,function(e,r){var t=jQuery(r).val();""!=t&&void 0!==t||(jQuery(this).addClass(i),s=a=!0)}),!idcIsLoggedIn()){var t=idcCheckoutFormData(),n=!1;idfValidateEmail(t.email)||(jQuery("#payment-form .email").addClass(i),n=a=!0),jQuery(".pw").is(":visible")&&t.pw!==t.cpw&&(e=e+" "+idc_localization_strings.pass_dont_match+".",jQuery(".pw").addClass(i),jQuery(".cpw").addClass(i),a=!0)}if(isTerms()&&!isTermsChecked()){var u=jQuery("#idc-hdn-error-terms-privacy").val();e=e+" "+idc_localization_strings.accept_terms+" "+u+".";var a=!0}return a?((s||n)&&(e=e+" "+idc_localization_strings.complete_all_fields+"."),jQuery(".payment-errors").text(e),jQuery("#id-main-submit").text(idc_localization_strings.continue),jQuery("#id-main-submit").removeClass("processing").removeAttr("disabled")):o(),a}function o(){var e=jQuery("#payment-form .email").val();
//console.log(email);
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_check_email",Email:e},success:function(e){
//console.log(res);
var r,t=JSON.parse(e).response;
//console.log(json);
idcIsLoggedIn()||"exists"!=t?"free"!==idcPayVars.isFree?
//console.log('not free');
n():
//console.log('free');
s():(jQuery(".payment-errors").html('<span id="email-error">'+idc_localization_strings.email_already_exists+"<br>"+idc_localization_strings.please+' <a class="login-redirect" href="'+memberdeck_durl+'">'+idc_localization_strings.login+"</a></span>"),jQuery("#id-main-submit").removeAttr("disabled"),jQuery("#email-error .login-redirect").click(function(e){e.preventDefault(),jQuery("#payment-form").hide(),jQuery(".login-form").show()}))}})}function s(){var e=jQuery(".first-name").val(),r=jQuery(".last-name").val(),t=jQuery("#payment-form .email").val(),a=jQuery(".pw").val(),i=jQuery(".cpw").val(),s,n={product_id:jQuery("#payment-form").data("product"),first_name:e,last_name:r,email:t,pw:a};
//console.log(customer);
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_free_product",Customer:n},success:function(e){if(console.log(e),json=JSON.parse(e),"success"==json.response){var r=json.product;window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+r,jQuery(document).trigger("idcFreeSuccess",n)}}})}function n(){var e=jQuery("#extra_fields input, #extra_fields select"),o={posts:{}};jQuery.each(e,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value)});var c="";jQuery.each(o.posts,function(){c=c+"&"+this.name+"="+this.value});var t=parseFloat(jQuery('input[name="pwyw-price"]').val());if("submitPaymentStripe"==jQuery("#id-main-submit").attr("name")){if(jQuery("#id-main-submit").text(idc_localization_strings.processing+"..."),idcPayVars.idSet){
//jQuery("#id-main-submit").text(idc_localization_strings.processing + '...');
var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),a={product_id:s,first_name:n,last_name:u,email:l,pw:r};
//console.log(customer);
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:"stripe",Customer:a,Token:"customer",Fields:o.posts,txnType:S,Renewable:w,PWYW:t},success:function(e){if(console.log(e),json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,o,s]),jQuery(document).trigger("stripeSuccess",[a,n,i,t,r,o,s]),
// Code for Custom Goal: Sale
//_vis_opt_goal_conversion(201);
//_vis_opt_goal_conversion(202);
// set a timeout for 1 sec to allow trigger time to fire
setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{var u;jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),"pay-with-paypal"==jQuery(".payment-type-selector .active").attr("id")?jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal):jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}})}else{var n=jQuery(".first-name").val(),u=jQuery(".last-name").val();try{Stripe.createToken({number:jQuery(".card-number").val(),cvc:jQuery(".card-cvc").val(),exp_month:jQuery(".card-expiry-month").val(),exp_year:jQuery(".card-expiry-year").val(),name:n+" "+u,address_zip:jQuery(".zip-code").val()},v)}catch(e){jQuery("#id-main-submit").removeAttr("disabled").removeClass("processing"),jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(idc_localization_strings.stripe_credentials_problem_text)}}return!1}if("submitPaymentFD"==jQuery("#id-main-submit").attr("name")){jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),i=jQuery(".card-number").val(),d,m,y=(d=jQuery(".card-expiry-month").val())+(m=jQuery(".card-expiry-year").val().slice(-2)),a={product_id:s,first_name:n,last_name:u,email:l,pw:r};if(idcPayVars.idSet)var p="customer";else var p="none";return jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:"fd",Customer:a,Token:p,Card:i,Expiry:y,Fields:o.posts,txnType:S,Renewable:w,PWYW:t},success:function(e){if(console.log(e),json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,o,s]),jQuery(document).trigger("fdSuccess",[a,n,i,t,r,o,s]),
// Code for Custom Goal: Sale
//_vis_opt_goal_conversion(201);
//_vis_opt_goal_conversion(202);
// set a timeout for 1 sec to allow trigger time to fire
setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{var u;jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),"pay-with-paypal"==jQuery(".payment-type-selector .active").attr("id")?jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal):jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}}),!1}if("submitPaymentMC"==jQuery("#id-main-submit").attr("name")){jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),a={product_id:s,first_name:n,last_name:u,email:l,pw:r};if(idcPayVars.idSet)var p="customer";else var p="none";return jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:"mc",Customer:a,Token:p,Fields:o.posts,txnType:S,Renewable:w,PWYW:t},success:function(e){if(console.log(e),json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,o,s]),jQuery(document).trigger("fdSuccess",[a,n,i,t,r,o,s]),
// Code for Custom Goal: Sale
//_vis_opt_goal_conversion(201);
//_vis_opt_goal_conversion(202);
// set a timeout for 1 sec to allow trigger time to fire
setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{var u;jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),"pay-with-paypal"==jQuery(".payment-type-selector .active").attr("id")?jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal):jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}}),!1}if("submitPaymentAuthorize"==jQuery("#id-main-submit").attr("name")){jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),i=jQuery(".card-number").val(),d,m,y=(d=jQuery(".card-expiry-month").val())+(m=jQuery(".card-expiry-year").val().slice(-2)),j=jQuery(".card-cvc").val(),a={product_id:s,first_name:n,last_name:u,email:l,pw:r};if(idcPayVars.idSet)var p="customer";else var p="none";return jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:"authorize.net",Customer:a,Token:p,Card:i,Expiry:y,CCode:j,Fields:o.posts,txnType:S,Renewable:w,PWYW:t},success:function(e){if(
//console.log(res);
json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,o,s]),jQuery(document).trigger("authorizeSuccess",[a,n,i,t,r,o,s]),
// Code for Custom Goal: Sale
//_vis_opt_goal_conversion(201);
//_vis_opt_goal_conversion(202);
// set a timeout for 1 sec to allow trigger time to fire
setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{var u;jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),"pay-with-paypal"==jQuery(".payment-type-selector .active").attr("id")?jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal):jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}}),!1}if("submitPaymentCredits"==jQuery("#id-main-submit").attr("name")){jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),a={product_id:s,first_name:n,last_name:u},e=jQuery("#extra_fields input, #extra_fields select"),o={posts:{}};jQuery.each(e,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value)}),jQuery.each(o.posts,function(){c=c+"&"+this.name+"="+this.value}),jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"md_use_credit",Customer:a,Token:"customer",Fields:o.posts,PWYW:t},success:function(e){if(console.log(e),json=JSON.parse(e),json)
//console.log(json);
if("success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=null;jQuery(document).trigger("creditSuccess",[a,n,i,t,r,null,s]),setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing");var u=jQuery(".payment-type-selector .active").attr("id");jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}})}else if("submitPaymentCoinbase"==jQuery("#id-main-submit").attr("name")){
// if user is logged in, then just trigger the Coinbase button
jQuery(document).bind("coinbase_modal_closed",function(e,r){jQuery("#id-main-submit").removeAttr("disabled").text(idc_localization_strings.continue_checkout).removeClass("processing")});var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),a={product_id:s,first_name:n,last_name:u,email:l,pw:r},e=jQuery("#extra_fields input, #extra_fields select");jQuery.each(e,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value)});var c="";jQuery.each(o.posts,function(){c=c+"&"+this.name+"="+this.value}),jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_get_level",Level:s},success:function(e){var r=JSON.parse(e),t=r.recurring_type,a=parseFloat(jQuery('input[name="pwyw-price"]').val()),i=h(json,a);
// Calling ajax to get the button code
// #devnote shouldn't we send customer var instead of single vars?
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_get_coinbase_button",product_id:s,product_name:r.level_name,product_price:i,product_currency:"USD",fname:n,lname:u,email:l,transaction_type:"recurring"==x?"recurring":"",recurring_period:t,guestCheckout:idcPayVars.isGuestCheckout,query_string:c},success:function(e){
//console.log(res);
var t=JSON.parse(e);if("success"==t.response)
/*var iframeId = 'coinbase_inline_iframe_' + json_b.code;
								var iframeSrc = 'https://www.coinbase.com/checkouts/' + json_b.code + '/inline';
								jQuery('#coinbaseload iframe').attr('id', iframeId);
								jQuery('#coinbaseload iframe').attr('src', iframeSrc);
								jQuery('#coinbaseload').toggle();*/
window.location.href="https://www.coinbase.com/checkouts/"+t.code,jQuery(document).on("coinbase_button_loaded",function(e,r){console.log("#coinbaseload loaded"),jQuery(document).trigger("coinbase_show_modal",t.code),jQuery(document).on("coinbase_payment_complete",function(e,r){
//console.log("Payment completed for button " + code);
var t=jQuery("#payment-form").data("product");window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+s+"&paykey="+r+c})});else{var r=t.message;
// now need to re-enable button and print error
jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing");var a=jQuery(".payment-type-selector .active").attr("id");jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(r)}}})}}),jQuery("#id-main-submit").text(idc_localization_strings.processing+"...").attr("disabled")}
// Adaptive PayPal payments
else if("submitPaymentPPAdaptive"==jQuery("#id-main-submit").attr("name")){var s=jQuery("#payment-form").data("product"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),a={product_id:s,first_name:n,last_name:u,email:l,pw:r};
// Calling ajax to get the button code
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_get_ppadaptive_paykey",product_id:s,Customer:a,Type:"recurring"==x?"recurring":"",PWYW:t,txnType:S,Renewable:w,guestCheckout:idcPayVars.isGuestCheckout,queryString:c},success:function(e){
//console.log(res);
var r=JSON.parse(e);if("success"==r.response){
//alert('device: ' + getDevice());
var t=b();if(console.log("device: "+t),"mobile"==t){var a=memberdeck_paypal_adaptive+"?expType=mini&paykey="+r.token;console.log("Loading PayPal page"),
// jQuery('.checkout-header').append('<br><code>log: Loading PayPal page</code><br/>');
k(a,r.return_address)}else A=new PAYPAL.apps.DGFlow({trigger:"ppAdapSubmitBtn"});"recurring"==x||"preauth"==S?(jQuery("#preapprovalkey").val(r.token),jQuery("#ppAdaptiveForm").attr("action",memberdeck_paypal_adaptive_preapproval)):(jQuery("#paykey").val(r.token),jQuery("#ppAdaptiveForm").attr("action",memberdeck_paypal_adaptive)),"mobile"!==t&&jQuery("#ppAdapSubmitBtn").trigger("click")}else jQuery("#id-main-submit").removeAttr("disabled").removeClass("processing").text(idc_localization_strings.pay_with_paypal),jQuery(".payment-errors").text(r.message)}}),jQuery("#id-main-submit").text(idc_localization_strings.processing+"...").attr("disabled")}else if("submitPaymentPaypal"==jQuery("#id-main-submit").attr("name")){
//console.log('paypal');
jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var Q=jQuery("#payment-form").data("currency-code"),n=jQuery(".first-name").val(),u=jQuery(".last-name").val(),l=jQuery("#payment-form .email").val(),r=jQuery(".pw").val(),_=jQuery(".cpw").val(),s=jQuery("#payment-form").data("product"),t=parseFloat(jQuery('input[name="pwyw-price"]').val());jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_get_level",Level:s},success:function(e){
//console.log(json);
//return false;
if(
//console.log(res);
json=JSON.parse(e),json){if(
//console.log(json);
null!==idcPayVars.claim_paypal&&1<idcPayVars.claim_paypal.length&&(memberdeck_pp=idcPayVars.claim_paypal),f(json,t),"recurring"==x){var r=json.recurring_type.charAt(0).toUpperCase();jQuery("#buyform input#pp-times").val(1),jQuery("#buyform input#pp-recurring").val(r),1==json.trial_period?(jQuery('#buyform input[name="a1"]').val("0"),jQuery('#buyform input[name="p1"]').val(json.trial_length),jQuery('#buyform input[name="t1"]').val(json.trial_type.charAt(0).toUpperCase())):(jQuery('#buyform input[name="a1"]').remove(),jQuery('#buyform input[name="p1"]').remove(),jQuery('#buyform input[name="t1"]').remove())}jQuery("#buyform").attr("action",memberdeck_paypal),jQuery('#buyform input[name="currency_code"]').val(Q),jQuery('#buyform input[name="item_number"]').val(json.id),jQuery('#buyform input[name="item_name"]').val(json.level_name),jQuery('#buyform input[name="return"]').val(memberdeck_returnurl+permalink_prefix+"ppsuccess=1"),jQuery('#buyform input[name="cancel_return"]').val(memberdeck_returnurl+permalink_prefix+"ppsuccess=0"),jQuery('#buyform input[name="notify_url"]').val(memberdeck_siteurl+permalink_prefix+"memberdeck_notify=pp&email="+l+"&guest_checkout="+idcPayVars.isGuestCheckout+c),jQuery('#buyform input[name="business"]').val(memberdeck_pp),jQuery('#buyform input[name="discount_amount"]').val("0").remove(),jQuery("#buyform").submit()}}})}jQuery(document).trigger("idcPaymentChecksAfter",[t,c,o])}function v(e,r){var t=parseFloat(jQuery('input[name="pwyw-price"]').val()),a=jQuery("#extra_fields input, #extra_fields select"),o={posts:{}};jQuery.each(a,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),o.posts[e]={},o.posts[e].name=t,o.posts[e].value=value)});var c="";if(jQuery.each(o.posts,function(){c=c+"&"+this.name+"="+encodeURIComponent(this.value)}),r.error)jQuery(".payment-errors").text(r.error.message),jQuery(".submit-button").removeAttr("disabled").removeClass("processing"),jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout);else{jQuery("#id-main-submit").text(idc_localization_strings.processing+"...");var i=jQuery("#payment-form"),s=r.id;
//console.log(token);
i.append('<input type="hidden" name="stripeToken" value="'+s+'"/>');var n,u,l,d,m,y={product_id:jQuery("#payment-form").data("product"),first_name:jQuery(".first-name").val(),last_name:jQuery(".last-name").val(),email:jQuery("#payment-form .email").val(),pw:jQuery(".pw").val()};
//console.log(customer);
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:"stripe",Customer:y,Token:s,Fields:o.posts,txnType:S,Renewable:w,PWYW:t},success:function(e){if(console.log(e),json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,o,s]),jQuery(document).trigger("stripeSuccess",[a,n,i,t,r,o,s]),
// Code for Custom Goal: Sale
//_vis_opt_goal_conversion(201);
//_vis_opt_goal_conversion(202);
// set a timeout for 1 sec to allow trigger time to fire
setTimeout(function(){window.location=idcPayVars.redirectURL+permalink_prefix+"idc_product="+t+"&paykey="+r+c},1e3)}else{var u;jQuery("#id-main-submit").removeAttr("disabled").text("").removeClass("processing"),"pay-with-paypal"==jQuery(".payment-type-selector .active").attr("id")?jQuery("#id-main-submit").text(idc_localization_strings.pay_with_paypal):jQuery("#id-main-submit").text(idc_localization_strings.continue_checkout),jQuery(".payment-errors").text(json.message)}}})}}function c(){
//console.log('checkCreditCard() called');
var e=!1;
// if Credit card field exists
if(0<jQuery("#stripe-input input.card-number").length){
//console.log('credit card exists');
var r=jQuery("#stripe-input input.card-number"),t=jQuery("#stripe-input input.card-cvc"),a=jQuery("#stripe-input input.card-expiry-month"),i=jQuery("#stripe-input input.card-expiry-year"),s=jQuery("#stripe-input input.zip-code");
// Credit card number field
jQuery(r).val().length<10?(jQuery(r).addClass(p),jQuery(r).siblings(".error-info").show(),e=!0):(
//console.log('.card-number: ', jQuery(card_number).val());
jQuery(r).removeClass(p),jQuery(r).siblings(".error-info").hide()),
// Card CVC field
jQuery(t).val().length<1?(
//console.log('cvc is less than 1');
jQuery(t).addClass(p),jQuery(t).siblings(".error-info").show(),e=!0):(
//console.log('.card_cvc: ', jQuery(card_cvc).val());
jQuery(t).removeClass(p),jQuery(t).siblings(".error-info").hide()),
// Card Expiry date Month field
jQuery(a).val().length<1?(jQuery(a).addClass(p),e=!0):2<jQuery(a).val().length?(jQuery(a).addClass(p),e=!0):
//console.log('card_expiry_month: ', jQuery(card_expiry_month).val());
jQuery(a).removeClass(p),
// Card Expiry date Year field
jQuery(i).val().length<1?(jQuery(i).addClass(p),e=!0):4<jQuery(i).val().length?(jQuery(i).addClass(p),e=!0):
//console.log('card_expiry_year: ', jQuery(card_expiry_year).val());
jQuery(i).removeClass(p),
// Zip code check, if exists
0<s.length&&(jQuery(s).val().length<1?(jQuery(s).addClass(p),jQuery(s).siblings(".error-info").show(),e=!0):(jQuery(s).removeClass(p),jQuery(s).siblings(".error-info").hide()))}return e}function f(e,r){var t=h(e,r);jQuery("#buyform input#pp-price").val(t)}function h(e,r){var t;if(0<jQuery('[name="upgrade-level-price"]').length)var t=jQuery('[name="upgrade-level-price"]').val();else if(w)var t=e.renewal_price;else t=(t=e.level_price)||0;if(0<parseFloat(r)&&parseFloat(r)>parseFloat(t))var a=r;else var a=t;return a}
/* To return which device it is */function b(e){var r=navigator.userAgent.toLowerCase(),t="",a="";
// Now return device
// if it's iOS device
return t=r.match(/iPhone|iPad|iPod/i)?(a="ios","mobile"):r.match(/Android/i)?(a="android","mobile"):r.match(/BlackBerry/i)?(a="blackberry","mobile"):r.match(/Opera Mini/i)?(a="unknown","mobile"):r.match(/IEMobile/i)?(a="windows","mobile"):a="desktop",void 0!==e&&1==e?a:t}function g(){jQuery(".buy-tooltip").data("levelid",null),jQuery(".buy-tooltip").data("pid",null);var e=jQuery(".buy-tooltip");
//jQuery('.tooltip-wrapper').html('<div class="inner-tooltip"></div>');
jQuery(".buy-tooltip").hide(),jQuery(e).find(".tt-product-name").text(""),jQuery(e).find(".tt-price").text(""),jQuery(e).find(".tt-credit-value").text(""),jQuery(e).find(".tt-more").attr("href",""),jQuery(e).find(".tt-credit-sep, .credits-avail, .credit-text").show()}
/* PayPal Adaptive function for making payments using mobile devices */
function k(e,r){var t=navigator.userAgent,a=0,i;
// mobile device
if(1/*ua.match(/iPhone|iPod|Android|Blackberry.*WebKit/i)*/)
//VERY IMPORTANT - You must use '_blank' and NOT name the window if you want it to work with chrome ios on iphone
//See this bug report from google explaining the issue: https://code.google.com/p/chromium/issues/detail?id=136610
// jQuery('.checkout-header').append('<code>log: Opening window</code><br/>');
i=window.open(e,"_blank"),
// chrome.windows.create({url: paypalURL, type: 'popup'});
// jQuery('.checkout-header').append('<code>log: Win: '+ win +'</code><br/>');
// jQuery('.checkout-header').append('<code>log: Well, its done, window opened</code><br/>');
a=setInterval(function(){i&&i.closed&&(
// jQuery('.checkout-header').append('<code>log: Win is present. win.closed: '+ win.closed +'</code><br/>');
clearInterval(a),N(r))},1e3);else{
//Desktop device
var s=400,n=550,u,o;window.outerWidth?(u=Math.round((window.outerWidth-s)/2)+window.screenX,o=Math.round((window.outerHeight-n)/2)+window.screenY):window.screen.width&&(u=Math.round((window.screen.width-s)/2),o=Math.round((window.screen.height-n)/2)),
//VERY IMPORTANT - You must use '_blank' and NOT name the window if you want it to work with chrome ios on iphone
//See this bug report from google explaining the issue: https://code.google.com/p/chromium/issues/detail?id=136610
i=window.open(e,"_blank","top="+o+", left="+u+", width="+s+", height="+n+", location=0, status=0, toolbar=0, menubar=0, resizable=0, scrollbars=1"),a=setInterval(function(){i&&i.closed&&(clearInterval(a),N(r))},1e3)}}var p=idcCheckoutErrorClass();
//tooltip checkoutform 
// Vars and functions used only when it's a checkout form
if(jQuery(".checkout-tooltip").hover(function(){jQuery(this).hasClass("tooltip-active")?(jQuery(this).removeClass("tooltip-active"),jQuery(".tooltip-text").css("height","0"),jQuery(".tooltip-text").css("visibility","hidden"),
//jQuery('.tooltip-text').removeClass('tooltip-text-hover'); 
jQuery(".checkout-tooltip i").removeClass("tooltip-color")):(jQuery(this).addClass("tooltip-active"),jQuery(".tooltip-text").css("visibility","visible"),jQuery(".tooltip-text").css("height","30px"),
//jQuery('.tooltip-text').addClass('tooltip-text-hover');
jQuery(".checkout-tooltip i").addClass("tooltip-color"))}),jQuery(".tooltip-text i.close").hover(function(){jQuery(".tooltip-text").removeClass("tooltip-text-hover"),jQuery(".checkout-tooltip i").removeClass("tooltip-color")}),
// shortcode button stuff
jQuery(".idc_shortcode_button").click(function(){var e=jQuery(this).data("source");
//console.log('jQuery(this): ', jQuery(this), ', lbSource: "', jQuery.trim(lbSource), '"', ', jQuery(lbSource): ', jQuery(lbSource));
0<e.length&&openLBGlobal(e)}),jQuery(".idc_button_lightbox .level_select").change(function(){var e=jQuery(this).find("option:selected").val();jQuery('.idc_button_lightbox input[name="product_id"]').val(e)}),jQuery(".idc_button_submit").click(function(e){var r,t;if(e.preventDefault(),jQuery(".payment-errors").hide(),0<jQuery('input[name="price"]').length)
// this is single product IDC_BUTTON lightbox
r=parseFloat(jQuery('input[name="price"]').val());else if((
// this is standard or multiple prodcut IDC_BUTTON lightbox
r=parseFloat(jQuery('input[name="total"]').val()))<parseFloat(jQuery(".idc_button_lightbox .level_select option:selected").data("price")))return void jQuery(".payment-errors").show();var a=jQuery('form[name="idc_button_checkout_form"]').attr("action");
// Check that inputted price is greater than or equal to level price
// #devnote may be redundant based on above check, we also do work in IDF
if(a=a+"?idc_button_submit=1&price="+r,parseFloat(jQuery(".idc-button-default-price").data("level-price"))>r)return jQuery(".button-error-placeholder .payment-errors").show(),!1;jQuery(".button-error-placeholder .payment-errors").hide(),jQuery('form[name="idc_button_checkout_form"]').attr("action",a).submit()}),
// For restoring default price, if input price is less than deafult price
// #devnote also redundant because it's only used for IDC lightbox single product also do work in idf
jQuery('input[name="price"]').change(function(e){var r=parseFloat(jQuery(this).val()),t=parseFloat(jQuery(".idc-button-default-price").data("level-price"));r<t&&jQuery(this).val(t)}),
// dashboard stuff
0<jQuery(".dashboardmenu").length&&jQuery(".dashboardmenu .active").length<=0&&jQuery(".dashboardmenu li").eq(0).addClass("active"),jQuery("form#payment-settings input").length<=1&&jQuery('input[name="creator_settings_submit"]').hide(),0<jQuery(".checkout-wrapper").length){jQuery(document).trigger("idcCheckoutLoaded");var u=jQuery("#payment-form").data("pay-by-credits"),x=jQuery("#payment-form").data("type");setTrialObj();var l=jQuery("#payment-form").data("limit-term"),d=jQuery("#payment-form").data("term-length");l&&(jQuery("#payment-form #pay-with-paypal").hide(),epp=0);var m=idcIsLoggedIn(),w=jQuery("#payment-form").data("renewable");if("1"==es)var y=jQuery("#stripe-input").data("symbol");var j=jQuery("#stripe-input").data("customer-id"),Q=jQuery(".currency-symbol").children("sup").text();if(1===u)var _=jQuery("#finaldescCredits").data("credits-label");var C=jQuery("#finaldescCoinbase").data("cb-symbol"),S=jQuery("#payment-form").data("txn-type");scpk=e();var P=r(),z=jQuery('input[name="reg-price"]').val(),T=parseFloat(jQuery('input[name="pwyw-price"]').val()),O=jQuery(".currency-symbol .product-price").text();if("preauth"==S&&(jQuery("#payment-form #pay-with-paypal").parent("div").remove(),a(),"1"==idc_elw&&"3dsecure"==idc_lemonway_method&&jQuery("#payment-form #pay-with-lemonway").parent("div").remove(),no_methods()),"recurring"==x){var F=jQuery("#payment-form").data("recurring");jQuery("#payment-form #pay-with-fd").parent("div").remove(),jQuery("#payment-form #pay-with-mc").parent("div").remove(),jQuery("#payment-form #pay-with-lemonway").parent("div").remove(),1<=parseFloat(T)&&parseFloat(z)<parseFloat(T)&&jQuery("#pay-with-stripe").parent("div").remove(),no_methods()}"free"==idcPayVars.isFree?(jQuery(".checkout-payment").hasClass("active")&&(jQuery(".checkout-payment").removeClass("active"),jQuery(".checkout-confirmation").addClass("active")),
// Showing the terms and checkout button
0<jQuery(".idc-terms-checkbox").length&&jQuery(".idc-terms-checkbox").show(),jQuery(".main-submit-wrapper").show(),jQuery(".confirm-screen").show(),jQuery(".checkout-header").hide(),jQuery("#payment-form #id-main-submit").text(idc_localization_strings.continue)):1<jQuery("#payment-form .pay_selector:visible").length?(jQuery("#payment-form #id-main-submit").text(idc_localization_strings.choose_payment_method),jQuery("#payment-form #id-main-submit").attr("disabled","disabled")):
// not free and only one selector
t()}jQuery(document).bind("idc_tps_option",function(){t()});var A="";
/* MDID Backer List */
if(jQuery(".link-terms-conditions a").click(function(e){return openLBGlobal(jQuery(".idc-terms-conditions")),!1}),jQuery(".link-privacy-policy a").click(function(e){return openLBGlobal(jQuery(".idc-privacy-policy")),!1}),
// Calling lightbox for social sharing box if it exists
0<jQuery(".idc_lightbox_attach").length&&openLBGlobal(jQuery(".idc_lightbox_attach")),jQuery(".pay_selector").click(function(e){e.preventDefault(),
// trigger anytime a payment method is selected
jQuery(document).trigger("idcPaySelect",this),jQuery(".checkout-payment").hasClass("active")&&(jQuery(".checkout-payment").removeClass("active"),jQuery(".checkout-confirmation").addClass("active")),
// Showing the terms and checkout button
0<jQuery(".idc-terms-checkbox").length&&jQuery(".idc-terms-checkbox").show(),jQuery(".main-submit-wrapper").show(),jQuery(".confirm-screen").show()}),jQuery(document).bind("idcPaySelect",function(e,r){e.preventDefault(),jQuery(".pay_selector").removeClass("active"),jQuery(r).addClass("active"),idcPaySelectActions(r),setGuestCheckout()}),jQuery(document).bind("idc_no_methods",function(){no_methods()}),jQuery(".reveal-login").click(function(e){e.preventDefault(),jQuery("#payment-form").hide(),jQuery(".disclaimer").hide(),jQuery(".login-form").show()}),jQuery(".hide-login").click(function(e){e.preventDefault(),jQuery("#payment-form").show(),jQuery(".disclaimer").show(),jQuery(".login-form").hide()}),jQuery(".reveal-account").click(function(e){e.preventDefault(),jQuery(this).hide(),jQuery("#create_account").show(),setGuestCheckout()}),jQuery("#id-main-submit").click(function(e){if(e.preventDefault(),!i()){var r=jQuery(this).attr("name");"1"==es&&"free"!==idcPayVars.isFree&&(1<jQuery(".pay_selector").length?jQuery("#pay-with-stripe").hasClass("active")&&Stripe.setPublishableKey(memberdeck_pk):Stripe.setPublishableKey(memberdeck_pk)),jQuery(document).trigger("idcCheckoutSubmit",r)}}),jQuery("form[name='reg-form']").submit(function(e){e.preventDefault(),jQuery(".payment-errors").text(""),jQuery("#id-reg-submit").attr("disabled","disabled");var r=jQuery(".first-name").val(),t=jQuery(".last-name").val(),a=jQuery("#payment-form .email").val(),i=jQuery(".pw").val(),s=jQuery(".cpw").val(),n=jQuery("form[name='reg-form']").data("regkey");jQuery(this).find("input, select").removeClass(p);
//console.log(regkey);
var u=!0,o=!1;if(null!=n&&""!=n||(
//console.log(uid);
//jQuery(".payment-errors").text("There was an error processing your registration. Please contact site administrator for assistance");
u=!1),i!==s){jQuery(".payment-errors").text(idc_localization_strings.passwords_mismatch_text);var c=!0}if(r.length<1||t.length<1||0==idfValidateEmail(a)||i.length<5)var c=!0;var l=jQuery('form[name="reg-form"] input.required:visible, form[name="reg-form"] select.required:visible');
//console.log('update: ' + update);
if(jQuery.each(l,function(e,r){var t=jQuery(r).val();"checkbox"==jQuery(r).attr("type")?"0"==jQuery(r).prop("checked")&&(jQuery(this).addClass(p),o=c=!0):""!=t&&void 0!==t||(jQuery(this).addClass(p),o=c=!0)}),1==c)
//console.log('error');
return jQuery(".payment-errors").append(idc_localization_strings.registration_fields_error_text),jQuery("#id-reg-submit").removeAttr("disabled").removeClass("processing"),!1;if(1==u){var d={regkey:n,first_name:r,last_name:t,email:a,pw:i};jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_update_user",User:d},success:function(e){
//console.log(res);
json=JSON.parse(e),"success"==json.response?window.location=memberdeck_durl+permalink_prefix+"account_updated=1":
//console.log(json.message);
json.message?jQuery(".payment-errors").text(json.message):jQuery(".payment-errors").text(idc_localization_strings.error_in_processing_registration_text)}})}else{var d={first_name:r,last_name:t,email:a,pw:i},m=jQuery('[id^="registration-form-extra-fields"] input'),y={posts:{}};
// Getting extra fields if any
jQuery.each(m,function(e,r){var t=jQuery(this).attr("name"),a=jQuery(this).attr("type");"checkbox"==a||"radio"==a?"checked"==jQuery(this).attr("checked")&&(value=jQuery(this).val(),y.posts[e]={},y.posts[e].name=t,y.posts[e].value=value):("SELECT"==this.tagName.toUpperCase()?value=jQuery(this).find(":selected").val():value=encodeURIComponent(jQuery(this).val()),y.posts[e]={},y.posts[e].name=t,y.posts[e].value=value)}),jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"memberdeck_insert_user",User:d,Fields:y.posts},success:function(e){console.log(e),json=JSON.parse(e),"success"==json.response?window.location=memberdeck_durl+permalink_prefix+"account_created=1":(
//console.log(json.message);
jQuery("#id-reg-submit").removeAttr("disabled"),json.message?jQuery(".payment-errors").text(json.message):jQuery(".payment-errors").text(idc_localization_strings.error_in_processing_registration_text))}})}}),jQuery("#idc-downloads .inactive").click(function(e){
// If tooltip is open, don't close it by clicking anywhere on .inactive except the .tt-close button
if(e.preventDefault(),jQuery(".oneclick").click(function(){var e=jQuery(this).attr("href");window.location.href=e}),"open"==jQuery(".buy-tooltip").data("closing"))return!1;
// Check that tooltip is just closed, then prevent from going further
if(g(),"processing"==jQuery(".buy-tooltip").data("closing"))return jQuery(".buy-tooltip").data("closing","closed"),!1;var s=jQuery(this).data("levelid"),n=jQuery(this).data("pid"),u=jQuery(this).children(".inactive-item").attr("href"),o=jQuery(this).children(".tooltip-wrapper");if(0<s){var r=jQuery(this).offset(),t=r.top,a=r.left,i=jQuery(this).height(),c=jQuery(this).width(),l=jQuery(".buy-tooltip").height(),d=jQuery(".components button").width(),m=jQuery(".components button").height(),y=jQuery(".components button").css("padding-top").replace("px",""),p=jQuery(".buy-tooltip").width(),j=jQuery(".buy-tooltip").css("padding-top").replace("px",""),Q=jQuery(".buy-tooltip").css("padding-left").replace("px","");
//console.log(offset);
ttTotalTop=2*j;var _=!0;jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"md_level_data",level_id:s,get_instant_checkout:"true"},success:function(e){if(
//console.log(res);
json=JSON.parse(e),json){var r=json.instant_checkout;json=json.level,
//console.log(json);
jQuery(".buy-tooltip").data("levelid",s),jQuery(".buy-tooltip").data("pid",n);var t=jQuery(".buy-tooltip"),a,i,i;if(jQuery(t).find(".tt-product-name").text(json.level_name),jQuery(t).find(".tt-price").text(json.level_price),0<json.credit_value)jQuery(t).find(".tt-credit-value").text(json.credit_value),parseFloat(jQuery(".credits-avail").data("credits-available"))>=json.credit_value&&jQuery('[name="occ_method"]').children('option[value="credit"]').length<=0&&jQuery('[name="occ_method"]').append(jQuery("<option/>",{value:"credit",text:idc_localization_strings.pay_with_credits}));else
// Removing the option to pay by credits if it's less than 0, else adding that option if removed
// #devnote there should be a way to simplify all of this manual js hiding
jQuery(".tt-credit-sep, .credits-avail, .credit-text").hide(),jQuery('[name="occ_method"]').children('option[value="credit"]').remove();
// If instant checkout is not enabled and purchase with credits not available either, redirect to infoLink
if(0==r&&json.credit_value<=0&&(_=!1,window.location=u),
// If only one default option is left, then rename it to No Payment Options, else make first option to Select Payment Options
1==jQuery('[name="occ_method"]').children(":enabled").length?(jQuery('[name="occ_method"]').children('option[value=""]').html(idc_localization_strings.no_payment_options),
// instant checkout not available and no credits so redirecting...
_=!1,window.location=u):1<jQuery('[name="occ_method"]').children(":enabled").length&&
// Renaming value="" options to Select Payment Options if it was rename earlier
jQuery('[name="occ_method"]').children('option[value=""]').html(idc_localization_strings.select_payment_option),1<json.credit_value)(i=jQuery(t).find(".credit-text")).text(i.data("credit-label-p"));else(i=jQuery(t).find(".credit-text")).text(i.data("credit-label-s"));jQuery(".tt-more").attr("href",u),_&&(jQuery(t).show().data("closing","open"),jQuery(o).append(jQuery(".buy-tooltip")),jQuery(window).trigger("tt_open",[o,json]))}}})}else window.location.href=u}),jQuery(".tt-close").click(function(e){e.preventDefault(),
//if (!jQuery('.buy-tooltip').is(':hover') && jQuery('.buy-tooltip').is(':visible')) {
//console.log('leave');
jQuery(".buy-tooltip").data("closing","processing"),g()}),jQuery('select[name="occ_method"]').change(function(){0<jQuery('select[name="occ_method"]').val().length?jQuery(".md_occ").removeAttr("disabled"):jQuery(".md_occ").attr("disabled","disabled")}),jQuery(".md_occ").click(function(e){e.preventDefault(),jQuery(this).attr("disabled","disabled").addClass("processing"),jQuery(this).text(idc_localization_strings.processing);var r=jQuery('select[name="occ_method"]').val(),t=jQuery(".buy-tooltip").data("levelid"),a=jQuery(".buy-tooltip").data("pid"),i,s,n={product_id:t,first_name:jQuery(".md-firstname").text(),last_name:jQuery(".md-lastname").text()},u=[{name:"project_id",value:a},{name:"project_level",value:0}];
//console.log(payMethod);
"cc"==r?jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idmember_create_customer",Source:null,Customer:n,Token:"customer",Fields:u,txnType:null},success:function(e){if(
//console.log(res);
json=JSON.parse(e),"success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=json.customer_id;jQuery(document).trigger("idcPaymentSuccess",[a,n,i,t,r,null,s]),jQuery(document).trigger("stripeSuccess",[a,n,i,t,r,null,s]),
//location.reload();
window.location="?idc_product="+t+"&paykey="+r}else jQuery(".md_occ").removeAttr("disabled").removeClass("processing"),jQuery(".md_occ").text("Confirm")}}):"credit"==r?jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"md_use_credit",Customer:n,Token:"customer",PWYW:T},success:function(e){if(
//console.log(res);
json=JSON.parse(e),json)
//console.log(json);
if("success"==json.response){var r=json.paykey,t=json.product,a=json.order_id,i=json.user_id,s=json.type,n=null;jQuery(document).trigger("creditSuccess",[a,n,i,t,r,null,s]),
//location.reload();
window.location="?idc_product="+t+"&paykey="+r}else jQuery(".md_occ").removeAttr("disabled").removeClass("processing")}}):jQuery(".md_occ").removeAttr("disabled").removeClass("processing")}),
/* Check for PP Adaptive Completion */
0<jQuery("div#idc_ppadap_return").length&&window!=top&&top.location.replace(document.location)
/* Edit Profile js */,jQuery('select[name="sub_list"]').change(function(){var e;if("0"!==jQuery(this).children("option:selected").val()){var r=jQuery(this).children("option:selected").text();
//console.log(planID);
jQuery('button[name="cancel_sub"]').removeAttr("disabled").show()}else jQuery('button[name="cancel_sub"]').attr("disabled","disabled").hide()}),jQuery('button[name="cancel_sub"]').click(function(e){e.preventDefault(),jQuery(".sub_response").text("").removeClass().addClass("sub_response");var r=jQuery('select[name="sub_list"]').children("option:selected").val(),t=jQuery('select[name="sub_list"]').children("option:selected").text(),a=jQuery('select[name="sub_list"]').data("userid"),i=jQuery('select[name="sub_list"]').val(),s=jQuery('select[name="sub_list"] option[value="'+i+'"]').data("gateway");jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"idc_cancel_sub",plan_id:r,plan:t,user_id:a,payment_gateway:s},success:function(e){
//console.log(res);
if(e){var r=JSON.parse(e);"success"==r.status&&(jQuery('select[name="sub_list"] option:selected').remove(),1==jQuery('select[name="sub_list"] option').size()&&jQuery('button[name="cancel_sub"]').attr("disabled","disabled").hide()),jQuery(".sub_response").text(r.message).addClass(r.status)}}})}),
/* Bridge js */
// First, let's apply MemberDeck links to to standard IgnitionDeck widgets
jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"mdid_project_list"},success:function(e){
//console.log(res);
// would trigger 0 if !$crowdfunding
"0"!==e&&(json=JSON.parse(e),
//console.log(json);
jQuery.each(json,function(e,i){
//console.log('k: ' + k + ', v: ' + v);
jQuery.each(jQuery(".id-full, #ign-product-levels, .widget_level_container"),function(){var e=jQuery(this),r=jQuery(this).data("projectid");if(i&&r==i.id){
// Let's transform the links
var t=jQuery(this).find(".level-binding");jQuery.each(t,function(e,r){var t=jQuery(this).attr("href");if(t&&-1==t.indexOf("mdid")){var a=t.replace("prodid","mdid_checkout");jQuery(this).attr("href",a)}});var a=jQuery(this).attr("id");a&&a.indexOf("ign-product-levels")}})}))}}),
/* Payment Settings js */
// hide payment settings butotn if the form is empty
0<jQuery(".payment-settings").length&&jQuery(".payment-settings input").length<=1&&jQuery(".payment-settings .submit").hide()
/* MDID File Upload */,jQuery('input[name="ide_fes_file_upload_submit"]').click(function(e){
//e.preventDefault();
jQuery(".required").removeClass("error");var r=!1;if(jQuery.each(jQuery('form[name="ide_fes_file_upload_form"] input'),function(){jQuery(this).val().length<=0&&"submit"!=jQuery(this).attr("type")&&(jQuery(".required").addClass("error"),console.log(this),r=!0)}),r)return!1}),0<jQuery(".content_tabs").length){var V=jQuery(this).find(".ign_backer_list").data("count");null!=V&&0!=V||jQuery("#backers_tab").hide()}jQuery(".backer_list_more a").click(function(e){e.preventDefault;var r=jQuery(this).data("first");jQuery(this).data("first",parseInt(r)+20);var t=jQuery(this).data("last"),a;jQuery(this).data("last",parseInt(t)+20),jQuery(this).data("total")<=t+20&&jQuery(this).hide();var i,s={First:r,Last:t,Project:jQuery(this).data("project")};jQuery.ajax({url:memberdeck_ajaxurl,type:"POST",data:{action:"mdid_show_more_backers",Vars:s},success:function(e){
//console.log(res);
if(e){var r=JSON.parse(e);jQuery(".ign_backer_list li").last().after(r),jQuery(".ign_backer_list li.new_backer_item").fadeIn("slow").removeClass("new_backer_item"),jQuery(document).trigger("backer_list_more")}}})}),
/* Login form validations */
0<jQuery(".md-requiredlogin").length&&
//console.log('its here. md-requiredlogin');
jQuery('.md-requiredlogin input[name="wp-submit"]').click(function(e){var r=!1,t=!1,a=!1;
// there is an error, output it
return""===jQuery('.md-requiredlogin input[name="log"]').val()&&(t=r=!0),""===jQuery('.md-requiredlogin input[name="pwd"]').val()&&(a=r=!0),r&&(t||a)?(jQuery(".md-requiredlogin .error.blank-field").removeClass("hide"),!1):!r})
/* memberdeck edge on dashboard for tooltip  
	
		var wrapperW = jQuery('.memberdeck').outerWidth(true);
		var boxW = jQuery('.tooltip-wrapper').width();
		var boxPosX = jQuery('.tooltip-wrapper').position().left;
		var touched = wrapperW - (boxW + boxPosX);
		console.log('wrapperW',wrapperW);
		console.log('boxW',boxW);
		console.log('boxPosX',boxPosX);
		console.log('touched',touched);
		if( touched <= 0 ){
		   jQuery('.tooltip-wrapper').css('left', '11px'); 
		   jQuery('.memberdeck .buy-tooltip').addClass('buy-tooltip-hidden');
		}
 	/* section ends */
/* Validation for Edit Profile screen */,jQuery("#edit-profile-submit").click(function(e){var r=!1,t=jQuery(".email").val();
// if there are errors, scroll to the first error
if(0==idfValidateEmail(t)?(r=!0,jQuery(".email").addClass("error")):jQuery(".email").removeClass("error"),r){var a=jQuery(".error").get(0),i=jQuery(a).offset().top-55;jQuery("html, body").animate({scrollTop:i},500)}return!r});var N=function(e){location.replace(e);
// Here you would need to pass on the payKey to your server side handle (use session variable) to call the PaymentDetails API to make sure Payment has been successful
// based on the payment status- redirect to your success or cancel/failed page
}}),jQuery(document).bind("idcCheckoutLoaded",function(e){setGuestCheckout(),setIdcPayObj()});