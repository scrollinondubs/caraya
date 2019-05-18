<?php

add_action('init', 'idf_verify_platform', 1);

function idf_verify_platform() {
	$platform = idf_platform();

	if ($platform !== 'legacy') {
		remove_shortcode('project_purchase_form');
	}

	if ($platform == 'wc' && class_exists('WooCommerce')) {
		add_action('add_meta_boxes', 'idwc_project_pairing');
		add_action('wp', 'idwc_level_links');
		add_action('wp', 'idwc_project_redirect');
		add_action('woocommerce_order_status_changed', 'idwc_insert_order', 1, 3);
		add_action('before_delete_post', 'idwc_delete_order', 1, 1);
	}
	else if ($platform == 'edd' && class_exists('Easy_Digital_Downloads')) {
		add_action( 'add_meta_boxes', 'idedd_project_pairing');
		remove_shortcode('project_purchase_form');
		add_action('wp', 'idedd_level_links');
		add_action('wp', 'idedd_project_redirect');
		add_shortcode('project_purchase_form', 'idedd_swap_forms', 1);
		add_action('edd_insert_payment', 'idedd_insert_order', 5, 2);
		add_action('edd_update_edited_purchase', 'idedd_update_order', 5, 1);
		add_action('edd_complete_purchase', 'idedd_complete_order', 5, 1);
		add_action('before_delete_post', 'idedd_delete_order', 5, 1);
		// can we de-register the scripts and links from the template?
	}
	// now we load the general functions that apply to all frameworks
	add_action('id_widget_after', 'idcf_level_select_lb', 10, 2);
}

/*
IDF WC Integration
1. Update orders
*/



function idwc_project_pairing() {
	add_meta_box("idwc_project_pairing", __("WooCommerce Shortcode", "ignitiondeck"), "set_idwc_project_pairing", "ignition_product", "side", "default");
}

function set_idwc_project_pairing($post) {
	// Add an nonce field so we can check for it later.
  	wp_nonce_field( 'set_idwc_project_pairing', 'set_idwc_project_pairing_nonce' );
  	$value = get_post_meta($post->ID, 'idwc_project_pairing', true);
	$fields = array(
		array(
			'before' => '<p>'.__('If matching IgnitionDeck project to WooCommerce products, please enter the product ID here.', 'ignitiondeck').__('Otherwise, leave this field blank.', 'ignitiondeck').'</p>',
			'label' => __('Product ID', 'ignitiondeck'),
			'value' => (isset($value) ? $value : ''),
			'name' => 'idwc_project_pairing',
			'type' => 'number'
		)
	);
	$form = new ID_Form($fields);
	echo apply_filters('idwc_project_pairing_form', $form->build_form());
}

add_action('save_post', 'save_idwc_project_pairing');

