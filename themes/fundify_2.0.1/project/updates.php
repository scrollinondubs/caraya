<?php
/**
 * Campaign updates.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $post;
$project_id = get_post_meta($post->ID, 'ign_project_id', true);
?>

<?php if ( $project_id > 0 ) : ?>
	<div id="updates">
		<!--<h3 class="campaign-updates-title sans"><?php _e( 'Updates', 'fundify' ); ?></h3>-->
			<?php echo do_shortcode('[project_updates product="'.$project_id.'"]') ?>
	</div>
<?php endif; ?>