<div class="ignitiondeck id-widget id-mini <?php echo (($mini_deck->successful) ? 'success' : ''); ?> <?php echo $mini_deck->post_id; ?>" data-projectid="<?php echo (isset($project_id)? $project_id : ''); ?>">
	<div class="id-product-infobox">
		<div class="product-wrapper">
			<?php echo do_action('id_widget_before', $project_id, $mini_deck); ?>
			<?php echo do_action('id_mini_widget_before', $project_id); ?>
			<div class="pledge">
				<?php $mini_image = ID_Project::get_project_thumbnail($mini_deck->post_id, 'id_project_thumb'); ?>
				<?php  if (!$custom || ($custom && isset($attrs['project_title']))) { ?>
					<h2 class="id-product-title"><a href="<?php echo getProjectURLfromType($project_id); ?>"><?php echo stripslashes(get_the_title($mini_deck->post_id)); ?></a></h2>
				<?php } ?>
				<?php  if (!$custom || ($custom && isset($attrs['project_image']))) { ?>
					<?php if (!empty($mini_image)) { ?>
						<div class="img_cur"><a href="<?php echo getProjectURLfromType($project_id); ?>"><img src="<?php echo $mini_image; ?>" /></a></div>
					<?php } ?>
				<?php } ?>
				<?php  if (!$custom || ($custom && isset($attrs['project_bar']))) { ?>
					<div class="progress-wrapper">
						<div class="progress-percentage"><?php echo $mini_deck->rating_per; ?>% </div>					
						<!-- end progress-percentage -->
						<div class="progress-bar" style="width: <?php echo $mini_deck->rating_per; ?>%"> 
							<!----> 
						</div>
						<!-- end progress bar --> 
					</div>
					<!-- end progress wrapper --> 
				<?php } ?>
			</div>
			
			<!-- end pledge -->
			
			<div class="clearing"><!----></div>
			<div class="id-product-funding"></div>
			<?php  if (!$custom || ($custom && isset($attrs['project_pledged']))) { ?>
				<div class="id-progress-raised"> <?php echo $mini_deck->p_current_sale; ?> </div>
			<?php } ?>
			<?php  if (!$custom || ($custom && isset($attrs['project_goal']))) { ?>
				<div class="id-product-funding"><?php echo __('Pledged of', 'ignitiondeck').' '.$mini_deck->item_fund_goal.' '.__('Goal', 'ignitiondeck'); ?> </div>
			<?php } ?>
			<?php  if (!$custom || ($custom && isset($attrs['project_pledgers']))) { ?>
				<div class="id-product-total"><?php echo $mini_deck->p_count->p_number; ?></div>
				<div class="id-product-pledges"><?php _e('Pledgers', 'ignitiondeck'); ?></div>
			<?php } ?>
			<?php  if (!$custom || ($custom && isset($attrs['days_left']))) { ?>
				<?php if (isset($mini_deck->days_left) && $mini_deck->days_left > 0) { ?>
					<div class="id-product-days"><?php echo (($mini_deck->days_left !== "" || $mini_deck->days_left !== 0) ? $mini_deck->days_left : '0'); ?></div>
					<div class="id-product-days-to-go"><?php _e('Days Left', 'ignitiondeck'); ?></div>
				<?php } ?>
			<?php } ?>
		</div>
		<?php  if (!$custom || ($custom && isset($attrs['project_end']))) { ?>
			<!-- end product-wrapper -->	
			<?php if ($mini_deck->item_fund_end !== '') { ?>	
			<div class="id-product-proposed-end"><?php _e('Crowdfunding ends on', 'ignitiondeck'); ?>
				<div class="id-widget-date">
					<div class="id-widget-month"><?php echo $mini_deck->month; ?></div>
					<div class="id-widget-day"><?php echo $mini_deck->day; ?></div>
					<div class="id-widget-year"><?php echo $mini_deck->year; ?></div>
				</div>
			</div>
			<?php } ?>
		<?php } ?>
		<div class="separator">&nbsp;</div>
		<?php  if (!$custom || ($custom && isset($attrs['project_button']))) { ?>
			<div class="btn-container"><a href="<?php echo getProjectURLfromType($project_id); ?>" class="learn-more-button"><?php _e('Learn More', 'ignitiondeck'); ?></a></div>
		<?php } ?>
		<?php
		if ($mini_deck->settings->id_widget_logo_on) {
			?>
			<div class="poweredbyID"><span><a href="<?php echo $mini_deck->affiliate_link; ?>" title="<?php _e('Crowdfunding', 'ignitiondeck'); ?>"><?php _e('Powered By IgnitionDeck','ignitiondeck');; ?></a></span></div>
			<?php
		}
		?>
		<?php echo do_action('id_widget_after', $project_id, $mini_deck); ?>
		<?php echo do_action('id_mini_widget_after', $project_id); ?>
	</div>
	<!-- end product-infobox -->
</div>
<!-- end mini-widget -->