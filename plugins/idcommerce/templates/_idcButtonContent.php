<div class="ignitiondeck idc_lightbox idc_button_lightbox mfp-hide">
	<div class="project_image" style="background-image: url('<?php echo apply_filters('idc_lightbox_image', (isset($args['thumb']) ? $args['thumb'] : '')) ?>');">
    	<div class="aspect_ratio_maker"></div>
    </div>
    <div class="lb_wrapper">
		<div class="form_header">
			<strong><?php _e('Step 1:', 'memberdeck'); ?></strong> <?php _e('Choose Your Contribution Level', 'memberdeck'); ?>
		</div>
		<div class="form">
			<form action="" method="POST" name="idc_button_checkout_form">
				<div class="form-row inline left twothird <?php echo (is_array($level) ? '' : 'total'); ?>">
					<?php if (is_array($level)) { ?>
						<label for="level_select"><?php _e('Select Amount', 'memberdeck'); ?>
							<span class="idc-dropdown">
								<select name="level_select" id="level_select" class="idc-dropdown__select level_select">
								<?php foreach ($level as $k=>$v) { ?>
									<option value="<?php echo $v->id; ?>" data-price="<?php echo apply_filters('idc_price_format', $v->level_price); ?>"><?php echo $v->level_name; ?></option>
								<?php } ?>
								</select>
							</span>
						</label>
					<?php } else { ?>
						<label for="price"><?php _e('Total', 'memberdeck'); ?></label>
						<input type="text" class="total" name="price" id="price" value="<?php echo apply_filters('idc_price_format', $level->level_price); ?>"/>
						<span class="idc-button-default-price hide" data-level-price="<?php echo apply_filters('idc_price_format', $level->level_price); ?>"></span>
					<?php } ?>
				</div>
				<?php if (is_array($level)) { ?>
					<div class="form-row inline third total">
					<label for="total"><?php _e('Total', 'ignitiondeck'); ?></label>
						<input type="text" class="total" name="total" id="total" value="" placeholder="<?php echo apply_filters('idc_price_format', (is_array($level) ? $level[0]->level_price : $level->level_price)); ?>" />
					</div>
				<?php } ?>
				<div class="button-error-placeholder">
					<span class="payment-errors" style="display:none;"><?php _e('Input price is below minimum', 'memberdeck') ?></span>
				</div>
				<div class="form-hidden">
					<input type="hidden" name="product_id" value="<?php echo (is_array($product_id) ? $product_id[0] : $product_id); ?>"/>
				</div>
				<div class="form-row submit">
					<input type="submit" name="idc_button_submit" class="btn idc_button_submit" value="<?php _e('Next Step', 'memberdeck'); ?>"/>
				</div>
			</form>
		</div>
	</div>
</div>