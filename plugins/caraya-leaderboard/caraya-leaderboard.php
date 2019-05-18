<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Description: Create Leaderboard
 * Author: Charity Makeover
 * Version: 1.0
 * Author URI: http://charitymakeover.com/
 */

const LEADERBOARD_TEAMS_TABLE = 'leaderboard_teams';
const LEADERBOARD_MEMBERS_TABLE = 'leaderboard_members';
const LEADERBOARD_ORDERS_TABLE = 'leaderboard_orders';
const LEADERBOARD_REFERRAL_TREE = 'leaderboard_referral_tree';
const LEADERBOARD_COOKIE = 'caraya-ry-leaderboard';

function leaderboardActivation()
{
    createLeaderboardTeams();
    createLeaderboardMembers();
    createLeaderboardOrders();
    createLeaderboardReferralTree();
}

function leaderboardDeactivation()
{
    // This is a quick way to toggle deleting of data and starting fresh. Uncomment to drop the tables.
//    deleteLeaderboardMembers();
//    deleteLeaderboardTeams();
//    deleteLeaderboardOrders();
}

function createLeaderboardTeams()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'name VARCHAR(255) NOT NULL,',
        'PRIMARY KEY (`id`),',
        'CONSTRAINT UNIQUE INDEX `unique_name` (`name`)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
}

function createLeaderboardMembers()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';
    $leaderBoardTeamsTable = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'hash VARCHAR(255),',
        'email VARCHAR(255),',
        'team_id INT(8),',
        'PRIMARY KEY (`id`),',
        'CONSTRAINT UNIQUE INDEX `unique_email` (`email`),',
        'FOREIGN KEY (team_id) REFERENCES ' . $leaderBoardTeamsTable . '(id)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
}

function createLeaderboardOrders()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';
    $ordersTable = $wpdb->prefix . 'memberdeck_orders';
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'referral_id VARCHAR(255),',
        'order_id mediumint(9),',
        'PRIMARY KEY (`id`),',
        'FOREIGN KEY (order_id) REFERENCES ' . $ordersTable . '(id)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
}

function createLeaderboardReferralTree()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_REFERRAL_TREE_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci';
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'referral_id VARCHAR(255) NOT NULL,',
        'parent_id VARCHAR(255),',
        'root_id VARCHAR(255) NOT NULL,',
        'PRIMARY KEY (`id`),',
        'CONSTRAINT UNIQUE INDEX `unique_referral_id` (`referral_id`)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
}

function deleteLeaderboardMembers() {
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $wpdb->query('DROP TABLE '. $table_name);
}

function deleteLeaderboardTeams() {
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;
    $wpdb->query('DROP TABLE '. $table_name);
}

function deleteLeaderboardOrders() {
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;
    $wpdb->query('DROP TABLE '. $table_name);
}

function deleteLeaderboardReferralTree() {
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_REFERRAL_TREE_TABLE;
    $wpdb->query('DROP TABLE '. $table_name);
}

register_activation_hook(__FILE__, 'leaderboardActivation');

register_deactivation_hook(__FILE__, 'leaderboardDeactivation');

function checkForLeaderboardLink()
{
    if (isset($_GET['ryreferral'])) {
        $ryReferral = $_GET['ryreferral'];

        // filter ryReferral and check if its an email, if so hash it.
        // Otherwise attempt to match the string to a hash.
        if(filter_var($ryReferral, FILTER_VALIDATE_EMAIL)) {
            $referralHash =  hash('sha256', $ryReferral);
        } else {
            $referralHash = $ryReferral;
        }

        $memberData = setLeaderboardReferralCookie($referralHash);
    }
}

function setLeaderboardReferralCookie($referralHash)
{
    if($memberData = getLeaderboardMemberData($referralHash)) {
        setcookie(LEADERBOARD_COOKIE, $memberData->team_id, null, '/');
        return $memberData;
    }

    return false;
}

function getLeaderboardMemberData($referralHash)
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;

    $leaderboardMember = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE hash = %s",
            $referralHash
        )
    );

    return $leaderboardMember;
}

add_action('init', 'checkForLeaderboardLink');

