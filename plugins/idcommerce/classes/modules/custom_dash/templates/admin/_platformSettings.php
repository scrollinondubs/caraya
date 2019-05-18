<h4><?php _e('Dashboard Menu', 'memberdeck'); ?></h4>
<div class="form-input">
	<input type="checkbox" name="disable_backer_profile" id="disable_backer_profile" value="1" <?php echo (!empty($enterprise_settings['disable_backer_profile']) && $enterprise_settings['disable_backer_profile'] ? 'checked="checked"' : ''); ?>/>
	<label for="disable_backer_profile"><?php _e('Disable Backer Profile', 'memberdeck'); ?></label>
</div>
<div class="form-input">
	<input type="checkbox" name="disable_creator_profile" id="disable_creator_profile" value="1" <?php echo (!empty($enterprise_settings['disable_creator_profile']) && $enterprise_settings['disable_creator_profile'] ? 'checked="checked"' : ''); ?>/>
	<label for="disable_creator_profile"><?php _e('Disable Creator Profile', 'memberdeck'); ?></label>
</div>
<div class="form-input">
	<input type="checkbox" name="disable_creator_settings" id="disable_creator_settings" value="1" <?php echo (!empty($enterprise_settings['disable_creator_settings']) && $enterprise_settings['disable_creator_settings'] ? 'checked="checked"' : ''); ?>/>
	<label for="disable_creator_settings"><?php _e('Disable Creator Settings', 'memberdeck'); ?></label>
</div>