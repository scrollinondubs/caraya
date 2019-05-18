<?php
function google_ecommerce_scripts() {
	$property_code = get_transient('idc_ga_property_code');
	wp_register_script('google_ecommerce-script', plugins_url('js/google_ecommerce.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('google_ecommerce-script');
	wp_localize_script('google_ecommerce-script', 'idc_ga_property_code', $property_code);
}

function google_ecommerce_admin() {
	add_submenu_page('idf', __('Google Ecommerce', 'memberdeck'), __('Google Ecommerce', 'memberdeck'), 'manage_options', 'idc-google-ecommerce', 'google_ecommerce_menu');
}

function google_ecommerce_menu() {
	$property_code = get_transient('idc_ga_property_code');
	if (isset($_POST['idc_ga_property_code'])) {
		$property_code = sanitize_text_field($_POST['idc_ga_property_code']);
		set_transient('idc_ga_property_code', $property_code, 0);
	}
	include_once(dirname(__FILE__) . '/templates/admin/_googleEcommerceMenu.php');
}

function google_ecommerce_pay_triggers($user_id, $order_id, $paykey = '', $fields = 'fields', $source = '') {
	// send via Google Meaturement Protocol
	$source_list = array('paypal', 'pp-adaptive', 'coinbase');
	if (in_array($source, $source_list)) {
		$order = new ID_Member_Order($order_id);
		$order_details = $order->get_order();
		if (!empty($order_details)) {
			$level = ID_Member_Level::get_level($order_details->level_id);
			if (!empty($level)) {
				$user = get_user_by('id', $user_id);
				$args = array(
					'uniqid' => uniqid('idc_ga_'),
					'order_id' => $order_id,
					'user_id' => $user_id,
					'level' => $level,
					'user' => $user
				);
				wp_schedule_single_event(time(), 'google_ecommerce_trigger', array($args['uniqid'], $args['order_id'], $args['user_id'], $args['level'], $args['user']));
			}
		}
	}
}

function google_ecommerce_free_triggers($user_id, $order_id) {
	//do_action('memberdeck_free_success', $user_id, $new_order);
	if (empty($order_id)) {
		return;
	}
	$order = new ID_Member_Order($order_id);
	$order_details = $order->get_order();
	if (empty($order_details)) {
		return;
	}
	$level = ID_Member_Level::get_level($order_details->level_id);
	if (empty($level)) {
		return;
	}
	$user = get_user_by('id', $user_id);
	$args = array(
		'uniqid' => uniqid('idc_ga_'),
		'order_id' => $order_id,
		'user_id' => $user_id,
		'level' => $level,
		'user' => $user
	);
	wp_schedule_single_event(time(), 'google_ecommerce_trigger', array($args['uniqid'], $args['order_id'], $args['user_id'], $args['level'], $args['user']));
}

add_action('google_ecommerce_trigger', 'google_ecommerce_trigger', 10, 5);

function google_ecommerce_trigger($uniqid, $order_id, $user_id, $level, $user) {
	if (empty($order_id)) {
		return;
	}
	$order = new ID_Member_Order($order_id);
	$the_order = $order->get_order();
	if (empty($the_order)) {
		return;
	}
	$transaction_args = array(
		'method' => 'POST',
		'timeout' => 60,
		'httpversion' => '1.0',
		'sslverify' => false,
		'body' => array(
			'v' => 1,
			'tid' => get_transient('idc_ga_property_code'),
			'cid' => $uniqid,
			't' => 'transaction',
			'ti' => $the_order->transaction_id,
			'tr' => $the_order->price
		),
	);
	$item_args = array(
		'method' => 'POST',
		'timeout' => 60,
		'httpversion' => '1.0',
		'sslverify' => false,
		'body' => array(
			'v' => 1,
			'tid' => get_transient('idc_ga_property_code'),
			'cid' => $uniqid,
			't' => 'item',
			'ti' => $the_order->transaction_id,
			'in' => $level->level_name,
			'ip' => $the_order->price,
			'iq' => '1'
		),
	);
	$transaction_post = wp_remote_post('https://www.google-analytics.com/collect', $transaction_args);
	$item_post = wp_remote_post('https://www.google-analytics.com/collect', $item_args);
}

function google_ecommerce_order_data() {
	if (isset($_POST['Order'])) {
		$order_id = absint($_POST['Order']);
		if ($order_id > 0) {
			$order = new ID_Member_Order($order_id);
			$the_order = $order->get_order();
			if (!empty($the_order)) {
				$level_id = $the_order->level_id;
				$level = ID_Member_Level::get_level($level_id);
			}
		}
	}
	if (isset($_POST['User'])) {
		$user_id = absint($_POST['User']);
		if ($user_id > 0) {
			$user = get_user_by('id', $user_id);
		}
	}
	$data = array(
		'order' => (isset($the_order) ? $the_order : null),
		'user' => (isset($user) ? $user : null),
		'level' => (isset($level) ? $level : null),
	);
	print_r(json_encode($data));
	exit;
}
?>