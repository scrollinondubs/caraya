<?php

function nameFromEmail($email)
{
    return explode("@", $email)[0];
}

function getLeaderboard()
{
    global $wpdb;
    $members_table = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;
    $orders_table = $wpdb->prefix . LEADERBOARD_ORDERS_TABLE;
    $teams_table = $wpdb->prefix . LEADERBOARD_TEAMS_TABLE;

    $sql = implode("\r\n", array(
        "SELECT",
        "    members_root.email as ry_email,",
        "    members_root.hash as ry_hash,",
        "    teams.name as ry_team,",
        "    teams.id as ry_team_id,",
        "    members.email as donor_email,",
        "    members.hash as donor_hash,",
        "    memberdeck_orders.price as donation_amount",
        "FROM",
        "    $orders_table",
        "    JOIN memberdeck_orders on memberdeck_orders.id = leaderboard_orders.order_id",
        "    JOIN $members_table as members on members.hash = leaderboard_orders.referral_id",
        "    JOIN $members_table as members_root on members_root.hash = members.root_hash",
        "    JOIN $teams_table as teams on teams.id = members_root.team_id;"
    ));

    $rows = $wpdb->get_results($sql, ARRAY_A);

    // Calculate total amount and amount for each team
    $total_donation = 0;
    $team_donations = array();
    foreach ($rows as $r) {
        $amount = $r['donation_amount'];
        $team = $r['ry_team'];

        if (!array_key_exists($team, $team_donations)) {
            $team_donations[$team] = array(
                'teamId' => $r['ry_team_id'],
                'teamName' => $team,
                'donationAmount' => 0, 
                'individualDonors' => array()
            );
        }

        $total_donation = $total_donation + $amount;
        $team_donations[$team]['donationAmount'] = $team_donations[$team]['donationAmount'] + $amount;

        array_push($team_donations[$team]['individualDonors'],
            array(
                'id' => $r['donor_hash'],
                'name' => nameFromEmail($r['donor_email']),
                'donationAmount' => $amount,
                'message' => '',
                'referral' => array('id' => $r['ry_hash'], 'name' => nameFromEmail($r['ry_email'])) 
            )
        );
    }

    $leaderboard = array(
        'totalRaised' => $total_donation,
        'leaderboard' => array_values($team_donations)
    );

    return leaderboard;
}

?>
