<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Author: Charity Makeover
 * Author URI: http://charitymakeover.com/
 */


function getLeaderboard()
{
    global $wpdb;

    $rows = getLeaderBoardData();
    $sortedProjectArray = getMonkeys();

    // Process Teams
    $teams_table = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;
    $teamsSql = "SELECT * FROM " . $teams_table . " WHERE id != 999";
    $teamsArray = $wpdb->get_results($teamsSql, ARRAY_A);

    // Calculate total amount and amount for each team
    $total_donation = 0;
    $team_donations = array();

    // We do this so ALL teams come through even if they don't have a donation.
    foreach ($teamsArray as $teamArray) {
        $team_donations[$teamArray['id']] = array(
            'teamId' => $teamArray['id'],
            'teamName' => $teamArray['name'],
            'donationAmount' => 0,
            'individualDonors' => array()
        );
    }

    // Now we process through each individual donation.
    foreach ($rows as $row) {
        $amount = $row['donation_amount'];
        $team = $row['ry_team_id'];

        if (!array_key_exists($team, $team_donations)) {
            $team_donations[$team] = array(
                'teamId' => $team,
                'teamName' => $row['ry_team_name'],
                'donationAmount' => 0,
                'individualDonors' => array()
            );
        }

        $total_donation = $total_donation + $amount;
        $team_donations[$team]['donationAmount'] = $team_donations[$team]['donationAmount'] + $amount;

        $monkeyProjectId = getMonkeyProjectId($row['meta_value']);
        $monkeyName = 'The Monkeys';
        if ($monkeyProjectId) {
            $monkeyName = $sortedProjectArray[$monkeyProjectId];
        }


        array_push($team_donations[$team]['individualDonors'],
            array(
                'id' => $row['donor_hash'],
                'name' => nameFromDisplayName($row['donor_display_name']),
                'donationAmount' => $amount,
                'message' => 'donated a ' . $row['donated_item'] . ' to ' . $monkeyName . '.',
                'referral' => array('id' => $row['ry_hash'], 'name' => nameFromEmail($row['ry_email']))
            )
        );
    }

    $options = get_option( 'caraya_leaderboard_settings' );

    $leaderboard = array(
        'fundraisingTarget' => $options['caraya_leaderboard_text_target_amount'],
        'endDate' => $options['caraya_leaderboard_date_end_date'],
        'totalRaised' => $total_donation,
        'leaderboard' => array_values($team_donations)
    );

    if (empty($leaderboard)) {

        return new WP_Error( 'empty_leaderboard', 'there is data for the leaderboard to render', array( 'status' => 404 ) );
    }
    return new WP_REST_Response($leaderboard, 200);
}

function getLeaderBoardData()
{
    global $wpdb;

    $members_table = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $orders_table = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;
    $teams_table = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;
    $mdOrders_table = $wpdb->prefix . 'memberdeck_orders';
    $mdOrderMeta_table = $wpdb->prefix .  'memberdeck_order_meta';
    $mdLevels_table = $wpdb->prefix . 'memberdeck_levels';
    $users_table = $wpdb->prefix . 'users';

    $sql = implode("\r\n", array(
        "SELECT",
        "    members_root.email as ry_email,",
        "    members_root.hash as ry_hash,",
        "    teams.name as ry_team_name,",
        "    teams.id as ry_team_id,",
        "    users.user_email as donor_email,",
        "    users.display_name as donor_display_name,",
        "    members.hash as donor_hash,",
        "    memberdeck_levels.level_name as donated_item,",
        "    memberdeck_ordermeta.meta_value as meta_value,",
        "    memberdeck_orders.price as donation_amount",
        "FROM",
        "    $orders_table",
        "    JOIN $mdOrders_table as memberdeck_orders on memberdeck_orders.id = $orders_table.order_id",
        "    JOIN $mdOrderMeta_table as memberdeck_ordermeta on memberdeck_ordermeta.order_id = memberdeck_orders.id AND memberdeck_ordermeta.meta_key = 'extra_fields'",
        "    JOIN $mdLevels_table as memberdeck_levels on memberdeck_levels.id = memberdeck_orders.level_id",
        "    JOIN $users_table as users on users.id = memberdeck_orders.user_id",
        "    JOIN $members_table as members on members.email = users.user_email",
        "    JOIN $members_table as members_root on members_root.hash = members.root_hash",
        "    JOIN $teams_table as teams on teams.id = members_root.team_id;"
    ));

    $rows = $wpdb->get_results($sql, ARRAY_A);

    return $rows;
}

function getMonkeys()
{
    global $wpdb;

    $postmeta_table = $wpdb->prefix . 'postmeta';
    $posts_table = $wpdb->prefix . 'posts';

    $projectsSql = implode("\r\n", array(
        "SELECT",
        "    wp_posts.ID as post_id,",
        "    wp_posts.post_title as project_title,",
        "    $postmeta_table.meta_value as project_id",
        "FROM",
        "    $postmeta_table",
        "    JOIN $posts_table as wp_posts on wp_posts.ID = $postmeta_table.post_id",
        "    WHERE $postmeta_table.meta_key = 'ign_project_id';"
    ));

    $projectsArray = $wpdb->get_results($projectsSql, ARRAY_A);

    $sortedProjectArray = array();
    foreach ($projectsArray as $projectArray) {
        $sortedProjectArray[$projectArray['project_id']] = $projectArray['project_title'];
    }

    return $sortedProjectArray;
}

function nameFromEmail($email)
{
    return explode("@", $email)[0];
}

function nameFromDisplayName($name)
{
    return explode(" ", $name)[0];
}

function getMonkeyProjectId($metaData)
{
    $unserializedMetaData = unserialize($metaData);

    foreach ($unserializedMetaData as $unserializedMetaItem) {
        if ($unserializedMetaItem['name'] == 'project_id') {
            return $unserializedMetaItem['value'];
        }
    }

    return false;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'caraya', '/leaderboard', array(
        'methods' => 'GET',
        'callback' => 'getLeaderboard',
    ) );
} );