<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Description: Create Leaderboard
 * Author: Charity Makeover
 * Version: 1.0
 * Author URI: http://charitymakeover.com/
 */

function checkForLeaderboardLink()
{
    if (isset($_GET['ryreferral'])) {
        $referralUsedEmail = false;
        $referralHash = $_GET['ryreferral'];

        // filter ryReferral and check if its an email, if so hash it.
        if(filter_var($referralHash, FILTER_VALIDATE_EMAIL)) {
            $referralUsedEmail = $referralHash;
            $referralHash =  hash('sha256', $referralHash);
        }

        // check if hash exists in the member data table.
        if($memberData = getLeaderboardMemberData($referralHash)) {
            setLeaderboardReferralCookie($referralHash);

            // if referral used an email check if we have stored the email for this hash yet. If not store it.
            if ($referralUsedEmail) {
                if (!$memberData->email) {
                    setLeaderboardMemberEmail($referralUsedEmail, $memberData);
                }
            }
        }
    }
}

function setLeaderboardReferralCookie($referralHash)
{
    setcookie(LEADERBOARD_COOKIE, $referralHash, null, '/');
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

function setLeaderboardMemberEmail($referralUsedEmail, $memberData)
{
    global $wpdb;

    $table_name = $wpdb->prefix . LEADERBOARD_MEMBERS_TABLE;

    $wpdb->update(
        $table_name,
        array('email' => $referralUsedEmail),
        array('id' => $memberData->id)
    );
}

add_action('init', 'checkForLeaderboardLink');