<?php

function shipping_info_scripts() {
	wp_register_script('shipping_info-script', plugins_url('js/shipping_info-min.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('shipping_info-script');
}

function idc_shipping_info_template(){
	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		if (!empty($user->ID)) {
			$address_info = get_user_meta($user->ID, 'md_shipping_info', true);
		}
	}
	$countries = file_get_contents(IDC_PATH . '/inc/countries_list.json');
	if (!empty($countries)) {
		$countries = json_decode($countries);
	}
	require_once 'templates/_shippingInfoForm.php';
}
function idc_save_shipping_info($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	global $crowdfunding;
	// Looping the extra fields posted from Checkout form
	if (!empty($fields)) {
		$address_info = get_user_meta($user_id, 'md_shipping_info', true);
		if (empty($address_info)) {
			$address_info = array(
				'address' => '',
				'address_two' => '',
				'city' => '',
				'state' => '',
				'zip' => '',
				'country' => ''
			);
		}
		//update_option('idc_shipping_fields', $fields);
		$bypass = false;
		foreach ($fields as $field) {
			// ajax
			if (is_array($field) && array_key_exists('name', $field)) {
				if (array_key_exists($field['name'], $address_info)) {
					$address_info[$field['name']] = sanitize_text_field(str_replace('%20', ' ', $field['value']));
					$bypass = true;
				}
			}
		}
		if (!$bypass) {
			// webhook
			foreach ($fields as $k=>$v) {
				if (array_key_exists($k, $address_info)) {
					$address_info[$k] = sanitize_text_field(str_replace('%20', ' ', $v));
				}
			}
		}

		// Adding the Address info to shipping info to show in Account tab
		if (!empty($address_info)) {
			update_user_meta($user_id, 'md_shipping_info', $address_info);
			do_action('idc_add_md_shipping_info', $user_id, $address_info);
		}

		// Adding address to order meta as well, in case needed
		ID_Member_Order::update_order_meta($order_id, 'shipping_info', $address_info);
		do_action('idc_add_shipping_info', $order_id, $address_info);
	}
}

function idc_schedule_idcf_shipping_update($pay_id) {
	wp_schedule_single_event(
		time() + 1,
		'idc_update_idcf_shipping',
		array(
			$pay_id,
		)
	);
}

function idc_update_idcf_shipping($pay_id){
	$mdid_order = mdid_payid_check($pay_id);
	if (empty($mdid_order)) {
		// no idc order to draw data from
		return;
	}
	$order_id = $mdid_order->order_id;
	$shipping_info = ID_Member_Order::get_order_meta($order_id, 'shipping_info');
	if (empty($shipping_info)) {
		// shipping meta is empty
		return;
	}
	$idcf_order = new ID_Order($pay_id);
	$order = $idcf_order->get_order();
	if (empty($order)) {
		// idcf order has not posted or is missing
		return;
	}
	// set address fields
	$address = (isset($shipping_info['address']) ? $shipping_info['address'] : '')." ".(isset($shipping_info['address_two']) ? $shipping_info['address_two'] : '');
	$country = (isset($shipping_info['country']) ? $shipping_info['country'] : '');
	$state = (isset($shipping_info['state']) ? $shipping_info['state'] : '');
	$city = (isset($shipping_info['city']) ? $shipping_info['city'] : '');
	$zip = (isset($shipping_info['zip']) ? $shipping_info['zip'] : '');
	// now update
	$update = new ID_Order(
		$pay_id,
		$order->first_name,
		$order->last_name,
		$order->email,
		$address,
		$country,
		$state,
		$city,
		$zip,
		$order->product_id,
		$order->transaction_id,
		$order->preapproval_key,
		$order->product_level,
		$order->prod_price,
		$order->status,
		$order->created_at
	);
	$update->update_order();
}
?>