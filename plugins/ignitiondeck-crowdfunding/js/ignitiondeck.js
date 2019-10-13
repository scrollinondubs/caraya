var id = {
	'project': {},
	'form': {},
	'checkout': {}
};
jQuery(document).ready(function() {
	if (jQuery('.id-progress-raised').length > 0) {
		adjustHeights('.id-progress-raised');
	}
	jQuery('.hasvideo').click(function() {
		var vidSource = jQuery('.hasvideo iframe').attr('src');
		if (vidSource.indexOf('?rel=0&autoplay=1') == -1) {
	  		jQuery('.hasvideo iframe').attr('src', vidSource + '?rel=0&autoplay=1').show();
	  	}
	  	else {
	  		jQuery('.hasvideo iframe').show();
	  	}
	});

	/* Shortcode Grid Magic */
	var gridCount = jQuery('.grid_wrap').length;
	if (gridCount > 0) {
		switch(gridCount) {
			case '1':
				var wide = parseInt(jQuery('.grid_wrap').data('wide'));
				jQuery('.grid_item:nth-child(' + wide + 'n + ' + wide + ')').css('margin-right', 0);
				jQuery('.grid_item:nth-child(' + wide + 'n + ' + (wide + 1) + ')').css('clear', 'both');
				break;
			default:
				jQuery.each(jQuery('.grid_wrap'), function() {
					var wide = parseInt(jQuery(this).data('wide'));
					jQuery(this).children('.grid_item:nth-child(' + wide + 'n + ' + wide + ')').css('margin-right', 0);
					jQuery(this).children('.grid_item:nth-child(' + wide + 'n + ' + (wide + 1) + ')').css('clear', 'both');
				});
				break;
		}
	}
});

// Submit form function for Purchasing product
// probably unused
function submitPurchaseForm(ajax_url) {
	//var dgFlow = new PAYPAL.apps.DGFlow({ trigger: 'submitBtn' });
	//jQuery('#submitBtn').trigger('click');
	//return false;
	//jQuery('#btnPayKey').attr('style','background: url("../images/loading.gif") no-repeat scroll top right transparent;');
	jQuery('#btnPayKey').val('Processing Payment...');
	jQuery.ajax({
		type: "POST",
		url: ajax_url,
		data: "action=" + 'get_paypal_paykey'
		+ "&" + jQuery('#form_pay').serialize()
		,
		success: function(html) {						
			//alert(jQuery.trim(html));
			//console.log(jQuery.trim(html));
			reply = jQuery.trim(html).split("|");
			if (reply[0] == "success") {
				//console.log(reply[1]);
				//jQuery('#pay_form_embedded').attr("action",reply[2]);
				jQuery('#paykey').val(reply[1]);
				var dgFlow = new PAYPAL.apps.DGFlow({ trigger: 'submitBtn' });
				jQuery('#submitBtn').trigger('click');
			} else {
				//console.log("Error: "+reply[1]);
				jQuery('#btnPayKey').val('Make Payment');
				jQuery('#message-container').html(	'<div class="notification error">' +
									'<a href="#" class="close-notification" title="Hide Notification" rel="tooltip">x</a>' +
									'<p><strong>Payment Error</strong> '+ reply[1] +'</p>' +
								'</div>');
				jQuery('#message-container').show();
			}
		}
	});
	
	return false;
}