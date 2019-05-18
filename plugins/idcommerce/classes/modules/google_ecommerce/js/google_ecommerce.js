jQuery(document).ready(function() {
	// orderID, custID, userID, product, paykey, fields, type
	jQuery(document).bind('idcPaymentSuccess', function(e, orderID, custID, userID, product, paykey, fields, type) {
		jQuery.ajax({
			url: idf_ajaxurl,
			type: 'POST',
			data: {action: 'google_ecommerce_order_data', Order: orderID, User: userID},
			success: function(res) {
				//console.log(res);
				if (res !== undefined) {
					var json = JSON.parse(res);
					var txn_id = '';
					var name = '';
					var price = 0;
					if (json.order.transaction_id !== undefined) {var txn_id = json.order.transaction_id};
					if (json.level.level_name !== undefined) {var name = json.level.level_name};
					if (json.order.price !== undefined) {var price = json.order.price};
					//console.log('txn: ' + txn_id + ' name: ' + name + ' price: ' + price);
					ga('ecommerce:addTransaction', {
						'id': txn_id,
						'revenue': parseFloat(price),
					});
					ga('ecommerce:addItem', {
					  'id': txn_id,
					  'name': name,
					  'price': parseFloat(price),
					  'quantity': '1'
					});
					ga('ecommerce:send');
					ga('ecommerce:clear');
				}
			}
		});
	});
});
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', idc_ga_property_code, 'auto');
ga('send', 'pageview');
ga('require', 'ecommerce');