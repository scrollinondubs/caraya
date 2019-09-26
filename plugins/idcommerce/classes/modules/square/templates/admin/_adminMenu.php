<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php _e('Square Settings', 'memberdeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:95%; margin-right:5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Gateway Settings', 'memberdeck'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<form action="" method="POST" id="id_square_admin_settings">
								<p><?php printf(__('Please note that this gateway uses test mode settings found in the %1$sGateways menu%2$s', 'memberdeck'), '<a href="'.menu_page_url('idc-gateways', 0).'">', '</a>'); ?>.</p>
								<h4><?php _e('Location Details', 'memberdeck'); ?></h4>
								<div class="form-input">
									<label for="location_id"><?php _e('Location ID', 'memberdeck'); ?></label>
									<input type="text" name="location_id" id="location_id" value="<?php echo (isset($settings['location_id']) ? $settings['location_id'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="currency"><?php _e('Location Currency', 'memberdeck'); ?></label><br/>
									<select class="idc_dropdown" name="currency" id="currency" data-selected="<?php echo (!empty($settings['currency']) ? $settings['currency'] : 'USD'); ?>"/>
									</select>
								</div>
								<h4><?php _e('Live Keys', 'memberdeck'); ?></h4>
								<div class="form-input">
									<label for="application_id"><?php _e('Application ID', 'memberdeck'); ?></label>
									<input type="text" name="application_id" id="application_id" value="<?php echo (isset($settings['application_id']) ? $settings['application_id'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="access_token"><?php _e('Personal Access Token', 'memberdeck'); ?></label>
									<input type="text" name="access_token" id="access_token" value="<?php echo (isset($settings['access_token']) ? $settings['access_token'] : ''); ?>"/>
								</div>
								<h4><?php _e('Sandbox Keys', 'memberdeck'); ?></h4>
								<div class="form-input">
									<label for="sandbox_application_id"><?php _e('Sandbox Application ID', 'memberdeck'); ?></label>
									<input type="text" name="sandbox_application_id" id="sandbox_application_id" value="<?php echo (isset($settings['sandbox_application_id']) ? $settings['sandbox_application_id'] : ''); ?>"/>
								</div>
								<div class="form-input">
									<label for="sandbox_access_token"><?php _e('Sandbox Access Token', 'memberdeck'); ?></label>
									<input type="text" name="sandbox_access_token" id="sandbox_access_token" value="<?php echo (isset($settings['sandbox_access_token']) ? $settings['sandbox_access_token'] : ''); ?>"/>
								</div>
								<p><?php printf(__('Register and retrieve application keys %1$shere%2$s', 'memberdeck'), '<a href="https://connect.squareup.com/apps/">', '</a>'); ?></p>
								<div class="form-input">
									<button class="button button-primary" id="id_square_settings_submit" name="id_square_settings_submit"><?php _e('Save', 'memberdeck'); ?></button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>