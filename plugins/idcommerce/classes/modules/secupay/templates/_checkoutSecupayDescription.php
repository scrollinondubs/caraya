<div id="finaldescSecupay" class="finaldesc" data-currency="EUR" data-currency-symbol="€" style="display:none;">
	<?php
	echo apply_filters('id_secupay_checkout_text', __('You will be billed via Secupay in the amount of ', 'memberdeck'));
	echo ' '.(isset($level_price) ? apply_filters('idc_price_format', $level_price) : '');
	echo ' <span class="currency-symbol">EUR</span> ';
	?>
</div>