<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Description: Leaderboard plugin, built for The Charity Makeover competition for remote year alumni.
 * Author: Charity Makeover
 * Version: 1.0
 * Author URI: http://charitymakeover.com/
 */

const LEADERBOARD_TEAMS_TABLE = 'leaderboard_teams';
const LEADERBOARD_MEMBERS_TABLE = 'leaderboard_members';
const LEADERBOARD_ORDERS_TABLE = 'leaderboard_orders';
const LEADERBOARD_COOKIE = 'caraya-ry-leaderboard';

function leaderboardActivation()
{
    createLeaderboardTeams();
    createLeaderboardMembers();
    createLeaderboardOrders();
}

function leaderboardDeactivation()
{
    // This is a quick way to toggle deleting of data and starting fresh. Uncomment to drop the tables.
    // deleteLeaderboardOrders();
    // deleteLeaderboardMembers();
    // deleteLeaderboardTeams();
}

function createLeaderboardTeams()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'name VARCHAR(190) NOT NULL,',
        'created_at DATETIME NOT NULL DEFAULT current_timestamp(),',
        'updated_at DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),',
        'PRIMARY KEY (`id`),',
        'CONSTRAINT UNIQUE INDEX `unique_name` (`name`)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
    importLeaderboardTeams($table_name);
}

function createLeaderboardMembers()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $leaderBoardTeamsTable = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'hash VARCHAR(190),',
        'email VARCHAR(190),',
        'team_id INT(8),',
        'parent_hash VARCHAR(190),',
        'root_hash VARCHAR(190),',
        'created_at DATETIME NOT NULL DEFAULT current_timestamp(),',
        'updated_at DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),',
        'PRIMARY KEY (`id`),',
        'CONSTRAINT UNIQUE INDEX `unique_email` (`email`),',
        'FOREIGN KEY (team_id) REFERENCES ' . $leaderBoardTeamsTable . '(id)',
        ") $charset_collate",
    ));

    $wpdb->query($create);

    importLeaderboardMembers($table_name);
}

function createLeaderboardOrders()
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;

    $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
    if ($wpdb->get_var( $query ) == $table_name) {
        return true;
    }

    $charset_collate = 'DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $ordersTable = $wpdb->prefix . 'memberdeck_orders';
    $create = implode("\r\n", array(
        "CREATE TABLE `$table_name` (",
        'id INT(8) NOT NULL AUTO_INCREMENT,',
        'referral_id VARCHAR(190),',
        'order_id mediumint(9),',
        'created_at DATETIME NOT NULL DEFAULT current_timestamp(),',
        'updated_at DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),',
        'PRIMARY KEY (`id`),',
        'FOREIGN KEY (order_id) REFERENCES ' . $ordersTable . '(id)',
        ") $charset_collate",
    ));

    $wpdb->query($create);
}

function importLeaderboardTeams($table_name) {
    global $wpdb;

    $teamsJson = file_get_contents(plugin_dir_path( __FILE__ ) . 'data/teams.json');
    $teamsArray = json_decode($teamsJson, true);

    // Note: We are doing this since the ID, is directly related to RY. There will be future classes.
    // To account for that we are setting this at 999 for non-ry and can manually add future RY...
    // classes if the need ever arises.
    $teamsArray[999] = 'Non-Remote Year';

    foreach ($teamsArray as $key => $value) {
        $wpdb->insert(
            $table_name,
            array(
                'id' => $key,
                'name' => $value,
            )
        );
    }
}

function importLeaderboardMembers($table_name) {
    global $wpdb;

    $teamsJson = file_get_contents(plugin_dir_path( __FILE__ ) . 'data/teams.json');
    $teamsArray = json_decode($teamsJson, true);
    $teamsArray = array_flip($teamsArray);

    $membersJson = file_get_contents(plugin_dir_path( __FILE__ ) . 'data/members.json');
    $membersArray = json_decode($membersJson, true);

    foreach ($membersArray as $key => $value) {
        $wpdb->insert(
            $table_name,
            array(
                'hash' => $key,
                'team_id' => $teamsArray[$value],
                'parent_hash' => null,
                'root_hash' => $key
            )
        );
    }
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

function leaderboardSettingsPage() {
    add_options_page( 'Settings API Page', 'Settings API Page', 'manage_options', 'settings-api-page', 'settings_api_page' );
}

register_activation_hook(__FILE__, 'leaderboardActivation');
register_deactivation_hook(__FILE__, 'leaderboardDeactivation');

require_once(plugin_dir_path( __FILE__ ) . 'admin/settings.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/manage-cookies.php');
require_once(plugin_dir_path( __FILE__ ) . 'includes/manage-order.php');
require_once(plugin_dir_path( __FILE__ ) . 'routes/get-leaderboard.php');