function save_idwc_project_pairing($post_id) {
	if (!isset($_POST['set_idwc_project_pairing_nonce'])) {
		return $post_id;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
	$value = esc_attr($_POST['idwc_project_pairing']);
	update_post_meta($post_id, 'idwc_project_pairing', $value);
	$project_id = get_post_meta($post_id, 'ign_project_id', true);
	update_post_meta($value, '_wc_project_pairing', $project_id);
}

function remove_id_purchaseform() {
	return null;
}

function idwc_level_links() {
	$project_id = is_id_project();
	if (!empty($project_id)) {
		$project = new ID_Project($project_id);
		$post_id = $project->get_project_postid();
		$wc_product = get_post_meta($post_id, 'idwc_project_pairing', true);
		if (!empty($wc_product)) {
			$wc_checkout_url = wc_get_checkout_url();
			$checkout_url = $wc_checkout_url.'?add-to-cart=';
			$level_count = get_post_meta($post_id, 'ign_product_level_count', true);
			for ($i = 0; $i <= $level_count - 1; $i++) {
				$level_id = $i + 1;
				add_filter('id_level_'.$level_id.'_link', function($link, $project_id) use ($checkout_url, $post_id, $wc_product, $i) {
					$product = new WC_Product_Variable($wc_product);
					if (!empty($product)) {
						if (!isset($variations)) {
							$variation_array = array();
							$variations = $product->get_available_variations();
							usort($variations, 'sort_idwc_level_links');
							$variations = array_reverse($variations);
							if (!empty($variations[$i])) {
								$current_var = $variations[$i];
								$variation_id = $current_var['variation_id'];
								if (!empty($current_var['attributes'])) {
									$attributes_slug = key($current_var['attributes']);
									$attributes_value = $current_var['attributes'][$attributes_slug];
									$checkout_url = $checkout_url.$wc_product.'&'.$attributes_slug.'='.$attributes_value.'&variation_id=';
								}
								if (!empty($variation_id)) {
									$checkout_url = $checkout_url.$variation_id;
								}
							}
						}
					}
					return $checkout_url;
				} , 1, 2);
			}
		}
	}
}

function sort_idwc_level_links($a, $b) {
	return $a['variation_id'] - $b['variation_id'];
}

function idwc_project_redirect() {
	global $post;
	if (isset($post) && $post->post_type == 'product' && is_singular()) {
		$post_id = $post->ID;
		$project_id = get_post_meta($post_id, '_wc_project_pairing', true);
		if (!empty($project_id)) {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			if (!empty($post_id)) {
				$url = get_permalink($post_id);
				header('Location: '.$url);
			}
		}
	}
}

function idwc_insert_order($order_id, $old_status, $new_status) {
	// we need to run this on status update, edit, and delete
	// this used EDD payment_id as txn_id rather than gateway txn_id
	$items = idwc_order_items($order_id);
	$id_orders = array();
	foreach ($items as $item) {
		$qty = $item['qty'];
		for ($i = 1; $i <= $qty; $i++) {
			$vars = idwc_payment_vars($item, $new_status, $order_id, $i);
			$txn_id = $vars['transaction_id'];
			$existing_txn = ID_Order::get_order_by_txn($txn_id);
			if (empty($existing_txn)) {
				$rc = new ReflectionClass('ID_Order');
				$order = $rc->newInstanceArgs($vars);
				$pay_id = $order->insert_order();
				$id_orders[] = $pay_id;
			}
			else {
				if ($existing_txn->transaction_id !== $new_status) {
					$vars['id'] = $existing_txn->id;
					$rc = new ReflectionClass('ID_Order');
					$order = $rc->newInstanceArgs($vars);
					$update = $order->update_order();
				}
			}
		}
		update_post_meta($order_id, '_wc_order_pairing', $id_orders);
	}
}

function idwc_order_items($order_id) {
	$items = array();
	$order = new WC_Order($order_id);
	if (!empty($order)) {
		$items = $order->get_items();
	}
	return $items;
}

function idwc_payment_vars($item, $status, $order_id, $qty_num) {
	$order = new WC_Order($order_id);
	if (isset($item['product_id'])) {
		$product_id = $item['product_id'];
	}
	if (isset($item['variation_id'])) {
		$variation_id = $item['variation_id'];
	}
	if (isset($product_id)) {
		$project_id = get_post_meta($product_id, '_wc_project_pairing', true);
		if (isset($project_id)) {
			$product = new WC_Product_Variable($product_id);
			if (!empty($product)) {
				$variations = $product->get_available_variations();
				usort($variations, 'sort_idwc_level_links');
				$variations = array_reverse($variations);
				$v_array = array();
				foreach ($variations as $variant) {
					$v_array[] = $variant['variation_id'];
				}
				$level = array_search($variation_id, $v_array) + 1;
			}

			$first_name = get_post_meta($order_id, '_billing_first_name', true);
			$last_name = get_post_meta($order_id, '_billing_last_name', true);
			$email = get_post_meta($order_id, '_billing_email', true);
			$address = get_post_meta($order_id, '_billing_address_1', true);
			$city = get_post_meta($order_id, '_billing_city', true);
			$state = get_post_meta($order_id, '_billing_state', true);
			$zip = get_post_meta($order_id, '_billing_postcode', true);
			$country = get_post_meta($order_id, '_billing_country', true);
			$transaction_id = get_post_meta($order_id, '_order_key', true);
			$price = get_post_meta($variation_id, '_price', true);
			$date = $order->order_date;

			$vars = array(
				'id' => null,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'email' => $email,
				'address' => $address,
				'state' => $state,
				'city' => $city,
				'zip' => $zip,
				'country' => $country,
				'product_id' => $project_id,
				'transaction_id' => $transaction_id.'-v'.$variation_id.'-'.$qty_num,
				'preapproval_key' => '',
				'product_level' => $level,
				'prod_price' => $price,
				'status' => $status,
				'created_at' => $date
			);
		}
	}
	return (isset($vars) ? $vars : array());
}

function idwc_delete_order($post_id) {
	$orders = get_post_meta($post_id, '_wc_order_pairing', true);
	if (!empty($orders)) {
		foreach ($orders as $order) {
		//$order = ID_Order::get_order_by_txn($payment_id);
			//if (!empty($order)) {
				//$pay_id = $order->id;
				$delete = ID_Order::delete_order($order);
			//}
		}
	}
}

function idedd_project_pairing() {
	add_meta_box("idedd_project_pairing", __("EDD Download ID", "ignitiondeck"), "set_idedd_project_pairing", "ignition_product", "side", "default");
}

function set_idedd_project_pairing($post) {
	// Add an nonce field so we can check for it later.
  	wp_nonce_field( 'set_idedd_project_pairing', 'set_idedd_project_pairing_nonce' );
  	$value = get_post_meta($post->ID, 'idedd_project_pairing', true);
	$fields = array(
		array(
			'before' => '<p>'.__('If matching IgnitionDeck project to Easy Digital Downloads, please enter the download ID here.', 'ignitiondeck').__('Otherwise, leave this field blank.', 'ignitiondeck').'</p>',
			'label' => __('Download ID', 'ignitiondeck'),
			'value' => (isset($value) ? $value : ''),
			'name' => 'idedd_project_pairing',
			'type' => 'number'
		)
	);
	$form = new ID_Form($fields);
	echo apply_filters('idedd_project_pairing_form', $form->build_form());
}

add_action('save_post', 'save_idedd_project_pairing');

function save_idedd_project_pairing($post_id) {
	if (!isset($_POST['set_idedd_project_pairing_nonce'])) {
		return $post_id;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}
	$value = esc_attr($_POST['idedd_project_pairing']);
	update_post_meta($post_id, 'idedd_project_pairing', $value);
	$project_id = get_post_meta($post_id, 'ign_project_id', true);
	update_post_meta($value, '_edd_project_pairing', $project_id);
}

function idedd_level_links() {
	$project_id = is_id_project();
	if (!empty($project_id)) {
		$project = new ID_Project($project_id);
		$post_id = $project->get_project_postid();
		$level_count = get_post_meta($post_id, 'ign_product_level_count', true);
		$edd = get_post_meta($post_id, 'idedd_project_pairing', true);
		if (!empty($edd)) {
			$edd_prices = edd_get_variable_prices($edd);
			$checkout_url = edd_get_checkout_uri();
			$start_value = (!empty($edd_prices[0]) ? 0 : 1);
			if ($start_value == 0) {
				$level_count = absint($level_count - 1);
			}
			for ($i = $start_value; $i <= $level_count; $i++) {
				$level_id = ($start_value == 0 ? absint($i + 1) : $i);
				add_filter('id_level_'.$level_id.'_link', function($link, $project_id) use ($checkout_url, $edd, $i) {
					// start here
					$link = $checkout_url.'?edd_action=add_to_cart&download_id='.$edd.'&edd_options[price_id]='.$i;
					return $link;
				} , 1, 2);
			}
		}
	}
}

function idedd_project_redirect() {
	global $post;
	if (isset($post) && $post->post_type == 'download' && is_singular()) {
		$post_id = $post->ID;
		$project_id = get_post_meta($post_id, '_edd_project_pairing', true);
		if (!empty($project_id)) {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			if (!empty($post_id)) {
				$url = get_permalink($post_id);
				header('Location: '.$url);
			}
		}
	}
}

function idedd_swap_forms($attrs) {
	if (isset($attrs['product'])) {
		$project_id = absint($attrs['product']);
	}
	if (isset($_GET['prodid'])) {
		$project_id = absint($_GET['prodid']);	
	}
	if (isset($project_id) && $project_id > 0) {
		$project = new ID_Project($project_id);
		$post_id = $project->get_project_postid();
		if (isset($post_id) && $post_id > 0) {
			$download_id = get_post_meta($post_id, 'idedd_project_pairing', true);
			if (!empty($download_id) && $download_id > 0) {
				$text = __('Checkout', 'ignitiondeck');
				return do_shortcode('[purchase_link id="'.$download_id.'" text="'.$text.'"]');
			}
		}
	}
	return;
}

function idedd_insert_order($payment_id) {
	// we need to run this on status update, edit, and delete
	// this used EDD payment_id as txn_id rather than gateway txn_id
	$paymeta = get_post_meta($payment_id, '_edd_payment_meta', true);
	if (is_array($paymeta)) {
		$downloads = maybe_unserialize($paymeta['downloads']);
		if (is_array($downloads)) {
			$count = 1;
			$id_orders = array();
			foreach ($downloads as $download) {
				$download_id = $download['id'];
				$level = $download['options']['price_id'] + 1;
				$qty = $download['quantity'];
				for ($i = 1; $i <= $qty; $i++) {
					$vars = idedd_payment_vars($paymeta, $payment_id, $download_id, $level, $count, $i);
					$rc = new ReflectionClass('ID_Order');
					$order = $rc->newInstanceArgs($vars);
					$pay_id = $order->insert_order();
					$id_orders[] = $pay_id;
				}
				$count++;
			}
			update_post_meta($payment_id, '_edd_order_pairing', $id_orders);
		}
	}
}

function idedd_update_order($payment_id) {
	$paymeta = get_post_meta($payment_id, '_edd_payment_meta', true);
	if (is_array($paymeta)) {
		$downloads = unserialize($paymeta['downloads']);
		if (is_array($downloads)) {
			//print_r($downloads);
			$count = 1;
			foreach ($downloads as $download) {
				$download_id = $download['id'];
				$level = $download['options']['price_id'] + 1;
				$qty = $download['quantity'];
				for ($i = 1; $i <= $qty; $i++) {
					$vars = idedd_payment_vars($paymeta, $payment_id, $download_id, $level, $count, $i);
					if (!empty($vars)) {
						$rc = new ReflectionClass('ID_Order');
						$order = $rc->newInstanceArgs($vars);
						$check = $order->check_new_order($vars['transaction_id']);
						if (isset($check)) {
							$pay_id = $check->id;
							if (isset($pay_id) && $pay_id > 0) {
								$vars['id'] = $pay_id;
								$rc = new ReflectionClass('ID_Order');
	       						$order = $rc->newInstanceArgs($vars);
	        					$update = $order->update_order();
							}
						}
					}
				}
				$count++;
			}
		}
	}
}

function idedd_complete_order($payment_id) {
	$orders = get_post_meta($payment_id, '_edd_order_pairing', true);
	if (!empty($orders)) {
		foreach ($orders as $order) {
			$update = setOrderStatus('C', $order);
		}
	}
}

function idedd_delete_order($post_id) {
	$orders = get_post_meta($post_id, '_edd_order_pairing', true);
	if (!empty($orders)) {
		foreach ($orders as $order) {
		//$order = ID_Order::get_order_by_txn($payment_id);
			//if (!empty($order)) {
				//$pay_id = $order->id;
				$delete = ID_Order::delete_order($order);
			//}
		}
	}
}

function idedd_payment_vars($paymeta, $payment_id, $download_id, $level, $count, $qty_num) {
	$vars = array();
	$post = get_post($payment_id);
	if (isset($post)) {
		$status = strtoupper(substr($post->post_status, 0, 1));
		$date = $post->post_date;
	}
	else {
		$status = 'P';
	}
	if (is_array($paymeta['user_info'])) {
		// strange but seems that after editing, it saves as array and not serialized array
		$user_info = $paymeta['user_info'];
	}
	else {
		$user_info = unserialize($paymeta['user_info']);
	}
	if (isset($user_info['first_name'])) {
		$first_name = $user_info['first_name'];
	}
	else {
		$first_name = '';
	}
	if (isset($user_info['last_name'])) {
		$last_name = $user_info['last_name'];
	}
	else {
		$last_name = '';
	}
	if (isset($user_info['email'])) {
		$email = $user_info['email'];
	}
	else {
		$email = '';
	}
	$cart_details = maybe_unserialize($paymeta['cart_details']);
	$price_array = edd_get_variable_prices($download_id);
	$price = $price_array[$level - 1]['amount'];
	if (!isset($date)) {
		$date = null;
	}
	if (isset($download_id) && $download_id > 0) {
		$project_id = get_post_meta($download_id, '_edd_project_pairing', true);
		if (isset($project_id) && $project_id > 0) {
			$project = new ID_Project($project_id);
			$the_project = $project->the_project();
			$vars = array(
				'id' => null,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'email' => $email,
				'address' => '',
				'state' => '',
				'city' => '',
				'zip' => '',
				'country' => '',
				'product_id' => $project_id,
				'transaction_id' => $payment_id.'-v'.$level.'-'.$count.'-'.$qty_num,
				'preapproval_key' => '',
				'product_level' => $level,
				'prod_price' => $price,
				'status' => $status,
				'created_at' => $date
			);
		}
	}
	return (isset($vars) ? $vars : array());
}

function idcf_level_select_lb($project_id, $the_deck = null) {
	//ob_start();
	//$project = new ID_Project($project_id);
	global $pwyw;
	if ($the_deck->deck_type == 'default') {
		$post_id = $the_deck->post_id;
		$image = idc_checkout_image($post_id);
		if (isset($the_deck->level_data)) {
			$level_data = $the_deck->level_data;
		}
		else {
			$level_data = null;
		}
		if (!empty($level_data)) {
			$purchase_url = getPurchaseURLfromType($project_id, 'purchaseform');
			$action = apply_filters('idcf_purchase_url', $purchase_url, $project_id);
			include ID_PATH.'/templates/_lbLevelSelect.php';
			//$content = ob_get_contents();
			//ob_end_flush();
			//echo $content;
		}
	}
	return;
}

//add_action('wp', 'catch_idcf_level_select', 1);

function catch_idcf_level_select() {
	// note: we really need to get this down to a single function that can be re-used
	if (isset($_POST['lb_level_submit'])) {
		$project_id = absint($_POST['project_id']);
		$level = absint($_POST['level_select']);
		$price = esc_attr($_POST['total']);
		if (isset($project_id) && $project_id > 0) {
			// which commerce system are we using?
			// test for IDC
			if (class_exists('ID_Member')) {
				$idc_owned = mdid_get_selected();
			}
			$purchase_url = getPurchaseURLfromType($project_id, 'purchaseform').'&level='.$level.'&price='.$price;
			header('Location: '.apply_filters('idcf_purchase_url', $purchase_url, $project_id));
		}
	}
	return;
}

?>