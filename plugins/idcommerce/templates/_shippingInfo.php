<h2 class="border-bottom"><?php _e('Shipping Address', 'memberdeck'); ?></h2>
<p class="desc-note"><?php _e('Please provide an accurate mailing address.', 'memberdeck'); ?>
</p>
<div class="form-row full">
	<label for="address"><?php _e('Address', 'memberdeck'); ?></label>
	<input type="text" size="20" class="address" name="address" value="<?php echo (isset($shipping_info['address']) ? $shipping_info['address'] : ''); ?>"/>
</div>
<div class="form-row full">
	<label for="address_two"><?php _e('Address Line 2', 'memberdeck'); ?></label>
	<input type="text" size="20" class="address_two" name="address_two" value="<?php echo (isset($shipping_info['address_two']) ? $shipping_info['address_two'] : ''); ?>"/>
</div>
<div class="form-row half left">
	<label for="city"><?php _e('City', 'memberdeck'); ?></label>
	<input type="text" size="20" class="city" name="city" value="<?php echo (isset($shipping_info['city']) ? $shipping_info['city'] : ''); ?>"/>
</div>
<div class="form-row half">
	<label for="state"><?php _e('State', 'memberdeck'); ?></label>
	<input type="text" size="20" class="state" name="state" value="<?php echo (isset($shipping_info['state']) ? $shipping_info['state'] : ''); ?>"/>
</div>
<div class="form-row half left">
	<label for="zip"><?php _e('Postal Code', 'memberdeck'); ?></label>
	<input type="text" size="20" class="zip" name="zip" value="<?php echo (isset($shipping_info['zip']) ? $shipping_info['zip']: ''); ?>"/>
</div>
<div class="form-row half">
	<label for="country"><?php _e('Country', 'memberdeck'); ?></label>
	<?php if (!empty($countries)) { ?>
		<select name="country" class="country select">
			<?php foreach ($countries as $country) {
				echo '<option value="'.$country->code.'" '.(isset($shipping_info['country']) && $shipping_info['country'] == $country->code ? 'selected="selected"' : '').'>'.$country->name.'</option>';
			} ?>
		</select>
	<?php } else { ?>
	<input type="text" size="20" class="country" name="country" value="<?php echo (isset($shipping_info['country']) ? $shipping_info['country'] : ''); ?>"/>
	<?php } ?>
</div>