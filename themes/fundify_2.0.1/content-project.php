<?php
/**
 * @package Fundify
 * @since Fundify 1.0
 */

global $post;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
if ($project_id > 0) {
	$project = new ID_Project($project_id);
	/*
	$end_type = get_post_meta($post_id, 'ign_end_type', true);
	$days_left = $project->days_left();*/
	$closed = $project->project_closed();
	$deck = new Deck($project_id);
	$mini_deck = $deck->mini_deck();
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'item' ); ?>>
	<?php if ( $mini_deck->successful && ( $closed || 'open' == $mini_deck->end_type ) ) : ?>
	<div class="campaign-ribbon success">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Successful', 'fundify' ); ?></a>
	</div>
	<?php elseif ( $closed && ! $mini_deck->successful) : ?>
	<div class="campaign-ribbon unsuccess">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Unsuccessful', 'fundify' ); ?></a>
	</div>
	<?php elseif ( $mini_deck->days_left < 5 && $mini_deck->end_type == 'closed' && !$closed) : ?>
	<div class="campaign-ribbon">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Ending Soon', 'fundify' ); ?></a>
	</div>
	<?php elseif ( has_term('Featured', 'project_type') ) : ?>
	<div class="campaign-ribbon featured">
		<a href="<?php the_permalink(); ?>"><?php _e( 'Staff Pick', 'fundify' ); ?></a>
	</div>
	<?php endif; ?>

	<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
		<?php echo '<img src="'.ID_Project::get_project_thumbnail($post_id, 'campaign').'"/>'; ?>
	</a>

	<h3 class="entry-title">
		<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); ?></a>
         <div class="project-tag">
			<?php
                $terms = wp_get_post_terms( $post->ID, 'project_category');
                if(!empty($terms)) {
                $site_url = home_url();
                $cat_name = "";
                 foreach($terms as $term){
                    if($term->count > 0){
                        $cat_name .= $term->name;
                        break;
                    }
                 }
                if($term->count > 0){ echo $cat_name; }
                }
             ?>
         </div>
	</h3>
   	<p><?php echo strip_tags(html_entity_decode(get_post_meta($post->ID, 'ign_project_description', true))); ?></p>

	<div class="digits">
		<div class="bar"><span style="width: <?php echo $mini_deck->rating_per; ?>%"></span></div>
		<ul>
			<li><?php printf( '<strong>%s</strong> '.__( 'Funded', 'fundify' ), $mini_deck->rating_per.'%' ); ?></li>
			<li><?php printf( '<strong>%s</strong> '._x( 'Funded', 'Amount funded in single campaign stats', 'fundify' ), $mini_deck->p_current_sale ); ?></li>
			<?php if ( $mini_deck->end_type == 'closed' ) : ?>
			<li>
				<?php if ( $mini_deck->days_left >= 0 && !$closed ) : ?>
					<?php printf( '<strong>%s</strong> '.__( 'Days to Go', 'fundify' ), $mini_deck->days_left ); ?>
				<?php elseif ($closed) : ?>
					<?php printf( '<strong>'.__( 'Ended On', 'fundify').'</strong> %s', $mini_deck->item_fund_end ); ?>
				<?php endif; ?>
			</li>
			<?php endif; ?>
		</ul>
	</div>
</article>