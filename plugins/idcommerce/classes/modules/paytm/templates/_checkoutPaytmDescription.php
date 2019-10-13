<div id="finaldescPaytm" class="finaldesc" data-currency="INR" data-currency-symbol="₹" style="display:none;">
	<?php
	echo apply_filters('id_paytm_checkout_text', sprintf(__('You will be redirected to Paytm to enter payment in the amount of %s %s. Payment is not complete until you have been redirected back to this website.', 'memberdeck'), (isset($level_price) ? apply_filters('idc_price_format', $level_price) : ''), '<span class="currency-symbol">INR</span>'));
	?>
</div>