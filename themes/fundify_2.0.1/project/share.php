<?php
/**
 * Campaign sharing.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $post;

if (class_exists('ID_Project')) {
	$idsocial_settings = maybe_unserialize(get_option('idsocial_settings'));
	if (!empty($idsocial_settings)) {
		$app_id = $idsocial_settings['app_id'];
		if (!empty($idsocial_settings['social_checks'])) {
			$social_settings = $idsocial_settings['social_checks'];
		}
		else {
			$social_settings = array();
		}
		$settings = (object) $social_settings;
	}
}

$message = apply_filters( 'fundify_share_message', sprintf( __( 'Check out %s on %s! %s', 'fundify' ), $post->post_title, get_bloginfo( 'name' ), get_permalink() ) );
?>

<div class="entry-share">
	<?php _e( 'Share this campaign', 'fundify' ); ?>
	
	<?php do_action('idf_general_social_buttons', $post->ID, $message) ?>

	<div id="share-widget" class="fundify-modal">
		<?php get_template_part( 'modal', 'project-widget' ); ?>
	</div>
</div>