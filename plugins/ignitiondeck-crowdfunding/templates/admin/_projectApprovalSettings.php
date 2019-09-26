<?php do_action('ide_above_project_approval'); ?>
<div class="form-row">
	<h4><?php _e('Project Approval', 'memberdeck'); ?></h4>
	<p><?php _e('Allows creators to post projects without admin approval', 'memberdeck'); ?>.</p>
</div>
<div class="form-input form-row checkbox inline">
	<input type="checkbox" name="auto_approve" value="1" <?php echo (isset($enterprise_settings['auto_approve']) && $enterprise_settings['auto_approve'] ? 'checked="checked"' : ''); ?>/>
	<label for="assign_user"><?php _e('Automatically Approve Projects', 'memberdeck'); ?></label>
</div>
<?php do_action('ide_below_project_approval'); ?>