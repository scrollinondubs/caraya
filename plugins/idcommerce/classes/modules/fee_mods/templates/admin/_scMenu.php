<div class="form-row">
	<h4><?php _e('Fee Mods', 'memberdeck'); ?></h4>
	<p><?php _e('Additional options for the handling of donations and platform fees', 'memberdeck'); ?>.</p>
</div>
<div class="form-check">
	<input type="checkbox" id="fee_mods" name="fee_mods" value="1" <?php echo (isset($sc_settings['fee_mods']) && $sc_settings['fee_mods'] ? 'checked="checked"' : ''); ?>/>
	<label for="fee_mods"><?php _e('Enable Per Project Fees', 'memberdeck'); ?></label>
</div>
<div class="form-check checkbox inline">
	<input type="checkbox" name="fee_mods_donations_on_checkout" id="donations_on_checkout" class="checkbox" value="1" <?php echo (isset($sc_settings['donations_on_checkout']) && $sc_settings['donations_on_checkout'] ? 'checked="checked"' : ''); ?>/>
	<label for="donations_on_checkout"><?php _e('Collect Donations on Checkout', 'memberdeck'); ?></label>
</div>
<div class="form-input text">
	<label for="donations_on_checkout_label"><?php _e('Checkout label', 'memberdeck'); ?></label>
	<input type="text" name="fee_mods_donations_on_checkout_label" id="donations_on_checkout_label" class="text" value="<?php echo (isset($sc_settings['donations_on_checkout_label']) ? $sc_settings['donations_on_checkout_label'] : ''); ?>"/>
</div>
<div class="form-input textarea">
	<label for="donations_on_checkout_text"><?php _e('Checkout text', 'memberdeck'); ?></label>
	<textarea name="fee_mods_donations_on_checkout_text" id="donations_on_checkout_text"><?php echo (isset($sc_settings['donations_on_checkout_text']) ? $sc_settings['donations_on_checkout_text'] : ''); ?></textarea>
</div>
<div class="form-check checkbox inline">
	<input type="checkbox" name="fee_mods_cover_fees_on_checkout" id="cover_fees_on_checkout" class="checkbox" value="1" <?php echo (isset($sc_settings['cover_fees_on_checkout']) && $sc_settings['cover_fees_on_checkout'] ? 'checked="checked"' : ''); ?>/>
	<label for="cover_fees_on_checkout"><?php _e('Allow Donors to Cover Fees', 'memberdeck'); ?></label>
</div>
<div class="form-input text">
	<label for="cover_fees_on_checkout_label"><?php _e('Cover creator fees label', 'memberdeck'); ?></label>
	<input type="text" name="fee_mods_cover_fees_on_checkout_label" id="cover_fees_on_checkout_label" class="text" value="<?php echo (isset($sc_settings['cover_fees_on_checkout_label']) ? $sc_settings['cover_fees_on_checkout_label'] : ''); ?>"/>
</div>
<div class="form-input textarea">
	<label for="cover_fees_on_checkout_text"><?php _e('Cover creator fees text', 'memberdeck'); ?></label>
	<textarea name="fee_mods_cover_fees_on_checkout_text" id="cover_fees_on_checkout_text"><?php echo (isset($sc_settings['cover_fees_on_checkout_text']) ? $sc_settings['cover_fees_on_checkout_text'] : ''); ?></textarea>
</div>