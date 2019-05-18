<div class="wrap ignitiondeck">
	<div class="icon32" id=""></div><h2 class="title"><?php _e('Secupay Settings', 'memberdeck'); ?></h2>
	<?php if (isset($_POST['submit_secupay_settings'])) { ?>
		<div id="message" class="updated"><?php _e('Settings Saved', 'memberdeck'); ?></div>
	<?php } ?>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large button-primary"><?php _e('Support', 'memberdeck'); ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large button-primary"><?php _e('Documentation', 'memberdeck'); ?></button></a>
	</div>
	<div class="id-settings-container">
		<div class="postbox-container" style="width:95%; margin-right:5%">
			<div class="metabox-holder">
				<div class="meta-box-sortables" style="min-height:0;">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e('Secupay Credentials', 'memberdeck'); ?></span></h3>
						<div class="inside" style="width: 50%; min-width: 400px;">
							<?php echo $output; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>