<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Author: Charity Makeover
 * Author URI: http://charitymakeover.com/
 */

function leaderboardSetOrder($last_order)
{
    global $wpdb;

    $user = wp_get_current_user();
    if (!$user) {
        echo 'Error, no user currently logged in';
        return;
    }

    $table_name = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;
    if($hash = getLeaderboardReferralCookie()) {
        $wpdb->insert(
            $table_name,
            array(
                'referral_id' => $hash,
                'order_id' => $last_order->id,
            )
        );
    }
}


add_action('idc_order_lightbox_before', 'leaderboardSetOrder', 10, 2);