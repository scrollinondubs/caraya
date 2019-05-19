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

function getMemberRootHash($hash)
{
    // Get the root_hash corresponding to a member hash.

    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $sql = "SELECT root_hash FROM $table_name WHERE hash = '$hash'";
    $result = $wpdb->query($sql);
    $row = $result->fetch_assoc();
    if ($row) {
        return $row['root_hash'];
    }
    return null;
}

function checkMemberExists($hash)
{
    // Check if a member already exists.

    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $sql = "SELECT 1 FROM $table_name WHERE hash = '$hash'";
    $result = $wpdb->query($sql);
    if ($result->fetch_row()) {
        return true;
    }
    return false;
}

function leaderboardSetMember($order_email)
{
    // Create a new row in the members table for a donor. $order_email
    // is the donor's email address, entered in the donation form.

    global $wpdb;

    // Careful, if a member already exists, we don't want to add them again.
    // This could happen when someone donates using their own referral link.
    $hash = hash($order_email);
    if (checkMemberExists($hash)) {
        return;
    }

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $parent_hash = getLeaderboardReferralCookie();
    $root_hash = getMemberRootHash($parent_hash);

    if($parent_hash) {
        $wpdb->insert(
            $table_name,
            array(
                'hash' => $hash,
                'email' => $order_email,
                'team_id' => null,
                'parent_hash' => $parent_hash,
                'root_hash' => $root_hash
            )
        );
    }
}


add_action('idc_order_lightbox_before', 'leaderboardSetOrder', 10, 2);

