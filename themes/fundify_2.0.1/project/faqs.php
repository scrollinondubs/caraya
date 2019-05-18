<?php
/**
 * Campaign faqs.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $post;
$project_id = get_post_meta($post->ID, 'ign_project_id', true);
?>

<?php if ( $project_id > 0 ) : ?>
	<div id="faq">
		<!--<h3 class="campaign-faq-title sans"><?php _e( 'FAQ', 'fundify' ); ?></h3>-->
			<?php echo do_shortcode('[project_faq product="'.$project_id.'"]') ?>
	</div>
<?php endif; ?>