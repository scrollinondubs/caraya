<?php
/**
 * Plugin Name: Caraya Leaderboard
 * Author: Charity Makeover
 * Author URI: http://charitymakeover.com/
 */

add_action( 'admin_menu', 'caraya_leaderboard_add_admin_menu' );
add_action( 'admin_init', 'caraya_leaderboard_settings_init' );

function caraya_leaderboard_add_admin_menu()
{
    add_options_page( 'Leaderboard Settings', 'Leaderboard Settings', 'manage_options', 'leaderboard-settings', 'caraya_leaderboard_options_page' );
}

function caraya_leaderboard_settings_init()
{
    register_setting( 'leaderboardPlugin', 'caraya_leaderboard_settings' );
    add_settings_section(
        'caraya_leaderboardgeneral_settings',
        __( 'General Settings', 'wordpress' ),
        'caraya_leaderboard_settings_section_callback',
        'leaderboardPlugin'
    );

    add_settings_field(
        'caraya_leaderboard_text_target_amount',
        __( 'Target Amount To Raise', 'wordpress' ),
        'caraya_leaderboard_text_target_amount_render',
        'leaderboardPlugin',
        'caraya_leaderboardgeneral_settings'
    );

    add_settings_field(
        'caraya_leaderboard_date_end_date',
        __( 'Date Event Ends', 'wordpress' ),
        'caraya_leaderboard_date_end_date_render',
        'leaderboardPlugin',
        'caraya_leaderboardgeneral_settings'
    );
}

function caraya_leaderboard_text_target_amount_render()
{
    $options = get_option( 'caraya_leaderboard_settings' );
    ?>
    <input type='text' name='caraya_leaderboard_settings[caraya_leaderboard_text_target_amount]' value='<?php echo $options['caraya_leaderboard_text_target_amount']; ?>'>
    <?php
}

function caraya_leaderboard_date_end_date_render()
{
    $options = get_option( 'caraya_leaderboard_settings' );
    ?>
    <input type="date" name="caraya_leaderboard_settings[caraya_leaderboard_date_end_date]" value="<?php echo $options['caraya_leaderboard_date_end_date']; ?>">

<?php
}

function caraya_leaderboard_settings_section_callback()
{
    echo __( 'Update leaderboard settings below', 'wordpress' );
}

function caraya_leaderboard_options_page()
{
    ?>
    <form action='options.php' method='post'>

        <h1>Caraya Leaderboard</h1>

        <?php
        settings_fields( 'leaderboardPlugin' );
        do_settings_sections( 'leaderboardPlugin' );
        submit_button();
        ?>

    </form>
    <?php
}