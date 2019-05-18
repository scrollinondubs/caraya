<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php _e('reCAPTCHA Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:95%; margin-right:5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('API Keys', 'memberdeck'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<form action="" method="POST" id="id_recaptcha_settings">
								<div class="form-input">
									<label for="id_recaptcha_site_id"><?php _e('Site ID', 'memberdeck'); ?></label>
									<input type="text" name="id_recaptcha_site_id" id="id_recaptcha_site_id" value="<?php echo (isset($settings['id_recaptcha_site_id']) ? $settings['id_recaptcha_site_id'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="id_recaptcha_secret_key"><?php _e('Secret Key', 'memberdeck'); ?></label>
									<input type="text" name="id_recaptcha_secret_key" id="id_recaptcha_secret_key" value="<?php echo (isset($settings['id_recaptcha_secret_key']) ? $settings['id_recaptcha_secret_key'] : ''); ?>"/>
								</div>
								<p><a href="https://www.google.com/recaptcha/admin#list" target="_blank"><?php _e('Generate API Keys', 'idf'); ?></a></p>
								<div class="form-row">
									<button class="button button-primary" id="submit_id_recaptcha_settings" name="submit_id_recaptcha_settings"><?php _e('Save', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>