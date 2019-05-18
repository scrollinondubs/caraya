<?php
/**
 * Campaign details.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign, $wp_embed;
global $post;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
if ($project_id > 0) {
	$project = new ID_Project($project_id);
	$deck = new Deck($project_id);
	$hDeck = $deck->hDeck();
	//$the_project = $project->the_project();
	$video = get_post_meta($post_id, 'ign_product_video', true);
	//$end_type = get_post_meta($post_id, 'ign_end_type', true);
	$closed = $project->project_closed();
	//$ccode = $project->currency_code();
}
$prefix = idf_get_querystring_prefix(); 
?>

<article class="project-details">
	<div class="image">
		<?php if ( !empty($video) ) : ?>
			<div class="video-container">
				<?php echo idf_handle_video($video); ?>
			</div>
		<?php else : ?>
			<?php 
				echo '<img src="'.ID_Project::get_project_thumbnail($post_id, 'blog').'"/>';
			?>
		<?php endif; ?>
	</div>
	<div class="right-side">
		<ul class="campaign-stats">
			<li class="progress">
				<h3><?php echo $hDeck->total; ?></h3>
				<p><?php printf( __( 'Pledged of %s Goal', 'fundify' ), $hDeck->goal ); ?></p>

				<div class="bar"><span style="width: <?php echo $hDeck->percentage; ?>%"></span></div>
			</li>

			<li class="backer-count">
				<h3><?php echo $hDeck->pledges; ?></h3>
				<p><?php echo _n( 'Backer', 'Backers', $hDeck->pledges, 'fundify' ); ?></p>
			</li>
			<?php if ( $hDeck->end_type == 'closed' ) : ?>
			<li class="days-remaining">
				<?php if ( $hDeck->days_left >= 0 ) : ?>
					<h3><?php echo $hDeck->days_left; ?></h3>
					<p><?php echo _n( 'Day to Go', 'Days to Go', $hDeck->days_left, 'fundify' ); ?></p>
				<?php else : ?>
					<!--<h3><?php echo '00:00' /*$campaign->hours_remaining()*/; ?></h3>-->
					<p><?php _e('Ends at midnight', 'fundify'); ?></p>
				<?php endif; ?>
			</li>
			<?php endif; ?>
		</ul>

		<div class="contribute-now ign-supportnow" data-projectid="<?php echo $project_id; ?>">
			<?php if ( !$closed ) : ?>
				<a href="<?php the_permalink().$prefix; ?>purchaseform=fundify&amp;prodid=<?php echo (isset($project_id) ? $project_id : ''); ?>" class="btn-green contribute"><?php _e( 'Contribute Now', 'fundify' ); ?></a>
			<?php else : ?>
				<a class="btn-green expired"><?php _e('Project Closed', 'fundify'); ?></a>
			<?php endif; ?>
		</div>

		<?php
			if ( $hDeck->end_type == 'closed' ) :
				$end_date = date_i18n( get_option( 'date_format' ), strtotime( $hDeck->end ) );
		?>
		
		<p class="fund">
			<?php //if ( 'fixed' == $campaign->type() ) : ?>
			<?php //printf( __( 'This %3$s will only be funded if at least %1$s is pledged by %2$s.', 'fundify' ), $campaign->goal(), $end_date, strtolower( edd_get_label_singular() ) ); ?>
			<?php //elseif ( 'flexible' == $campaign->type() ) : ?>
			<?php //printf( __( 'All funds will be collected on %1$s.', 'fundify' ), $end_date ); ?>
			<?php //else : ?>
			<?php //printf( __( 'All pledges will be collected automatically until %1$s.', 'fundify' ), $end_date ); ?>
			<?php //endif; ?>
		</p>
		<?php endif; ?>
	</div>
</article>