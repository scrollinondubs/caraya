<div class="wrap">
	<div class="icon32" id="icon-options-general"></div><h2><?php _e('Profile Donations Settings', 'memberdeck'); ?></h2>
	<div class="postbox-container" style="width:95%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<form method="POST" action="" id="profile_donations_settings" name="profile_donations_settings">
						<div class="inside">
							<div class="form-row half wp-media-buttons ignitiondeck">
								<label for="profile_donations_image_selector"><?php _e('Lightbox Image', 'idsocial'); ?></label><br/>
								<button name="profile_donations_image_selector" id="profile_donations_image_selector" class="button insert-media add_media" data-input="profile_donations_image"><?php _e('Select Image', 'memberdeck'); ?></button>
								<input type="hidden" id="profile_donations_image" name="profile_donations_image" class="main-setting" value="<?php echo (!empty($settings['profile_donations_image']) ? $settings['profile_donations_image'] : ''); ?>" />
							</div>
							<div class="form-row profile-donations-website-image">
								<br/>
								<img src="<?php echo ((!empty($settings['profile_donations_image'])) ? $settings['profile_donations_image_url'] : ''); ?>" style="max-width:15%; <?php echo (!empty($settings['profile_donations_image']) ? '' : 'display:none;') ?>" />
							</div>
							<div class="form-row">
								<label for="profile_donations_product"><?php _e('Standard Donation Product', 'memberdeck'); ?></label><br/>
								<select name="standard_donation_product" id="standard_donation_product">
									<option value="0"><?php _e('None/Disabled', 'memberdeck'); ?></option>
									<?php foreach ($idc_products as $product) {
										echo '<option value="'.$product->id.'" '.(isset($settings['standard_donation_product']) && $product->id == $settings['standard_donation_product'] ? 'selected="selected"' : '').'>'.$product->level_name.'</option>';
									} ?>
								</select>
							</div>
							<div class="form-row">
								<label for="profile_donations_product"><?php _e('Weekly Donation Product', 'memberdeck'); ?></label><br/>
								<select name="weekly_donation_product" id="weekly_donation_product">
									<option value="0"><?php _e('None/Disabled', 'memberdeck'); ?></option>
									<?php foreach ($idc_products as $product) {
										echo '<option value="'.$product->id.'" '.(isset($settings['weekly_donation_product']) && $product->id == $settings['weekly_donation_product'] ? 'selected="selected"' : '').'>'.$product->level_name.'</option>';
									} ?>
								</select>
							</div>
							<div class="form-row">
								<label for="monthly_donation_product"><?php _e('Monthly Donation Product', 'memberdeck'); ?></label><br/>
								<select name="monthly_donation_product" id="monthly_donation_product">
									<option value="0"><?php _e('None/Disabled', 'memberdeck'); ?></option>
									<?php foreach ($idc_products as $product) {
										echo '<option value="'.$product->id.'" '.(isset($settings['monthly_donation_product']) && $product->id == $settings['monthly_donation_product'] ? 'selected="selected"' : '').'>'.$product->level_name.'</option>';
									} ?>
								</select>
							</div>
							<div class="form-row">
								<label for="annual_donation_product"><?php _e('Annual Donation Product', 'memberdeck'); ?></label><br/>
								<select name="annual_donation_product" id="annual_donation_product">
									<option value="0"><?php _e('None/Disabled', 'memberdeck'); ?></option>
									<?php foreach ($idc_products as $product) {
										echo '<option value="'.$product->id.'" '.(isset($settings['annual_donation_product']) && $product->id == $settings['annual_donation_product'] ? 'selected="selected"' : '').'>'.$product->level_name.'</option>';
									} ?>
								</select>
							</div>
							<div class="submit">
								<input type="submit" name="save_profile_donations_settings" id="save_profile_donations_settings" class="button" />
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).on('idfMediaSelected', function(e, attachment) {
   		jQuery('.profile-donations-website-image-website-image').children('img').attr('src', attachment.url).show();
   	});
</script>