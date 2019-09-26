<?php

function id_secupay_settings() {
	return get_option('id_secupay_settings');
}

function id_secupay_scripts() {
	wp_register_script('id_secupay', plugins_url('js/id_secupay-min.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('idcommerce-js');
	wp_enqueue_script('id_secupay');
}

function id_secupay_localization_strings($strings) {
	$strings['pay_with_prepay'] = __('Pay via Bank Transfer', 'memberdeck');
	$strings['pay_with_secupay'] = __('Pay with Secupay', 'memberdeck');
	return $strings;
}

function id_secupay_admin() {
	add_submenu_page('idc', __('Secupay', 'memberdeck'), __('Secupay', 'memberdeck'), 'manage_options', 'id-secupay', 'id_secupay_menu');
}

function id_secupay_query_handler() {
	$get_args = wp_parse_args($_GET);
	$post_args = wp_parse_args($_POST);
	if (array_key_exists('gateway-submit', $post_args)) {
		// saving gateway settings
		add_filter('idc_gateway_settings', 'id_secupay_gateway_toggle');
	}
}

function id_secupay_gateway_toggle($settings) {
	$post_args = wp_parse_args($_POST);
	$enable_secupay = isset($post_args['enable_secupay']) ? absint($post_args['enable_secupay']) : 0;
	$settings['enable_secupay'] = $enable_secupay;
	return $settings;
}

function id_secupay_admin_toggle() {
	$settings = maybe_unserialize(get_option('memberdeck_gateways'));
	$enable_secupay = isset($settings['enable_secupay']) ? $settings['enable_secupay'] : 0;
	$output = '<div class="form-input inline">';
	$output .= '<input type="checkbox" name="enable_secupay" id="enable_secupay" value="1" class="cc-gateway-chkbox" '.(isset($enable_secupay) && $enable_secupay ? 'checked="checked"' : '').'/>';
	$output .= '<label for="enable_secupay">'.__('Enable Secupay', 'memberdeck').'</label>';
	$output .= '</div>';
	echo $output;
}

function id_secupay_menu() {
	$settings = get_option('id_secupay_settings');
	if (isset($_POST['submit_secupay_settings'])) {
		$settings = array();
		foreach ($_POST as $k=>$v) {
			if ($k !== 'submit_secupay_settings') {
				$settings[$k] = sanitize_text_field($v);
			}
		}
		$settings = apply_filters('id_secupay_settings', $settings);
		update_option('id_secupay_settings', $settings);
	}
	$fields = array(
		'api_key' => array(
			'label' => __('API Key', 'memberdeck'),
			'name' => 'api_key',
			'id' => 'api_key',
			'class' => 'form-row third left',
			'type' => 'text',
			'value' => (isset($settings['api_key']) ? $settings['api_key'] : '')
		),
		'submit_secupay_settings' => array(
			'name' => 'submit_secupay_settings',
			'id' => 'submit_secupay_settings',
			'class' => 'form-row third left button button-primary',
			'type' => 'submit',
			'value' => __('Save', 'memberdeck')
		)
	);
	$form = new MD_Form($fields);
	$output = '<form name="secupay_settings" id="secupay_settings" action="" method="POST">';
	$output .= $form->build_form();
	$output .= '</form>';
	include_once('templates/admin/_secupaySettings.php');
}

function id_secupay_checkout_selector() {
	echo
	'<div><a id="pay-with-prepay" class="pay_selector" href="">
    	<i class="fa fa-bank"></i>
		<span>'.__('Bank Transfer', 'memberdeck').'</span>
	</a></div>';
	echo
	'<div><a id="pay-with-secupay" class="pay_selector" href="">
    	<i class="fa fa-credit-card-alt"></i>
		<span>'.__('Direct Debit', 'memberdeck').'</span>
	</a></div>';
	echo
	'<div><a id="pay-with-secupaycc" class="pay_selector" href="">
    	<i class="fa fa-credit-card"></i>
		<span>'.__('Credit Card', 'memberdeck').'</span>
	</a></div>';
}

function id_secupay_checkout_actions($product_id) {
	add_filter('id_secupay_checkout_text', 'id_secupay_preauth_text');
}

function id_secupay_preauth_text($text) {
	$text = __('Your card will be authorized in the amount of ', 'memberdeck');
	return $text;
}

function id_secupay_checkout_descriptions($content, $level, $level_price, $user_data, $gateways, $general, $credit_value) {
	ob_start();
	include_once 'templates/_checkoutSecupayDescription.php';
	$secupay_description = ob_get_contents();
	ob_end_clean();
	$content .= apply_filters('id_secupay_checkout_description', $secupay_description, $level, $level_price, $user_data, $gateways, $general);

	ob_start();
	include_once 'templates/_checkoutPrepayDescription.php';
	$prepay_description = ob_get_contents();
	ob_end_clean();
	$content .= apply_filters('id_prepay_checkout_description', $prepay_description, $level, $level_price, $user_data, $gateways, $general);

	return $content;
}

function id_secupay_payment_action($status, $level) {
	switch ($level->txn_type) {
		case 'capture':
			$status = 'sale';
			break;
		
		case 'preauth':
			$status = 'authorization';
			break;

		default:
			$status = $status;
			break;
	}
	return $status;
}

function id_secupay_demo_check() {
	$gateways = maybe_unserialize(get_option('memberdeck_gateways'));
	$test = (isset($gateways['test']) ? $gateways['test'] : 0);
	return $test;
}

function id_secupay_submit() {
	$status = 'failure';
	$message = __('Payment gateway not recognized', 'memberdeck').':'.__LINE__;
	if (isset($_POST['submit_name'])) {
		$message = __('Invalid customer data', 'memberdeck').':'.__LINE__;
		if (!empty($_POST['customer'])) {
			$message = __('Invalid product data', 'memberdeck').':'.__LINE__;
			$customer = $_POST['customer'];
			$level = ID_Member_Level::get_level(absint($customer['product_id']));
			if (!empty($level)) {
				$message = __('Gateway error', 'memberdeck').':'.__LINE__;
				$prefix = idf_get_querystring_prefix();
				$price = $level->level_price;
				if (isset($_POST['pwyw_price']) && $_POST['pwyw_price'] > $price) {
					$price = floatval($_POST['pwyw_price']);
				}
				$amount = $price * 100;
				require_once('lib/secupay-php/secupay_api.php');
				$settings = id_secupay_settings();
				$api_key = (isset($settings['api_key']) ? $settings['api_key'] : '');
				try {
					$request_data = array("apikey" => $api_key, "apiversion" => secupay_api::get_api_version());
				}
				catch (Exception $e) {
					$message = __('Error requesting API version', 'memberdeck').':'.__LINE__;
				}
				if (!empty($request_data)) {
					// #devote push to default data method
					$query_array = array(
						'email' => $customer['email'],
						'product_id' => absint($customer['product_id']),
						'price' => $price,
					);
					$querystring = http_build_query($query_array);
					if (!empty($_POST['fields'])) {
						$fields = $_POST['fields'];
						foreach ($_POST['fields'] as $field) {
							if (is_array($field)) {
								$querystring .= '&'.$field['name'].'='.$field['value'];
							}
						}
					}
					$querystring = str_replace('-', '%2D', $querystring); // secupay does not allow hyphens in url args
					$current_url = (isset($_POST['current_url']) ? sanitize_text_field($_POST['current_url']) : '');
					$default_data = array(
						'demo' => apply_filters('id_secupay_demo_mode', 1),
						'payment_action' => apply_filters('id_secupay_payment_action', 'sale', $level),
						'url_success' => md_get_durl().$prefix.'secupay_success=1'. '&' .$querystring,
						'url_failure' => $current_url,
						'url_push' => home_url('/').$prefix.'memberdeck_notify=secupay' . '&' . $querystring,
						'amount' => $amount,
						'firstname' => (isset($customer['first_name']) ? sanitize_text_field($customer['first_name']) : ''),
						'lastname' => (isset($customer['last_name']) ? sanitize_text_field($customer['last_name']) : ''),
						'email' => $customer['email'],
						'purpose' => $level->level_name,
					);
					$submit_name = sanitize_text_field($_POST['submit_name']);
					if (!empty($submit_name)) {
						switch ($submit_name) {
							case 'submitPaymentPrepay':
								$sale_data = array(
									'payment_type' => 'prepay',
								);
								break;
							
							case 'submitPaymentSecupay':
								$sale_data = array(
									'payment_type' => 'debit',
								);
								break;
							case 'submitPaymentSecupaycc':
								$sale_data = array(
									'payment_type' => 'creditcard',
								);
						}
						$request_data = apply_filters('id_secupay_request_data', array_merge($request_data, $default_data, $sale_data), $fields);
						$api_return = (object) ID_Secupay::id_secupay_request_handler($request_data);
						if (!empty($api_return->status)) {
							$status = $api_return->status;
							$message = $api_return->data;
						}
						else {
							$message = __('Error processing request', 'memberdeck').':'.__LINE__;
						}
					}
				}
			}
		}
	}
	print_r(json_encode(array('status' => $status, 'message' => $message)));
	exit;
}

function id_secupay_shipping_info($data, $fields) {
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
		foreach ($fields as $field) {
			if (array_key_exists($field['name'], $shipping_defaults)) {
				// secupay wants decoded values
				$shipping_update[$field['name']] = urldecode($field['value']);
			}
		}
		$shipping_info = wp_parse_args($shipping_update, $shipping_defaults);
		$shipping_data = array(
			'street' => $shipping_info['address'].(!empty($shipping_info['address_two']) ? ' '.$shipping_info['address_two'] : ''),
			'city' => $shipping_info['city'],
			'zip' => $shipping_info['zip'],
			'country' => $shipping_info['country'],
			'delivery_address' => array(
				'street' => $shipping_info['address'].(!empty($shipping_info['address_two']) ? ' '.$shipping_info['address_two'] : ''),
				'city' => $shipping_info['city'],
				'zip' => $shipping_info['zip'],
				'country' => $shipping_info['country'],
			),
		);
		$data = array_merge($data, $shipping_data);
	}
	return $data;
}

function id_secupay_webhook_handler() {
	if (isset($_GET['memberdeck_notify'])) {
		//update_option('secupay_get', $_GET);
		//update_option('secupay_post', $_POST);
		global $crowdfunding;
		$preauth = false;
		$source = sanitize_text_field($_GET['memberdeck_notify']);
		switch ($source) {
			case 'secupay':
				$get_params = new stdclass;
				$required_params = array('email', 'product_id', 'mdid_checkout', 'price');
				foreach ($_GET as $k=>$v) {
					if (in_array($k, $required_params)) {
						$get_params->$k = sanitize_text_field($v);
					}
				}
				$get_params = apply_filters('id_secupay_get_params', $get_params, $_GET);
				if (empty($get_params->email) || empty($get_params->product_id) || empty($get_params->mdid_checkout) || empty($get_params->price)) {
					// incomplete request
					return;
				}
				$post_params = new stdclass;
				foreach ($_POST as $k=>$v) {
					$post_params->$k = sanitize_text_field($v);
				}
				if (empty($post_params->payment_status)) {
					// need status to continue
					return;
				}
				//update_option('secupay_post_params', $post_params);
				$status_array = array('accepted', 'authorized');
				if (in_array($post_params->payment_status, $status_array)) {
					/*
					1. Does the user exist? If not, insert
					2. Does the order exist? If not, insert
					3. Crowdfunding?
					*/
					$request_data = array(
						'apikey' => $post_params->apikey,
						'hash' => $post_params->hash
					);
					$api_return = (object) ID_Secupay::id_secupay_request_handler($request_data, 'status');
					//update_option('secupay_pre_api_return', $api_return);
					if ($api_return->status !== 'ok') {
						// there was an error with the payment
						return;
					}
					update_option('secupay_api_return', $api_return);
					$member = new ID_Member();
					$check_member = $member->check_user($get_params->email);
					if (empty($check_member)) {
						$user_params = array(
							'user_email' => $get_params->email,
							'user_login' => $get_params->email,
							'user_pass' => idmember_pw_gen(),
							'first_name' => (isset($get_params->first_name) ? $get_params->first_name : ''),
							'last_name' => (isset($get_params->last_name) ? $get_params->last_name : ''),
							'display_name' => (!empty($get_params->first_name) ? $get_params->first_name : uniqid('user_'))
						);
						$user_id = wp_insert_user($user_params);
						if (is_wp_error($user_id)) {
							// could not create user
							return;
						}
						$reg_key = md5($get_params->email.time());
					}
					else {
						$user_id = $check_member->ID;
					}
					// we have user info, now lets get product info
					$level = ID_Member_Level::get_level($get_params->product_id);
					if (empty($level)) {
						// can't add order, but at least we have a user id
						return;
					}

					switch ($level->level_type) {
						case 'lifetime':
							$e_date = null;
							break;
						
						default:
							$exp = strtotime('+1 years');
							$e_date = date('Y-m-d H:i:s', $exp);
							break;
					}
					$txn_check = ID_Member_Order::check_order_exists($api_return->data->trans_id);
					if (empty($txn_check)) {
						if ($post_params->payment_status == 'authorized') {
							$preauth = true;
						}
						// work with user data
						$access_levels = array(absint($get_params->product_id));
						$data = array('apikey' => $post_params->apikey);
						$match_user = $member->match_user($user_id);
						if (empty($match_user)) {
							// does not exist in idc, lets add them
							$member_params = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
							$new = ID_Member::add_user($member_params);
						}
						else {
							// lets merge data
							if (isset($match_user->access_level)) {
		            			$levels = maybe_unserialize($match_user->access_level);
		            			if (!empty($levels)) {
			            			foreach ($levels as $key['val']) {
										$access_levels[] = absint($key['val']);
									}
								}
		            		}
		            		if (!empty($match_user->data)) {
		            			$old_data = unserialize($match_user->data);
		            			if (is_array($old_data)) {
		            				$data = array_merge($data, $old_data);
		            			}
		            		}
		            		$member_params = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
							$update = ID_Member::update_user($member_params);
						}
						// time to add order
						$order = new ID_Member_Order(null, $user_id, $get_params->product_id, null, ($preauth ? 'pre' : $api_return->data->trans_id), null, 'active', $e_date, $get_params->price);
						$new_order = $order->add_order();
						if ($preauth) {
							$preorder_entry = ID_Member_Order::add_preorder($new_order, $api_return->data->hash, 'secupay');
						}
						
					}
					if (isset($reg_key)) {
						do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
					}
					else {
						$reg_key = '';
					}
					if ($preauth) {
						do_action('memberdeck_preauth_success', $user_id, $new_order, $reg_key, $_GET, 'secupay');
						do_action('memberdeck_preauth_receipt', $user_id, $get_params->price, $get_params->product_id, 'secupay', $new_order);
					}
					else if (!empty($new_order)) {
						// we have a new order and not a duplicate webhook
						do_action('memberdeck_payment_success', $user_id, $new_order, $reg_key, $_GET, 'secupay');
						do_action('idmember_receipt', $user_id, $get_params->price, $get_params->product_id, 'secupay', $new_order, $_GET);
					}
				}
				break;
			
			default:
				break;
		}
	}
	return;
}

function id_secupay_order_currency($currency_code, $global_currency, $source) {
	if (!empty($source) && $source == 'secupay') {
		return 'EUR';
	}
}

function id_secupay_process_preauth($level_id) {
	$preorders = ID_Member_Order::get_md_preorders($level_id);
	$success = array();
	$fail = array();
	$response = array();
	if (!empty($preorders)) {
		$level = ID_Member_Level::get_level($level_id);
		$price = $level->level_price;
	}
	foreach ($preorders as $capture) {
		$user_id = $capture->user_id;
		$userdata = get_userdata($user_id);
		$email = (isset($userdata->user_email) ? $userdata->user_email : '');
		$pre_info = ID_Member_Order::get_preorder_by_orderid($capture->id);
		if (empty($pre_info)) {
			// no pre-order data
			continue;
		}
		$order_id = $pre_info->order_id;
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (empty($the_order)) {
			// need order to get order data
			continue;
		}
		$gateway = $pre_info->gateway;
		if (!empty($gateway) && $gateway == 'secupay') {
			if (empty($pre_info->charge_token)) {
				// need this to process the transaction
				continue;
			}
			$secupay_settings = id_secupay_settings();
			if (empty($secupay_settings)) {
				// cannot retrieve API key
				continue;
			}
			$request_data = array(
				'apikey' => $secupay_settings['api_key'],
				'hash' => $pre_info->charge_token
			);
			$status_check = (object) ID_Secupay::id_secupay_request_handler($request_data, 'status');
			//print_r(json_encode($status_check));
			if (empty($status_check) || $status_check->status !== 'ok') {
				// ok means we can proceed
				continue;
			}

			if (empty($status_check->data) || !in_array($status_check->data->status, array('authorized', 'accepted'))) {
				// may have been processed or canceled
				continue;
			}

			if ($status_check->data->hash !== $pre_info->charge_token) {
				// hash does not match
				continue;
			}

			// we now have object with status, created (yyyy-mm-dd hh:mm:ss), demo, trans_id, amount, currency, opt vars
			$process_preauth = (object) ID_Secupay::id_secupay_request_handler($request_data, 'capture');

			if (empty($process_preauth) || $process_preauth->status !== 'ok') {
				// could not be completed
				continue;
			}

			$preauth_data = (object) array(
				'paid' => 1,
				'refunded' => 0,
				'txn_id' => $process_preauth->data->trans_id,
				'error' => __('Transaction could not be captured', 'memberdeck').': '.__LINE__,
			);

			add_filter('idc_preauth_data_'.$pre_info->charge_token, function() use ($preauth_data) {
				return $preauth_data;
			});
		}
	}
}

function id_secupay_idcf_order($user_id, $order_id, $reg_key, $fields, $source) {
	if ($source !== 'secupay') {
		// only 
		return;
	}
	if (empty($fields)) {
		// need params to continue
		return;
	}
	$cf_params = array('project_id', 'project_level');
	$get_params = new stdclass();
	foreach ($fields as $k=>$v) {
		if (in_array($k, $cf_params)) {
			$get_params->$k = sanitize_text_field($v);
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
		isset($the_order->order_date) ? $the_order->order_date : date('Y-m-d H:i:s')
	);
	if (!empty($pay_id)) {
		$mdid_id = mdid_insert_order(null, $pay_id, $order_id, null);
		do_action('id_payment_success', $pay_id);
	}
}
?>