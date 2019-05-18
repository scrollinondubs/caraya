<div id="finaldescStripe" class="finaldesc" data-currency-symbol="<?php echo ((isset($es) && $es == 1) ? $stripe_symbol : $cc_currency_symbol); ?>" style="display:none;">
	<?php
	_e('Your card will be billed', 'memberdeck');
	echo ' '.(isset($level_price) ? apply_filters('idc_price_format', $level_price) : '');
	if (empty($combined_purchase_gateways['cc']) || !$combined_purchase_gateways['cc']) {
		echo ' <span class="currency-symbol">'.((isset($es) && $es == 1) ? $stripe_currency : $cc_currency).'</span> ';
	}
	echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('in ', 'memberdeck').$term_length.' ' : '');
	echo (isset($type) && $type == 'recurring' ? $recurring : '');
	echo (isset($type) && $type == 'recurring' && isset($limit_term) && $limit_term == '1' ? __('installments', 'memberdeck') : '');
	if (isset($combined_purchase_gateways['cc']) && $combined_purchase_gateways['cc']) { 
		echo '<span class="combined-product-desc"> '.__('plus', 'memberdeck').' '.apply_filters('idc_price_format', $combined_level->level_price).' <span class="currency-symbol">'.((isset($es) && $es == 1) ? $stripe_currency : $cc_currency).' '.$combined_level->recurring_type.'</span>';
	}
	echo ' '.__('and will appear on your statement as', 'memberdeck').': <em>'.(isset($coname) ? $coname : '').'</em>.';
	?>
</div>