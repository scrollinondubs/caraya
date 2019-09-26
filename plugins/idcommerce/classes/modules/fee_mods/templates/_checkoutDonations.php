<div class="idc_checkout_extra_fields fee_mods hide" id="checkout-form-extra-fields-donations">
	<div class="form-row number">
		<p class="label-description"><?php echo (isset($sc_settings['donations_on_checkout_text']) ? $sc_settings['donations_on_checkout_text'] : ''); ?></p>
		<label for="checkout_donation"><?php echo (isset($sc_settings['donations_on_checkout_label']) ? $sc_settings['donations_on_checkout_label'] : __('Donation Amount', 'memberdeck')); ?></label>
		<input type="number" name="checkout_donation" id="checkout_donation" value="" step="1" min="0.00"/>
	</div>
</div>