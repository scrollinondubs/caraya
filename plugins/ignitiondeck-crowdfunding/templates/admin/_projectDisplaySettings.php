<?php do_action('ide_above_project_management'); ?>
<div class="form-row">
	<h4><?php _e('Project Display', 'memberdeck'); ?></h4>
	<p><?php _e('Automatically hide unsuccessful projects from the project grid, including category and archive pages', 'memberdeck'); ?>.</p>
</div>
<div class="form-input form-row checkbox inline">
	<input type="checkbox" name="hide_failed" value="1" <?php echo (isset($enterprise_settings['hide_failed']) && $enterprise_settings['hide_failed'] ? 'checked="checked"' : ''); ?>/>
	<label for="assign_user"><?php _e('Hide Failed Projects', 'memberdeck'); ?></label>
</div>
<?php do_action('ide_below_project_management'); ?>