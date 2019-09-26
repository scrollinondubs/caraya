<?php

class ID_Paytm {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('admin_menu', array($this, 'paytm_admin'), 12);
		if (self::settings_complete()) {
			add_action('init', array($this, 'paytm_init'));
			add_action('init', array($this, 'webhook_handler'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_filter('id_paytm_settings', array($this, 'filter_settings'));
			add_filter('idc_localization_strings', array($this, 'localization_strings'), 11);
			add_action('idc_after_credit_card_selectors', array($this, 'paytm_checkout_selector'));
			add_filter('idc_checkout_descriptions', array($this, 'paytm_checkout_description'), 10, 7);
			add_action('wp_ajax_id_paytm_submit', array($this, 'payment_request'));
			add_action('wp_ajax_nopriv_id_paytm_submit', array($this, 'payment_request'));
			add_filter('idc_paytm_callback_url', array($this, 'prepare_callback'));
			add_filter('idc_order_currency', array($this, 'filter_currency_code'), 10, 3);
			global $crowdfunding;
			if ($crowdfunding) {
				add_filter('memberdeck_payment_success', array($this, 'add_idcf_order'), 3, 5);
			}
		}
	}

	function paytm_init() {
		wp_register_script('id_paytm', plugins_url('js/id_paytm-min.js', __FILE__));
		//wp_register_style('id_paytm', plugins_url('css/id_paytm-min.css', __FILE__));
	}

	function settings_complete() {
		$settings = self::get_settings();
		return !empty($settings);
	}

	function get_settings() {
		$paytm_settings = apply_filters('id_paytm_settings', get_option('id_paytm_settings'));
		return $paytm_settings;
	}

	function filter_settings($paytm_settings) {
		$gateway_settings = get_option('memberdeck_gateways');
		if (isset($gateway_settings['test']) && $gateway_settings['test']) {
			return self::get_test_settings($paytm_settings);
		}
		return $paytm_settings;
	}

	function get_test_settings($paytm_settings) {
		return array(
			'paytm_merchant_key' => $paytm_settings['paytm_staging_merchant_key'],
			'paytm_merchant_mid' => $paytm_settings['paytm_staging_merchant_mid'],
			'paytm_merchant_website' => $paytm_settings['paytm_staging_merchant_website'],
			'paytm_industry_type' => $paytm_settings['paytm_staging_industry_type']
		);
	}

	function enqueue_scripts() {
		global $post;
		if (!empty($post)) {
			#devnote convert to function globally
			if (has_shortcode($post->post_content, 'idc_checkout') || has_shortcode($post->post_content, 'memberdeck_checkout') || has_shortcode($post->post_content, 'idc_dashboard') || has_shortcode($post->post_content, 'memberdeck_dashboard') || isset($_GET['mdid_checkout']) || isset($_GET['idc_renew']) || isset($_GET['idc_button_submit'])) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('id_paytm');
				//wp_enqueue_style('id_paytm', plugins_url('js/id_paytm.css', __FILE__));
			}
		}
	}

	function paytm_admin() {
		$paytm_admin = add_submenu_page('idc', __('Paytm', 'memberdeck'), __('Paytm', 'memberdeck'), 'manage_options', 'idc-paytm', array($this, 'paytm_admin_menu'));
	}

	function paytm_admin_menu() {
		$settings = get_option('id_paytm_settings');
		if (isset($_POST['id_paytm_settings_submit'])) {
			foreach ($_POST as $k=>$v) {
				if ($k !== 'id_paytm_settings_submit') {
					$settings[$k] = sanitize_text_field($v);
				}
			}
			update_option('id_paytm_settings', $settings);
		}
		include_once(dirname(__FILE__) . '/' . 'templates/admin/_adminMenu.php');
	}

	function localization_strings($strings) {
		$strings['pay_with_paytm'] = __('Pay with Paytm', 'memberdeck');
		return $strings;
	}

