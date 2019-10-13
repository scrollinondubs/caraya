<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php _e('Paytm Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:95%; margin-right:5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Merchant Settings', 'memberdeck'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<form action="" method="POST" id="id_square_admin_settings">
								<p><?php printf(__('Please note that this gateway uses test mode settings found in the %1$sGateways menu%2$s', 'memberdeck'), '<a href="'.menu_page_url('idc-gateways', 0).'">', '</a>'); ?>.</p>
								<h3><?php _e('Staging Details', 'memberdeck'); ?></h3>
								<div class="form-input">
									<label for="paytm_staging_merchant_key"><?php _e('Paytm Staging Merchant Key', 'memberdeck'); ?></label>
									<input type="text" name="paytm_staging_merchant_key" id="paytm_staging_merchant_key" value="<?php echo (isset($settings['paytm_staging_merchant_key']) ? $settings['paytm_staging_merchant_key'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_staging_merchant_mid"><?php _e('Paytm Staging Merchant MID', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_staging_merchant_mid" id="paytm_staging_merchant_mid" value="<?php echo (isset($settings['paytm_staging_merchant_mid']) ? $settings['paytm_staging_merchant_mid'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_staging_merchant_website"><?php _e('Paytm Staging Merchant Website', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_staging_merchant_website" id="paytm_staging_merchant_website" value="<?php echo (isset($settings['paytm_staging_merchant_website']) ? $settings['paytm_staging_merchant_website'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_staging_industry_type"><?php _e('Paytm Staging Industry Type', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_staging_industry_type" id="paytm_staging_industry_type" value="<?php echo (isset($settings['paytm_staging_industry_type']) ? $settings['paytm_staging_industry_type'] : ''); ?>"/>
								</div>
								<h3><?php _e('Production Details', 'memberdeck'); ?></h3>
								<div class="form-input">
									<label for="paytm_merchant_key"><?php _e('Paytm Merchant Key', 'memberdeck'); ?></label>
									<input type="text" name="paytm_merchant_key" id="paytm_merchant_key" value="<?php echo (isset($settings['paytm_merchant_key']) ? $settings['paytm_merchant_key'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_merchant_mid"><?php _e('Paytm Merchant MID', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_merchant_mid" id="paytm_merchant_mid" value="<?php echo (isset($settings['paytm_merchant_mid']) ? $settings['paytm_merchant_mid'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_merchant_website"><?php _e('Paytm Merchant Website', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_merchant_website" id="paytm_merchant_website" value="<?php echo (isset($settings['paytm_merchant_website']) ? $settings['paytm_merchant_website'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="paytm_industry_type"><?php _e('Paytm Industry Type', 'memberdeck'); ?></label><br/>
									<input type="text" name="paytm_industry_type" id="paytm_industry_type" value="<?php echo (isset($settings['paytm_industry_type']) ? $settings['paytm_industry_type'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<button class="button button-primary" id="id_paytm_settings_submit" name="id_paytm_settings_submit"><?php _e('Save', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>