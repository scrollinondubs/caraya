<?php
/**
 * The Template for displaying all single projects.
 *
 * @package Fundify
 * @since Fundify 1.0
 */

global $campaign;
global $post;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
if ($project_id > 0) {
	$project = new ID_Project($project_id);
	$the_project = $project->the_project();
	$days_left = $project->days_left();
	$end_type = get_post_meta($post_id, 'ign_project_type', true);
	$closed = $project->project_closed();
	$deck = new Deck($project_id);
	$the_deck = $deck->the_deck();
	if (!empty($the_deck)) {
		$levels = $the_deck->level_data;
	}
	$long_desc = html_entity_decode(get_post_meta($post_id, 'ign_project_long_description', true));
	$permalink_structure = get_option('permalink_structure');
	if (empty($permalink_structure)) {
		$url_suffix = '&';
	}
	else {
		$url_suffix = '?';
	}
	$url = get_permalink($post_id).$url_suffix.'purchaseform=1&prodid='.$project_id;
}

get_header(); ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php locate_template( array( 'project/title.php' ), true ); ?>

		<div id="content" class="post-details">
			<div class="container">
				<?php 
				if (isset($_GET['purchaseform']) && $_GET['purchaseform'] == 'fundify') {
					locate_template( array( 'project/project-purchase-form.php' ), true);
				}
				else { 
					do_action( 'atcf_campaign_before', array('ID' => $post_id) ); ?>

					<?php locate_template( array( 'searchform-project.php' ), true ); ?>
					<?php locate_template( array( 'project/project-sort-tabs.php' ), true ); ?>

					<?php locate_template( array( 'project/project-details.php' ), true ); ?>

					<aside id="sidebar">
						<?php if (is_id_pro()) {
							locate_template( array( 'project/author-info.php' ), true );
						} ?>

						<div id="contribute-now" class="single-reward-levels">
							<?php
								if ( !$closed ) { ?>
									<div class="edd_price_options active">
										<ul>
											<?php if (!empty($levels)) { ?>
												<?php foreach ($levels as $level) { ?>
												<?php
												$level_invalid = getLevelLimitReached($project_id, $post_id, $level->id);
												if (!function_exists('is_id_licensed') || !is_id_licensed()) {
													$level_invalid = 1;
												}
												?>
												<?php echo '<a class="level-binding" '.(isset($level_invalid) && $level_invalid ? '' : 'href="'.apply_filters('id_level_'.$level->id.'_link', $url.'&level='.$level->id, $project_id).'"').'>'; ?>
												<li class="atcf-price-option <?php echo ((isset($level_invalid) && $level_invalid) ? 'inactive' : ''); ?>">
													<div class="clear">
														<h3>
															<label><span class="pledge-verb"><?php _e('Pledge', 'fundify'); ?></span> <?php echo (!empty($level->meta_price) ? apply_filters('id_price_selection', $level->meta_price, $the_deck->post_id) : ''); ?></label>
														</h3>
														<div class="backers">
															<div class="backer-count">
																<i class="icon-user"></i> <?php echo $level->meta_count; ?> <?php _e('Backers', 'fundify'); ?>
															</div>
														</div>
														<p><?php echo $level->meta_short_desc; ?></p>
													</div>
												</li>
												<?php echo '</a>'; ?>
												<?php } ?>
											<?php } ?>
										</ul>
										<?php echo do_action('id_widget_after', $project_id, $the_deck); ?>
									</div>
								<?php }
							?>
						</div>
					</aside>

					<div id="main-content">

						<?php locate_template( array( 'project/meta.php' ), true ); ?>
						<?php locate_template( array( 'project/share.php' ), true ); ?>

						<div class="entry-content inner campaign-tabs">
							<div id="description">
								<?php echo apply_filters('fh_project_content', $long_desc, $project_id); ?>
							</div>

							<?php locate_template( array( 'project/updates.php' ), true ); ?>

							<?php comments_template(); ?>

							<?php locate_template( array( 'project/backers.php' ), true ); ?>

							<?php locate_template( array( 'project/faqs.php' ), true); ?>

							<?php locate_template( array( 'project/updates.php' ), true); ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>

	<?php endwhile; ?>

<?php get_footer(); ?>