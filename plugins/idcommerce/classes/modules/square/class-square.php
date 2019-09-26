<?php

class ID_Square {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('admin_menu', array($this, 'square_admin'), 12);
		if (self::settings_complete()) {
			add_action('init', array($this, 'square_init'));
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_filter('idc_localization_strings', array($this, 'localization_strings'), 999);
			add_action('idc_after_credit_card_selectors', array($this, 'square_checkout_selector'));
			add_action('md_purchase_extrafields', array($this, 'purchase_form'));
			add_filter('idc_display_zip', array($this, 'purchase_form_zip'));
			add_action('wp_ajax_id_square_submit', array($this, 'payment_request'));
			add_action('wp_ajax_nopriv_id_square_submit', array($this, 'payment_request'));
			add_filter('id_square_user_address', array($this, 'add_shipping_info'), 10, 2);
			global $crowdfunding;
			if ($crowdfunding) {
				add_filter('memberdeck_payment_success', array($this, 'add_idcf_order'), 3, 5);
				//add_filter('memberdeck_preauth_success', array($this, 'add_idcf_order'), 3, 5);
			}
		}
	}

	function settings_complete() {
		$settings = get_option('id_square_settings');
		return !empty($settings);
	}

	function get_settings() {
		return get_option('id_square_settings');
	}

	function get_access_token($square_settings = null) {
		$gateway_settings = get_option('memberdeck_gateways');
		if (empty($square_settings)) {
			$square_settings = self::get_settings();
		}
		if (empty($square_settings['access_token']) && empty($square_settings['sandbox_application_id'])) {
			return null;
		}
		$access_token = (isset($square_settings['access_token']) ? $square_settings['access_token'] : null);
		if (isset($gateway_settings['test']) && $gateway_settings['test']) {
			$access_token = (isset($square_settings['sandbox_access_token']) ? $square_settings['sandbox_access_token'] : null);
		}
		return $access_token;
	}

	function square_init() {
		wp_register_script('square_js', self::square_js_url(), array('jquery', 'idcommerce-js'));
		wp_register_script('id_square', plugins_url('js/id_square-min.js', __FILE__));
		wp_register_style('id_square', plugins_url('css/id_square-min.css', __FILE__));
		wp_register_script('square_admin_js', plugins_url('js/admin/id_square_admin-min.js', __FILE__));
		self::localize_scripts();
		self::filter_checkout_form();
	}

	function localize_scripts() {
		$square_settings = get_option('id_square_settings');
		$gateway_settings = get_option('memberdeck_gateways');
		$localization_array = array(
			'application_id' => '',
			'location_id' => ''
		);
		if (!empty($square_settings)) {
			$localization_array['location_id'] = $square_settings['location_id'];
			if (isset($gateway_settings['test']) && $gateway_settings['test']) {
				$localization_array['application_id'] = isset($square_settings['sandbox_application_id']) ? $square_settings['sandbox_application_id'] : '';
			}
			else {
				$localization_array['application_id'] = isset($square_settings['application_id']) ? $square_settings['application_id'] : '';
			}
		}
		wp_localize_script('id_square', 'id_square_vars', $localization_array);
	}

	function enqueue_scripts() {
		global $post;
		if (!empty($post)) {
			if (has_shortcode($post->post_content, 'idc_checkout') || has_shortcode($post->post_content, 'memberdeck_checkout') || has_shortcode($post->post_content, 'idc_dashboard') || has_shortcode($post->post_content, 'memberdeck_dashboard') || isset($_GET['mdid_checkout']) || isset($_GET['idc_renew']) || isset($_GET['idc_button_submit'])) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('square_js');
				wp_enqueue_script('id_square');
				wp_enqueue_style('id_square', plugins_url('js/id_square.css', __FILE__));
			}
		}
	}

	function square_admin() {
		$square_admin = add_submenu_page('idc', __('Square', 'memberdeck'), __('Square', 'memberdeck'), 'manage_options', 'idc-square', array($this, 'square_admin_menu'));
		add_action('admin_print_styles-'.$square_admin, array($this, 'admin_scripts'));
	}

	function square_admin_menu() {
		$settings = get_option('id_square_settings');
		if (isset($_POST['id_square_settings_submit'])) {
			foreach ($_POST as $k=>$v) {
				if ($k !== 'id_square_settings_submit') {
					$settings[$k] = sanitize_text_field($v);
				}
			}
			update_option('id_square_settings', $settings);
		}
		include_once(dirname(__FILE__) . '/' . 'templates/admin/_adminMenu.php');
	}

	function admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('square_admin_js');
	}

	function localization_strings($strings) {
		$strings['pay_with_square'] = __('Pay with Square', 'memberdeck');
		$strings['expiration'] = sprintf(__('Expiration (mm/yy) %1$s*%2$s', 'memberdeck'), ' <span class="starred">', '</span>');
		return $strings;
	}

	function filter_checkout_form() {
		add_filter('idc_checkout_card_number_misc', array($this, 'card_number_id'));
		add_filter('idc_checkout_card_cvc_misc', array($this, 'card_cvc_id'));
		add_filter('idc_checkout_card_expiry_month_misc', array($this, 'card_expiry_id'));
		add_filter('idc_checkout_zip_code_misc', array($this, 'zip_code_id'));
	}

	function card_number_id($id) {
		return 'id="sq-card-number"';
	}

	function card_cvc_id($id) {
		return 'id="sq-cvv"';
	}

	function card_expiry_id($id) {
		return 'id="sq-expiration-date"';
	}

	function zip_code_id($id) {
		return 'id="sq-postal-code"';
	}

	function square_checkout_selector($gateways) {
		$selector = '<div>';
		$selector .= '<a id="pay-with-square" class="pay_selector" href="#">';
        $selector .= '<i class="fa fa-credit-card-alt"></i>';
		$selector .= '<span>'.__('Square', 'memberdeck').'</span>';
		$selector .= '</a>';
		$selector .= '</div>';
		echo $selector;
	}

	function purchase_form() {
		$form = '<div id="square-hidden-input">';
		$form .= '<input type="hidden" class="sq-input" id="card-nonce" name="nonce" />';
		$form .= '</div>';
		echo $form;
	}

	function purchase_form_zip($show_zip) {
		return true;
	}

	function payment_request() {
		if (isset($_POST['nonce'])) {
			# Collect settings
			$settings = get_option('memberdeck_gateways');
			$square_settings = self::get_settings();
			# Square processing
			require (dirname(__FILE__) . '/' . 'lib/vendor/autoload.php');
			\SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken(self::get_access_token($square_settings));
			# IDC data
			global $crowdfunding;
			$type = 'purchase';
			$nonce = sanitize_text_field($_POST['nonce']);
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
			}
			$customer_id = get_user_meta($check_user->ID, 'square_customer_id', true);
			$customer_instance = new SquareConnect\Api\CustomersApi();
			if (empty($customer_id)) {
				// #devnote apply filter to add address
				$customer_body = array(
					'given_name' => (isset($check_user->user_firstname) ? $check_user->user_firstname : $check_user->display_name),
					'family_name' => (isset($check_user->user_lastname) ? $check_user->user_lastname : ''),
					'company_name' => $check_user->display_name,
					'nickname' => $check_user->display_name,
					'email_address' => $check_user->user_email,
					'reference_id' => '',
					'note' => ''
				);
				if (!empty(apply_filters('id_square_user_address', array(), $fields))) {
					$customer_body['address'] = apply_filters('id_square_user_address', array(), $fields);
				}
				try {
				    $customer_result = $customer_instance->createCustomer($customer_body);
				    //print_r($customer_result);
				} catch (\SquareConnect\ApiException $e) {
					// #devnote fail here
					$message = $e->getMessage().' '.__LINE__;
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
					exit;
				}
				try {
					$customer_model = $customer_result->getCustomer();
				} catch (\SquareConnect\ApiException $e) {
					// #devnote fail here
					$message = $e->getMessage().' '.__LINE__;
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
					exit;
				}
				try{
					$customer_id = $customer_model->getId();
					$customer_id = apply_filters('idc_customer_id_checkout', $customer_id, 'square', $check_user->ID, $fields);
					update_user_meta($check_user->ID, 'square_customer_id', $customer_id);
				} catch (\SquareConnect\ApiException $e) {
					// #devnote fail here
					$message = $e->getMessage().' '.__LINE__;
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
					exit;
				}
			}
			else {
				// #devnote may not need this, but pulling for model consistency
				$customer_id = apply_filters('idc_customer_id_checkout', $customer_id, 'square', $check_user->ID, $fields);
				$customer_model = $customer_instance->retrieveCustomer($customer_id);
			}
			// #devnote check if card already exists
			$customer_card_response = self::create_customer_card($customer_instance, $customer_id, $nonce);
			try {
				$customer_card_id = $customer_card_response->getCard()->getId();
			} catch (\SquareConnect\ApiException $e) {
				// #devnote fail here
				$message = $e->getMessage().' '.__LINE__;
				print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
				exit;
			}
			# For upgrade pathways
			$level_data->level_price = apply_filters( 'idc_checkout_level_price', $level_data->level_price, $product_id, ((!empty($check_user->ID)) ? $check_user->ID : ''), ((isset($ignore_upgrade)) ? $ignore_upgrade : false) );
			
			$transactions_api = new \SquareConnect\Api\TransactionsApi();
			// #devnote check for shipping info
			$transaction_body = array(
				//'card_nonce' => $nonce,
				'customer_id' => $customer_id,
				'buyer_email_address' => $check_user->user_email,
				'customer_card_id' => $customer_card_id,
				'amount_money' => array(
					'amount' => ($level_data->level_price*100),
					'currency' => (isset($square_settings['currency']) ? $square_settings['currency'] : 'USD'),
				),
				'idempotency_key' => uniqid()
			);
			$shipping_address = apply_filters('id_square_user_address', array(), $fields);
			if (!empty($shipping_address)) {
				$transaction_body['shipping_address'] = $shipping_address;
				$transaction_body['billing_address'] = $shipping_address;
			}
			try {
			    $result = $transactions_api->charge($square_settings['location_id'],  $transaction_body);
			} catch (\SquareConnect\ApiException $e) {
				// #devnote fail here
				$message = $e->getMessage().' '.__LINE__;
				print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
				exit;
			}
			$transaction = $result->getTransaction();
			$txn_id = $transaction->getId();

			// #devnote check transaction successful

			if ($level_data->level_type == 'lifetime') {
				$e_date = null;
			}
			else {
				$e_date = idc_set_order_edate($level_data);
			}

			$match_user = $member->match_user($check_user->ID);
			if (empty($match_user)) {
				# Insert IDC Member
				$idc_userdata = array('user_id' => $check_user->ID, 'level' => array($level_data->id), 'data' => array());
				$idc_user = ID_Member::add_user($idc_userdata);
			}
			else {
				$access_levels = maybe_unserialize($match_user->access_level);
				if (empty($access_levels)) {
					$access_levels = array($level_data->id);
				}
				else {
					$access_levels[] = $level_data->id;
					$access_levels = array_unique($access_levels);
				}
				$match_user->level = serialize($access_levels);
				$idc_user = ID_Member::update_user((array) $match_user);
			}
			$order = new ID_Member_Order(null, $check_user->ID, $level_data->id, null, $txn_id, '', 'active', $e_date, $level_data->level_price);
			$success = apply_filters('idc_checkout_success', true, $txn_id, 'square');
			if ($success) {
				if ($renewable) {
					// get proper edate #devnote move to hook
					$order->price = $level_data->renewal_price;
					$last_order = new ID_Member_Order(null, $check_user->ID, $level_data->id);
					$get_last_order = $last_order->get_last_order();
					if (isset($get_last_order)) {
						$lo_time = strtotime($get_last_order->e_date);
						$order->e_date = idc_set_order_edate($level_data, $lo_time);
					}
				}
				$new_order = $order->add_order();
				$paykey = md5($user_email.time());
				if (empty($check_user->ID)) {
					do_action('idc_guest_checkout_order', $new_order, $customer);
				}
				else if (is_multisite()) {
					// #devnote add to webhook handler?
					$blog_id = get_current_blog_id();
					add_user_to_blog($blog_id, $check_user->ID, 'subscriber');
				}
				MD_Keys::set_licenses($check_user->ID, $product_id);		
				do_action('memberdeck_payment_success', $check_user->ID, $new_order, $paykey, $fields, 'square');
				do_action('idmember_receipt', $check_user->ID, $order->price, $level_data->id, 'authorize.net', $new_order, $fields);
				do_action('memberdeck_square_success', $check_user->ID, $check_user->user_email);
				print_r(json_encode(array('response' => 'success', 'product' => $level_data->id, 'paykey' => $paykey, 'customer_id' => $customer_id, 'user_id' => $check_user->ID, 'order_id' => $new_order, 'type' => $type)));
				exit;
			}
					

	
		}
		exit;
	}

	function create_customer_card($customer_instance, $customer_id, $nonce) {
		// #devnote use this filter to add address
		$card_body = apply_filters('id_square_card_params',
			array(
				'card_nonce' => $nonce,
			)
		);
		$card_body = apply_filters('id_square_card_params', $card_body);
		try {
			$customer_card = $customer_instance->createCustomerCard($customer_id, $card_body);
		   // $result = $api_instance->createCustomerCard($customer_id, $body);
		} catch (\SquareConnect\ApiException $e) {
			// #devnote fail here
			$message = $e->getMessage().' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		return $customer_card;
	}

	function add_shipping_info($address_data, $fields) {
		$active_modules = ID_Modules::get_active_modules();
		$shipping_active = in_array('shipping_info', $active_modules);
		if ($shipping_active) {
			$shipping_defaults = array(
				'address' => '',
				'address_two' => '',
				'city' => '',
				'zip' => '',
				'country' => ''
			);
			$shipping_update = array();
			if (!empty($fields)) {
				foreach ($fields as $field) {
					if (array_key_exists($field['name'], $shipping_defaults)) {
						$shipping_update[$field['name']] = sanitize_text_field($field['value']);
					}
				}
			}
			$shipping_info = wp_parse_args($shipping_update, $shipping_defaults);
			$shipping_data = array(
				'address_line_1' => $shipping_info['address'],
				'address-line_2' => (!empty($shipping_info['address_two']) ? ' '.$shipping_info['address_two'] : ''),
				'locality' => $shipping_info['city'],
				'postal_code' => $shipping_info['zip'],
				'country' => $shipping_info['country'],
			);
			$address_data = array_merge($address_data, $shipping_data);
		}
		return $address_data;
	}

	function add_idcf_order($user_id, $order_id, $reg_key, $fields, $source) {
		if ($source !== 'square') {
			return;
		}
		if (empty($fields)) {
			// need params to continue
			return;
		}
		$cf_params = array('project_id', 'project_level');
		$get_params = new stdclass();
		foreach ($fields as $field) {
			if (in_array($field['name'], $cf_params)) {
				$get_params->{$field['name']} = sanitize_text_field($field['value']);
			}
		}
		if (empty($get_params->project_id) || empty($get_params->project_level)) {
			// cannot set cf data
			return;
		}
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (empty($the_order)) {
			// cannot add order until IDC order is added
			return;
		}
		$user = get_user_by('id', $the_order->user_id);
		$pay_id = mdid_insert_payinfo(
			isset($user->first_name) ? $user->first_name : '',
			isset($user->last_name) ? $user->last_name : '',
			isset($user->user_email) ? $user->user_email : '',
			$get_params->project_id,
			$the_order->transaction_id,
			$get_params->project_level,
			$the_order->price,
			'C',
			isset($the_order->order_date) ? $the_order->order_date : date(idf_date_format() . ' H:i:s')
		);
		if (!empty($pay_id)) {
			$mdid_id = mdid_insert_order(null, $pay_id, $order_id, null);
			do_action('id_payment_success', $pay_id);
		}
	}

	public static function square_js_url() {
		return 'https://js.squareup.com/v2/paymentform';
	}

}
new ID_Square();
?>