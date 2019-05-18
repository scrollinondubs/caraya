<div id="finaldescPrepay" class="finaldesc" data-currency="EUR" data-currency-symbol="€" style="display:none;">
	<?php
	_e('Please remit a payment of  ', 'memberdeck').'.';
	echo ' '.(isset($level_price) ? apply_filters('idc_price_format', $level_price) : '');
	echo ' <span class="currency-symbol">EUR</span>.<br/>';
	echo __('Details will be provided upon form submission', 'memberdeck').'.'; ?>
</div>