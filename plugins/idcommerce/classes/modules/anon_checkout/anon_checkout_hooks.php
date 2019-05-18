<?php

function anon_checkout_scripts() {
	wp_register_style('anon_checkout-style', plugins_url('css/anon_checkout.css', __FILE__));
	wp_register_script('anon_checkout-script', plugins_url('js/anon_checkout.js', __FILE__));
	wp_enqueue_style('anon_checkout-style');
	wp_enqueue_script('jquery');
	wp_enqueue_script('anon_checkout-script');
}

function idc_anon_checkout_template() {
	require_once 'templates/_anonCheckoutForm.php';
}

function idc_save_anon_checkout_selections($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	if (!empty($fields)) {
		// default to 0 for normal (not anonymous)
		$anonymous_checkout = 0;
		if (isset($fields['anonymous_checkout'])) {
			// when in php $_GET via IPN
			$anonymous_checkout = $fields['anonymous_checkout'];
		}
		else {
			// when in javascript
			foreach ($fields as $field) {
				if ($field['name'] == "anonymous_checkout") {
					$anonymous_checkout = $field['value'];
					break;
				}
			}
		}

		ID_Member_Order::update_order_meta($order_id, 'anonymous_checkout', $anonymous_checkout);
		$comments = '';
		if (isset($fields['idc_checkout_comments'])) {
			// when in php $_GET via IPN
			$comment = str_replace('%20', ' ', $fields['idc_checkout_comments']);
			$comments = sanitize_text_field($comment);
		}
		else {
			// when in javascript
			foreach ($fields as $field) {
				if ($field['name'] == "idc_checkout_comments") {
					$comment = str_replace('%20', ' ', $field['value']);
					$comments = sanitize_text_field($comment);
					break;
				}
			}
		}

		if (!empty($comments)) {
			// Adding Comments to Order meta
			ID_Member_Order::update_order_meta($order_id, 'idc_checkout_comments', $comments);
		}
	}
}

function idc_anon_checkout_comments($name, $idc_order_id, $level_id) {
	$backer_comment = ID_Member_Order::get_order_meta($idc_order_id, 'idc_checkout_comments', true);
	if(empty($backer_comment)) {
		return;
	}
	$name = ' <span class="checkout-comment">'.$backer_comment.'</span>';
	return $name;
}

function idc_anon_checkout_name($display_name, $order_id, $user_id){
	$anonymous_checkout = ID_Member_Order::get_order_meta($order_id, 'anonymous_checkout', true);
	if (empty($anonymous_checkout)) {
		return $display_name;
	}
	return __('Anonymous', 'memberdeck');
}

function idc_anon_checkout_link($user_info, $idc_order_id) {
	$anonymous_checkout = ID_Member_Order::get_order_meta($idc_order_id, 'anonymous_checkout', true);

	// If User has set to anonymous during checkout
	if ($anonymous_checkout) {
		$array = (array) $user_info;
		unset($array['ID']);
		$user_info = (object) $array;
	}
	return $user_info;
}

function idc_anon_checkout_avatar($user_id, $idc_order_id) {
	$anonymous_checkout = ID_Member_Order::get_order_meta($idc_order_id, 'anonymous_checkout', true);
	if ($anonymous_checkout) {
		return null;
	}
	return $user_id;
}

?>