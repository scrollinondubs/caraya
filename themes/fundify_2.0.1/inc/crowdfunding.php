<?php
/**
 * Crowdfunding functionality for Fundify
 *
 * @package Fundify
 * @since Fundify 1.0
 */

/**
 * Check if the proper plugins are installed/activated so we can only use certain
 * functionality when we need to.
 *
 * @since Fundify 1.0
 *
 * @return boolean
 */
function fundify_is_crowdfunding() {
	if ( class_exists( 'Easy_Digital_Downloads' ) && class_exists( 'ATCF_Campaign' ) )
		return true;

	return false;
}

function idcf_is_crowdfunding() {
	if (class_exists('ID_Project')) {
		return true;
	}
	return false;
}

/**
 * Plugin Notice
 *
 * Make sure they know they should install Crowdfunding by Astoundify
 *
 * @since Fundify 1.0
 *
 * @return void
 */
function fundify_features_notice() {
	?>
	<div class="updated">
		<p><?php printf( 
					'<strong>'.__( 'Notice', 'fundify').':</strong> '.__('To take advantage of all of the great features Fundify offers, please install the', 'fundify').' <strong><a href="%s">'.__('IgnitionDeck Crowdfunding Plugin', 'fundify').'</a></strong>. <a href="%s" class="alignright">'.__('Hide this message', 'fundify').'.</a>', 
					wp_nonce_url( 'http://ignitiondeck.com/id/?utm_source=fundify&utm_medium=link&utm_content=theme&utm_campaign=fundify' ),
					wp_nonce_url( add_query_arg( array( 'action' => 'fundify-hide-plugin-notice' ), admin_url( 'index.php' ) ), 'fundify-hide-plugin-notice' ) 
			); ?></p>
	</div>
<?php
}

add_action('admin_init', 'fundify_user_notices');

function fundify_user_notices() {
	if ( ( ! fundify_is_crowdfunding() ) && is_admin() && ! get_user_meta( get_current_user_id(), 'fundify-hide-plugin-notice', true ) && !idcf_is_crowdfunding())
		add_action( 'admin_notices', 'fundify_features_notice' );
}

/**
 * Hide plugin notice.
 *
 * @since Fundify 1.0
 *
 * @return void
 */
function fundify_hide_plugin_notice() {
	check_admin_referer( 'fundify-hide-plugin-notice' );

	$user_id = get_current_user_id();

	add_user_meta( $user_id, 'fundify-hide-plugin-notice', 1 );
}
if ( is_admin() )
	add_action( 'admin_action_fundify-hide-plugin-notice', 'fundify_hide_plugin_notice' );

/**
 * If a user has already purchased the download (campaign), don't show them an
 * annoying message.
 *
 * @since Fundify 1.0
 *
 * @return void
 */
remove_action( 'edd_after_download_content', 'edd_show_has_purchased_item_message' );

/**
 * Show campaigns on author archive.
 *
 * @since Fundify 1.0
 *
 * @return void
 */
function fundify_post_author_archive( $query ) {
	if (fundify_is_crowdfunding()) {
		if ( $query->is_author ) {
			$query->set( 'post_type', 'download' );
		}
	}
}
add_action( 'pre_get_posts', 'fundify_post_author_archive' );

/**
 * Depending on where the user has searched from (either on a campaign page)
 * or from the blog sidebar, change which post types show.
 *
 * @since Fundify 1.0
 *
 * @return void
 */
function fundify_search_filter( $query ) {
	if ( ! $query->is_search() )
		return;

	$post_type = isset ( $_GET[ 'type' ] ) ? esc_attr( $_GET[ 'type' ] ) : null;

	if ( ! in_array( $post_type, array( 'download', 'post', 'ignition_product' ) ) )
		return;

	if ( ! $post_type )
		$post_type = 'post';

	$query->set( 'post_type', $post_type );

	return $query;
};
add_filter( 'pre_get_posts', 'fundify_search_filter' );

/**
 * Expired campaign shim.
 *
 * When a campaign is inactive, we display the inactive pledge amounts,
 * but the lack of form around them messes with the styling a bit, and we
 * lose our header. This fixes that. 
 *
 * @since Fundify 1.3
 *
 * @param object $campaign The current campaign.
 * @return void
 */
function fundify_contribute_modal_top_expired( $campaign ) {
	if ( $campaign->is_active() )
		return;
?>
	<div class="edd_download_purchase_form">
		<h2><?php printf( __( 'This %s has ended. No more pledges can be made.', 'fundify' ), edd_get_label_singular() ); ?></h2>
<?php
}
add_action( 'fundify_contribute_modal_top', 'fundify_contribute_modal_top_expired' );

/**
 * Expired campaign shim.
 *
 * @since Fundify 1.3
 *
 * @param object $campaign The current campaign.
 * @return void
 */
function fundify_contribute_modal_bottom_expired( $campaign ) {
	if ( $campaign->is_active() )
		return;
?>
	</div>
<?php
}
add_action( 'fundify_contribute_modal_bottom', 'fundify_contribute_modal_bottom_expired' );