	function paytm_checkout_selector($gateways) {
		$selector = '<div>';
		$selector .= '<a id="pay-with-paytm" class="pay_selector" href="#">';
        $selector .= '<i class="fa fa-shopping-cart"></i>';
		$selector .= '<span>'.__('PAYTM', 'memberdeck').'</span>';
		$selector .= '</a>';
		$selector .= '</div>';
		echo $selector;
	}

	function paytm_checkout_description($content, $level, $level_price, $user_data, $gateways, $general, $credit_value) {
		ob_start();
		include_once 'templates/_checkoutPaytmDescription.php';
		$paytm_description = ob_get_contents();
		ob_end_clean();
		$content .= apply_filters('id_paytm_checkout_description', $paytm_description, $level, $level_price, $user_data, $gateways, $general);

		return $content;
	}

	function payment_request() {
		global $wpdb;
		# Collect settings
		$gateway_settings = get_option('memberdeck_gateways');
		$paytm_settings = self::get_settings();

		# Paytm processing
		require (dirname(__FILE__) . '/' . 'lib/Paytm_Web_Sample_Kit_PHP-master/PaytmKit/lib/config_paytm.php');
		require (dirname(__FILE__) . '/' . 'lib/Paytm_Web_Sample_Kit_PHP-master/PaytmKit/lib/encdec_paytm.php');

		# IDC data
		global $crowdfunding;
		$customer = idf_sanitize_array($_POST['customer']);
		$fields = (isset($_POST['Fields']) ? $_POST['Fields'] : null);
		$txn_type = $_POST['txnType'];
		$renewable = ((isset($_POST['Renewable'])) ? $_POST['Renewable'] : '');
		$pwyw_price = ((isset($_POST['pwyw_price'])) ? sanitize_text_field($_POST['pwyw_price']) : '');
		$product_id = absint(sanitize_text_field($customer['product_id']));
		// #devnote missing upgrade stuff
		if (empty($product_id)) {
			// #devnote fail here
			$message = __('Product ID is missing or incomplete', 'memberdeck').' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		$level_data = apply_filters('idc_level_data', ID_Member_Level::get_level($product_id), 'checkout');
		if (empty($level_data)) {
			// #devnote fail here
			$message = __('Level data is missing or incomplete', 'memberdeck').' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		#devnote do we support guest checkout here? Does the square gateway?
		if (isset($pwyw_price) && $pwyw_price > 0) {
			if ($level_data->product_type == 'purchase') {
				if ($pwyw_price > $level_data->level_price) {
					$level_data->level_price = $pwyw_price;
				}
			}
			else {
				$level_data->level_price = $pwyw_price;
			}
		}
		if (isset($customer['email'])) {
			$user_email = sanitize_email($customer['email']);
		}
		else {
			// they have used 1cc or some other mechanism and we don't have their email
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$user_email = $current_user->user_email;
			}
		}
		$member = new ID_Member();
		$check_user = $member->check_user($user_email);
		if (empty($check_user)) {
			# This is a new user
			if (!empty($customer['pw'])) {
				// only create user if we have a password and intend to create an account
				$user_id = wp_insert_user(array('user_email' => $customer['email'], 'user_login' => $customer['email'], 'user_pass' => $customer['pw'], 'first_name' => $customer['first_name'], 'last_name' => $customer['last_name'], 'display_name' => $customer['first_name']));
			}
			if (!empty($user_id)) {
				do_action('idc_register_success', $user_id, $customer['email']);
				$check_user = get_user_by('id', $user_id);
			}
			$user_email = $customer['email'];	
		}
		$paramList = array();
		$ORDER_ID = uniqid(null, true); //$_POST["ORDER_ID"];
		$CUST_ID = (isset($check_user->ID) ? $check_user->ID : $user_email); //$_POST["CUST_ID"];

		// Create an array having all required parameters for creating checksum.
		$paramList['MID'] = PAYTM_MERCHANT_MID;
		$paramList['ORDER_ID'] = $ORDER_ID;
		$paramList['CUST_ID'] = $CUST_ID;
		$paramList['INDUSTRY_TYPE_ID'] = PAYTM_INDUSTRY_TYPE;
		$paramList['CHANNEL_ID'] = 'WEB';
		$paramList['TXN_AMOUNT'] = $level_data->level_price;
		$paramList['WEBSITE'] = PAYTM_MERCHANT_WEBSITE;
		$paramList['EMAIL'] = $user_email;
		$paramList['CALLBACK_URL'] = apply_filters('idc_paytm_callback_url', md_get_durl().idf_get_querystring_prefix().'memberdeck_notify=paytm&email='.$user_email.'&first_name='.$customer['first_name'].'&last_name='.$customer['last_name'].'&guest_checkout=0');
		//$paramList['MOBILE_NO'] = '919953262848';
		if (!empty($fields)) {
			foreach ($fields as $field) {
				$paramList['CALLBACK_URL'] .= '&'.$field['name'].'='.$field['value'];
			}
		}
		$checkSum = getChecksumFromArray($paramList,PAYTM_MERCHANT_KEY);
		$paramList['CHECKSUMHASH'] = $checkSum;
		$form = '<form method="post" action="'.PAYTM_TXN_URL.'" name="f1">';
		foreach($paramList as $k=>$v) {
			$form .= '<input type="hidden" name="'.$k.'" value="'. $v.'">';
		}
		$form .= '</form>';

		print_r(json_encode(array('response' => 'success', 'message' => $form)));
		exit;
	}

	function prepare_callback($url) {
		return $url;
	}

	function filter_currency_code($currency_code, $global_currency, $source) {
		return 'INR';
	}

	function webhook_handler() {
		if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'paytm') {
			update_option('paytm_get_test_'.time(), $_GET);
			update_option('paytm_post_test_'.time(), $_POST);

			$vars = array();

			$payment_complete = false;
			$status = null;
			
			foreach($_POST as $key=>$val) {
	            $vars[$key] = sanitize_text_field($val);
	        }
	        foreach($_GET as $k=>$v) {
	        	if ($k == 'email') {
        			$vars[$k] = sanitize_email($v);
        		}
        		else {
        			$vars[$k] = sanitize_text_field($v);
        		}
	        }
	        if (empty($vars)) {
	        	return;
	        }

	        if (!isset($vars['STATUS'])) {
	        	return;
	        }

	        switch ($vars['STATUS']) {
	        	case 'TXN_SUCCESS':
	        		$payment_complete = true;
	        		break;
	        	
	        	default:
	        		#devnote handle failure?
	        		return;
	        		break;
	        }

	        if (!$payment_complete) {
	        	return;
	        }

        	// lets get our vars
        	$fields = $_GET;
        	$guest_checkout = (isset($vars['guest_checkout']) ? $vars['guest_checkout'] : 0);
            $fname = $vars['first_name'];
            $lname = $vars['last_name'];
            $price = $vars['TXNAMOUNT'];
            $email = $vars['email'];
            $product_id = $vars['mdid_checkout'];
            $txn_id = $vars['TXNID'];

			$txn_check = ID_Member_Order::check_order_exists($txn_id);			            
			if (!empty($txn_check)) {
				return;
			}	           

			$customer = array(
	   			'product_id' => $product_id,
	   			'first_name' => $fname,
	   			'last_name' => $lname,
	   			'email' => $email
	   		);

	   		$new_data = array('checksum' => $vars['CHECKSUMHASH']);

			$level_data = ID_Member_Level::get_level($product_id);
			$e_date = idc_set_order_edate($level_data);

			$access_levels = array(absint($product_id));
				$member = new ID_Member();
	        $check_user = $member->check_user($email);

			if (!empty($check_user)) {
				// WP User exists
				$user_id = $check_user->ID;
	        	$match_user = $member->match_user($user_id);

	        	if (empty($match_user)) {
	        		// First IDC purchase
	        		$data = $new_data;
	        		$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
					$new = ID_Member::add_user($user);
					$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, null, 'active', $e_date, $price);
					$new_order = $order->add_order();
	        	}

	        	else {
	        		// IDC member, merge product access
	        		if (isset($match_user->access_level)) {
	        			$levels = maybe_unserialize($match_user->access_level);
	        			if (!empty($levels)) {
	            			foreach ($levels as $lvl) {
								$access_levels[] = absint($lvl);
							}
						}
	        		}

	        		if (isset($match_user->data)) {
	        			$data = unserialize($match_user->data);
	        			if (!is_array($data)) {
	        				$data = array($data);
	        			}
	        			$data[] = $new_data;
	        		}
	        		else {
	        			$data = $new_data;
	        		}

					$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
					$new = ID_Member::update_user($user);
					//fwrite($log, $user_id);
					$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, null, 'active', $e_date, $price);
					$new_order = $order->add_order();
	        	}
			}
			else {
				// WP User does not exist
				$data = $new_data;
	        	if (!$guest_checkout) {
	        		// gen random pw they can change later
	        		$pw = idmember_pw_gen();
	        		// gen our user input
	            	$userdata = array('user_pass' => $pw,
	            		'first_name' => $fname,
	            		'last_name' => $lname,
	            		'user_login' => $email,
	            		'user_email' => $email,
	            		'display_name' => $fname);

	        		$user_id = wp_insert_user($userdata);

	        		$reg_key = md5($email.time());
	        		$user = array('user_id' => $user_id, 'level' => $access_levels, 'reg_key' => $reg_key, 'data' => $data);
					$new = ID_Member::add_ipn_user($user);
				}
				$order = new ID_Member_Order(null, (isset($user_id) ? $user_id : null), $product_id, null, $txn_id, null, 'active', $e_date, $price);
				$new_order = $order->add_order();
				if ($guest_checkout) {
					//fwrite($log, 'order added: '.$new_order."\n");
					do_action('idc_guest_checkout_order', $new_order, $customer);
				}
				else {
					do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
				}
			}
			do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : $user_id), $new_order, (isset($reg_key) ? $reg_key : null), $fields, 'paytm');
            /*if ($recurring) {
           		do_action('memberdeck_recurring_success', 'paypal', $user_id, $new_order, (isset($term_length) ? $term_length : null));
           	}*/
           	do_action('idmember_receipt', (isset($user_id) ? $user_id : ''), $price, $product_id, 'paytm', $new_order, $fields);
		}
	}

	function add_idcf_order($user_id, $order_id, $reg_key, $fields, $source) {
		if ($source !== 'paytm') {
			return;
		}
		if (empty($fields)) {
			// need params to continue
			return;
		}
		if (isset($fields['mdid_checkout'])) {
			$mdid_checkout = $fields['mdid_checkout'];
		}
		if (isset($fields['project_id'])) {
			$project_id = $fields['project_id'];
		}
		if (isset($fields['project_level'])) {
			$proj_level = $fields['project_level'];
		}
		if (!empty($project_id) && !empty($proj_level)) {
			$order = new ID_Member_Order($order_id);
			$order_info = $order->get_order();
			if (empty($order_info)) {
				return;
			}
			$user_id = $order_info->user_id;
			$user = get_user_by('id', $user_id);
			if (empty($user)) {
				return;
			}
			$created_at = $order_info->order_date;
			$pay_id = mdid_insert_payinfo($user->user_firstname, $user->user_lastname, $user->user_email, $project_id, $order_info->transaction_id, $proj_level, $order_info->price, $order_info->status, $created_at);
			if (isset($pay_id)) {
				$mdid_id = mdid_insert_order('', $pay_id, $order_id, null);
				do_action('id_payment_success', $pay_id);
			}
		}			
	}

}
new ID_Paytm();
?>