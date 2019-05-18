<?php
/**
 * Campaign tabs.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;
global $post;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
if (class_exists('ID_Member')) {
	$durl = md_get_durl();
}
?>

<div class="sort-tabs campaign">
	<ul>
		<?php do_action( 'fundify_campaign_tabs_before', $project_id ); ?>

		<li><a href="#description" class="campaign-view-descrption tabber"><?php _e( 'Overview', 'fundify' ); ?></a></li>
		<?php //if ( !empty($faq) ) : ?>
		<li><a href="#faq" class="tabber"><?php _e( 'FAQ', 'fundify' ); ?></a></li>
		<?php //if ( !empty($updates) ) : ?>
		<li><a href="#updates" class="tabber"><?php _e( 'Updates', 'fundify' ); ?></a></li>
		<?php //endif; ?>
		<?php if (comments_open()) { ?>
		<li><a href="#comments" class="tabber"><?php _e( 'Comments', 'fundify' ); ?></a></li>
		<?php } ?>
		<li><a href="#backers" class="tabber"><?php _e( 'Backers', 'fundify' ); ?></a></li>
		<?php if ( get_current_user_id() == $post->post_author || current_user_can( 'manage_options' ) && isset($durl)) : ?>
		<li><a href="<?php echo $durl.'/?edit_project='.$post_id; ?>"><?php _e( 'Edit Campaign', 'fundify' ); ?></a></li>
		<?php endif; ?>

		<?php do_action( 'fundify_campaign_tabs_after', $campaign ); ?>
	</ul>
</div>