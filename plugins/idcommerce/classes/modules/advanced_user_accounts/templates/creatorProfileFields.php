<div class="form-row third left wp-media-buttons">
	<label for="profile_hero"><?php _e('Hero Image', 'memberdeck'); ?></label>
	<button type="button" name="profile_hero_selector" id="profile_hero_selector" class="button insert-media add_media" data-input="profile_hero"><?php _e('Select Image', 'memberdeck'); ?></button>
	<input type="hidden" id="profile_hero" name="profile_hero" class="main-setting" value="<?php echo (!empty($profile_hero) ? $profile_hero : ''); ?>" />
</div>
<div class="form-row wp_media_image profile_hero_image twothird">
	<img src="<?php echo ((!empty($profile_hero_data)) ? $profile_hero_url : ''); ?>" style="max-width:100%; <?php echo (!empty($profile_hero_data) ? '' : 'display:none;') ?>" />
</div>
<div class="form-row full">
	<label for="creator_tagline"><?php _e('Creator Tagline', 'memberdeck'); ?></label>
	<input type="text" size="20" class="creator_tagline" name="creator_tagline" value="<?php echo (!empty($creator_tagline) ? $creator_tagline : ''); ?>"/>
</div>
<script>
	jQuery(document).on('idfMediaSelected', function(e, attachment) {
		if (jQuery('.profile_hero_image').data('active') === 1) {
			jQuery('.profile_hero_image').data('active', 0);
   			jQuery('.profile_hero_image').children('img').attr('src', attachment.url).show();
   		}
   	});
</script>