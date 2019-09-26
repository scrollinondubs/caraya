<?php
Class ID_Recover_Password {

	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_filter('idc_localization_strings', array($this, 'localize_text'), 999);
		add_filter('lostpassword_url', array($this, 'filter_pw_url'), 10, 2 );
		add_filter('the_content', array($this, 'pw_reset_form'));
		add_action('init', array($this, 'send_pw_reset'));
		add_action('init', array($this, 'update_password'));
	}

	function enqueue_scripts() {
		wp_register_script('id_recover_password',  plugins_url('js/id_recover_password-min.js', __FILE__), array('jquery', 'idf'));
		wp_enqueue_script('id_recover_password');
	}

	function localize_text($strings) {
		$strings['pw_reset_error'] = __('Passwords do not match or are fewer than five characters', 'memberdeck');
		return $strings;
	}

	function filter_pw_url($url, $redirect) {
		$prefix = idf_get_querystring_prefix();
		$url = md_get_durl(is_ssl()).$prefix.'?action=idc_recover_password';
		return $url;
	}

	function pw_reset_form($content) {
		if (!isset($_GET['action'])) {
			return $content;
		}

		$action_array = array(
			'idc_recover_password',
			'idc_password_reset',
			'rp',
			'idc_rp'
		);

		$action = sanitize_text_field($_GET['action']);

		if (!in_array($action, $action_array)) {
			return $content;
		}

		$prefix = idf_get_querystring_prefix();

		$form = '<div class="memberdeck ignitiondeck">';
		$form .= '<h3 class="center">'.__('Reset Password', 'memberdeck').'</h3>';
		$form_errors = '<div class="form-row">';
		$form_errors .= '<p class="error blank-field hide"></p>';
		$form_errors .= '</div>';
		switch ($action) {
			case 'idc_recover_password':
				$url = md_get_durl(is_ssl()).$prefix.'action=idc_password_reset';
				$form .= '<form action="'.$url.'" method="POST" name="idc_password_reset" id="payment-form" data-action="'.$action.'">';
				$form .= '<div class="no" id="logged-input">';
				$form .= __('Enter your email address to receive a password recovery email', 'memberdeck');
				$form .= '<div class="form-row left">';
				$form .= '<label for="email">'.__('Email address', 'memberdeck').'</label>';
				$form .= '<input id="email" class="" type="email" name="email" />';
				$form .= '</div>';
				$form .= '</div>';
				$form .= $form_errors;
				$form .= '<div class="form-row">';
				$form .= '<input class="button button-primary" id="idc_password_reset" name="idc_password_reset" type="submit" value="'.__('Submit', 'memberdeck').'"/>';
				break;
			case 'idc_password_reset':
				$url = null;
				$form .= '<form action="'.$url.'" method="POST" name="idc_password_reset" id="payment-form" data-action="'.$action.'">';
				$form .= '<div class="no" id="logged-input">';
				$form .= __('Your password reset has been submitted', 'memberdeck').'.<br/>';
				$form .= __('Please check your email for a password recovery link', 'memberdeck').'.';
				break;
			case 'rp':
				$login = sanitize_text_field($_GET['login']);
				$url = md_get_durl(is_ssl()).$prefix.'action=idc_rp&login='.$login;
				$form .= '<form action="'.$url.'" method="POST" name="idc_password_reset" id="payment-form" data-action="'.$action.'">';
				$form .= '<div class="no" id="logged-input">';
				$form .= '<div class="form-row left">';
				$form .= '<label for="new_password">'.__('Please enter a new secure password', 'memberdeck').'</label>';
				$form .= '<input id="new_password" class="" type="password" name="new_password" />';
				$form .= '</div>';
				$form .= '<div class="form-row left">';
				$form .= '<label for="new_password_confirm">'.__('Confirm new password', 'memberdeck').'</label>';
				$form .= '<input id="new_password_confirm" class="" type="password" name="new_password_confirm" />';
				$form .= '</div>';
				$form .= '</div>';
				$form .= $form_errors;
				$form .= '<div class="form-row">';
				$form .= '<input class="button button-primary" id="idc_submit_password" name="idc_submit_password" type="submit" value="'.__('Submit', 'memberdeck').'"/>';
				break;
			case 'idc_rp':
				$url = md_get_durl();
				$form .= '<form action="'.$url.'" method="POST" name="idc_rp" id="payment-form" data-action="'.$action.'">';
				$form .= '<div class="no" id="logged-input">';
				$form .= __('Your password has been reset', 'memberdeck').'.<br/>';
				$form .= sprintf(__('%sClick Here%s to login', 'memberdeck'), '<a href='.$url.'>', '</a>').'.';
				break;
		}
		
		$form .= '</div>';
		$form .= '</form>';
		$form .= '</div>';
		return $form;
	}

	function send_pw_reset() {

		if (!isset($_GET['action'])) {
			return;
		}
		

		$action = sanitize_text_field($_GET['action']);

		if ($action !== 'idc_password_reset') {
			return;
		}

		$email = sanitize_email($_POST['email']);
		if (empty($email)) {
			return;
		}

		$user_data = get_user_by('email', $email);

		if (empty($user_data)) {
			return;
		}

		$prefix = idf_get_querystring_prefix();
		$url = md_get_durl(is_ssl()).$prefix;

		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		$key = get_password_reset_key( $user_data );

		if ( is_wp_error( $key ) ) {
			echo 'shit';
			return $key;
		}

		if ( is_multisite() ) {
			$site_name = get_network()->site_name;
		} else {
			/*
			 * The blogname option is escaped with esc_html on the way into the database
			 * in sanitize_option we want to reverse this for the plain text arena of emails.
			 */
			$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		}

		$message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
		/* translators: %s: site name */
		$message .= sprintf(__('Site Name: %s'), $site_name) . "\r\n\r\n";
		/* translators: %s: user login */
		$message .= sprintf( __('Username: %s'), $user_login ) . "\r\n\r\n";
		$message .= __('If this was a mistake, just ignore this email and nothing will happen.') . "\r\n\r\n";
		$message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
		$message .= '<'.$url.'action=rp&key=$key&login='.rawurlencode($user_login).">\r\n";

		/* translators: Password reset email subject. %s: Site name */
		$title = sprintf(__('[%s] Password Reset'), $site_name);

		/**
		 * Filters the subject of the password reset email.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
		 *
		 * @param string  $title      Default email title.
		 * @param string  $user_login The username for the user.
		 * @param WP_User $user_data  WP_User object.
		 */
		$title = apply_filters('retrieve_password_title', $title, $user_login, $user_data);

		/**
		 * Filters the message body of the password reset mail.
		 *
		 * If the filtered message is empty, the password reset email will not be sent.
		 *
		 * @since 2.8.0
		 * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
		 *
		 * @param string  $message    Default mail message.
		 * @param string  $key        The activation key.
		 * @param string  $user_login The username for the user.
		 * @param WP_User $user_data  WP_User object.
		 */
		$message = apply_filters('retrieve_password_message', $message, $key, $user_login, $user_data );

		if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message))
			wp_die(__('The email could not be sent.') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.'));

		return true;
	}

	function update_password() {
		if (!isset($_GET['action'])) {
			return;
		}

		$action = sanitize_text_field($_GET['action']);

		if ($action !== 'idc_rp') {
			return;
		}

		$pw = sanitize_text_field($_POST['new_password']);
		$cpw = sanitize_text_field($_POST['new_password_confirm']);
		if ($pw !== $cpw) {
			// #devnote display error here
			return;
		}

		$login = sanitize_text_field($_GET['login']);

		if (empty($login)) {
			return;
		}

		$user = get_user_by('email', $login);

		if (empty($user)) {
			return;
		}

		$update = wp_set_password($pw, $user->ID);
	}
}



$shipping_info = new ID_Recover_Password();
?>