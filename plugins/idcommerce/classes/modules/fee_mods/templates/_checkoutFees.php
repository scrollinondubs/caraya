<div class="idc_checkout_extra_fields fee_mods hide" id="checkout-form-extra-fields-fees">
	<div class="form-row checklist">
		<p class="label-description"><?php echo (isset($sc_settings['cover_fees_on_checkout_text']) ? $sc_settings['cover_fees_on_checkout_text'] : ''); ?></p>
		<input type="checkbox" name="cover_fees_on_checkout" id="cover_fees_on_checkout" value="<?php echo $formatted_fee; ?>"/><label for="cover_fees_on_checkout_label"><?php echo (isset($sc_settings['cover_fees_on_checkout_label']) ? $sc_settings['cover_fees_on_checkout_label'] : __('Increase payment to cover platform fees', 'memberdeck')); ?><?php echo ' ('.$fee_with_code.')'; ?></label>
	</div>
</div>