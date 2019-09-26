<?php
add_shortcode('memberdeck_dashboard', 'memberdeck_dashboard');
add_shortcode('idc_dashboard', 'memberdeck_dashboard');
add_shortcode('memberdeck_checkout', 'memberdeck_checkout');
add_shortcode('idc_checkout', 'memberdeck_checkout');
add_shortcode('idc_button', 'idc_button');

function memberdeck_dashboard() {
	ob_start();
	global $crowdfunding;
	if (function_exists('idf_get_querystring_prefix')) {
		$prefix = idf_get_querystring_prefix();
	} else {
		$prefix = '?';
	}
	$instant_checkout = instant_checkout();
	/* Mange Dashboard Visibility */
	if (is_user_logged_in()) {
		//global $customer_id; --> will trigger 1cc notice
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$fname = $current_user->user_firstname;
		$lname = $current_user->user_lastname;
		$registered = $current_user->user_registered;
		$key = md5($registered.$current_user->ID);
		// expire any levels that they have not renewed
		$level_check = memberdeck_exp_checkondash($current_user->ID);
		// this is an array user options
		$user_levels = ID_Member::user_levels($current_user->ID);
	}

	if (isset($user_levels)) {
		// this is an array of levels a user has access to
		$access_levels = unserialize($user_levels->access_level);
		if (is_array($access_levels)) {
			$unique_levels = array_unique($access_levels);
		}
	}
	
	$downloads = ID_Member_Download::get_downloads();
	// we have a list of downloads, but we need to get to the levels by unserializing and then restoring as an array
	if (!empty($downloads)) {
		// this will be a new array of downloads with array of levels
		$download_array = array();
		foreach ($downloads as $download) {
			$new_levels = unserialize($download->download_levels);
			unset($download->download_levels);
			// lets loop through each level of each download to see if it matches
			$pass = false;
			if (!empty($new_levels)) {
				foreach ($new_levels as $single_level) {
					if (isset($unique_levels) && in_array($single_level, $unique_levels)) {
						// if this download belongs to our list of user levels, add it to array
						//$download->download_levels = $new_levels;
						$pass = true;
					}
				}
			}
			// Putting image URL on image_link according to new changes, as attachment_id might be stored in that field instead of URL
			if (!empty($download->image_link) && stristr($download->image_link, "http") === false) {
				$download_thumb = wp_get_attachment_image_src($download->image_link, 'idc_dashboard_download_image_size');
				if (!empty($download_thumb)) {
					$download->image_link = $download_thumb[0];
					$width = $download_thumb[1];
					$height = $download_thumb[2];
					if (function_exists('idf_image_layout_by_dimensions')) {
						$image_layout = idf_image_layout_by_dimensions($width, $height);
					} else {
						$image_layout = 'landscape';
					}
					$download->image_width = $width;
					$download->image_height = $height;
					$download->image_layout = $image_layout;
				}
			}
			else if (empty($download->image_link)) {
				$download->image_link = plugins_url('images/dashboard-download-placeholder.jpg', __FILE__);
				$download->image_layout = 'landscape';
			}
			else {	
				$download->image_layout = 'landscape';
			}
			if ($pass) {
				$e_date = ID_Member_Order::get_expiration_data($user_id, $single_level);
				if (!is_idc_free()) {
					$sub = new ID_Member_Subscription(null, $user_id, $single_level);
					$renewable = $sub->is_subscription_renewable();
					if ($renewable) {
						$days_left = idmember_e_date_format($e_date);
						$download->days_left = $days_left;
					}
				}
				$license_key = MD_Keys::get_license($user_id, $download->id);
				$download->key = $license_key;
				$download_array['visible'][] = $download;
			}
			else {
				$download_array['invisible'][] = $download;
			}
		}
		// we should now have an array of downloads that this user has accces to
	}
	if (is_user_logged_in()) {
		$dash = get_option('md_dash_settings');
		$general = maybe_unserialize(get_option('md_receipt_settings'));
		if (!empty($dash)) {
			if (!is_array($dash)) {
				$dash = unserialize($dash);
			}
			if (isset($dash['layout'])) {
				$layout = $dash['layout'];
			}
			else {
				$layout = 1;
			}
			if (isset($dash['alayout'])) {
				$alayout = $dash['alayout'];
			}
			else {
				$alayout = 'md-featured';
			}
			$aname = $dash['aname'];
			if (isset($dash['blayout'])) {
				$blayout = $dash['blayout'];
			}
			else {
				$blayout = 'md-featured';
			}
			$bname = $dash['bname'];
			if (isset($dash['clayout'])) {
				$clayout = $dash['clayout'];
			}
			else {
				$clayout = 'md-featured';
			}
			$cname = $dash['cname'];
			if ($layout == 1) {
				$p_width = 'half';
				$a_width = 'half';
				$b_width = 'half';
				$c_width = 'half';
			}
			else if ($layout == 2) {
				$p_width = 'half';
				$a_width = 'half';
				$b_width = 'full';
				$c_width = 'full';
			}
			else if ($layout == 3) {
				$p_width = 'full';
				$a_width = 'full';
				$b_width = 'full';
				$c_width = 'full';
			}
			else if ($layout == 4) {
				$p_width = 'half';
				$a_width = 'half-tall';
				$b_width = 'half';
				$c_width = 'hidden';
			}
			if (isset($dash['powered_by'])) {
				$powered_by = $dash['powered_by'];
			}
			else {
				$powered_by = 1;
			}
		}

		// If credits are enabled from settings, then get available credits, else set them to 0
		if (isset($general['enable_credits']) && $general['enable_credits'] == 1) {
			$md_credits = md_credits();
		} else {
			$md_credits = 0;
		}
		$settings = get_option('memberdeck_gateways', true);
		if (isset($settings)) {
			$es = (isset($settings['es']) ? $settings['es'] : 0);
			$efd = (isset($settings['efd']) ? $settings['efd'] : 0);
			$eauthnet = (isset($settings['eauthnet']) ? $settings['eauthnet'] : 0);
			if ($es == 1) {
				$customer_id = customer_id();
			}
			else if ($efd == 1) {
				$fd_card_details = fd_customer_id();
				if (!empty($fd_card_details)) {
					$fd_token = $fd_card_details['fd_token'];
					$customer_id = $fd_card_details;
				}
			}
			else if ($eauthnet == 1) {
				$authorize_customer_id = authnet_customer_id();
				if (!empty($authorize_customer_id)) {
					$customer_id = $authorize_customer_id['authorizenet_payment_profile_id'];
				} else {
					$customer_id = "";
				}
			}
			$customer_id = apply_filters('idc_checkout_form_customer_id', (isset($customer_id) ? $customer_id : ''), '', $settings);
		}
		if ($md_credits > 0 || !empty($customer_id)) {
			$show_occ = true;
		}
		else {
			$show_occ = false;
		}
		include_once 'templates/admin/_memberDashboard.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	else {
		include_once 'templates/_protectedPage.php';
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}

function memberdeck_checkout($attrs) {
	ob_start();
	$url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$customer_id = customer_id();
	$instant_checkout = instant_checkout();
	$renewable = false;
	global $crowdfunding;
	global $first_data;
	global $pwyw;
	global $global_currency;
	global $stripe_api_version;
	$global_currency_symbol = idc_currency_to_symbol($global_currency);
	// use the shortcode attr to get our level id
	$product_id = $attrs['product'];
	do_action('doing_idc_checkout', $product_id);
	if (isset($pwyw) && $pwyw) {
		if (isset($_GET['price']) && $_GET['price'] > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$pwyw_price = number_format( sprintf('%f', floatval($_GET['price'])), 2, ".", "" );
			}
			else {
				$pwyw_price = number_format( floatval(esc_attr($_GET['price'])), 2, ".", "" );
			}
		}
		else if (isset($_POST['price']) && $_POST['price'] > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$pwyw_price = number_format( sprintf('%f', floatval($_POST['price'])), 2, ".", "" );
			}
			else {
				$pwyw_price = number_format( floatval(esc_attr($_POST['price'])), 2, ".", "" );
			}
		}
	}

	// get the user info
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;
		$fname = $current_user->user_firstname;
		$lname = $current_user->user_lastname;
		// Check first if this user is allowed to purchase
		// #dev send to function
		$is_purchases_blocked = get_user_meta($current_user->ID, 'block_purchasing', true);
		if (!empty($is_purchases_blocked) && $is_purchases_blocked == "1") {
			include_once 'templates/_purchasesBlocked.php';
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		$member = new ID_Member($current_user->ID);
		$user_data = ID_Member::user_levels($current_user->ID);
		if (!empty($user_data)) {
			$user_levels = unserialize($user_data->access_level);
		}
		else {
			$user_levels = null;
		}
		// lets see how many levels this user owns
		if (is_array($user_levels)) {
			foreach ($user_levels as $level) {
				if ($level == $product_id) {
					$renewable = ID_Member_Level::is_level_renewable($level);
					// Check if order exists for that product, if yes, then renewable is true
					// #devnote should be part of above function
					if (is_user_logged_in()) {
						$last_order = new ID_Member_Order(null, $current_user->ID, $product_id);
						$get_last_order = $last_order->get_last_order();
						if (empty($get_last_order)) {
							$renewable = false;
						}
					}
					if (!$renewable) {
						$already_valid = true;
					}
				}
			}
		}
	}
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		$settings = maybe_unserialize($settings);
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$guest_checkout = $settings['guest_checkout'];
	}
	else {
		$coname = '';
		$guest_checkout = 0;
	}
	// #devnote why are we changing var name here?
	// Settings assigning to general variable
	$general = maybe_unserialize($settings);
	
	$gateways = get_option('memberdeck_gateways');
	if (!empty($gateways)) {
		// gateways are saved and we can now get settings from Stripe and Paypal
		if (is_array($gateways)) {
			$mc = (isset($gateways['manual_checkout']) ? $gateways['manual_checkout'] : 0);
			$pp_email = (isset($gateways['pp_email']) ? $gateways['pp_email'] : '');
			$test_email = (isset($gateways['test_email']) ? $gateways['test_email'] : '');
			$pk = (isset($gateways['pk']) ? $gateways['pk'] : '');
			$sk = (isset($gateways['sk']) ? $gateways['sk'] : '');
			$tpk = (isset($gateways['tpk']) ? $gateways['tpk'] : '');
			$tsk = (isset($gateways['tsk']) ? $gateways['tsk'] : '');
			$test = (isset($gateways['test']) ? $gateways['test'] : 0);
			$epp = (isset($gateways['epp']) ? $gateways['epp'] : 0);
			$es = (isset($gateways['es']) ? $gateways['es'] : 0);
			$esc = (isset($gateways['esc']) ? $gateways['esc'] : 0);
			$ecb = 0; #disable coinbase until commerce integration is completed (isset($gateways['ecb']) ? $gateways['ecb'] : '');
			$eauthnet = (isset($gateways['eauthnet']) ? $gateways['eauthnet'] : '0');
			$eppadap = (isset($gateways['eppadap']) ? $gateways['eppadap'] : '0');
			$efd = (isset($gateways['efd']) ? $gateways['efd'] : '0');
            if (isset($efd) && $efd) {
				$gateway_id = $gateways['gateway_id'];
				$fd_pw = $gateways['fd_pw'];
				$efd = $gateways['efd'];
			}
			if (!is_idc_free() && function_exists('is_id_pro') && is_id_pro()) {
				if ($es) {
					// #devnote remove this
					// Do nothing because Stripe customer id is already set
				}
				else if ($efd) {
					$fd_card_details = fd_customer_id();
					if (!empty($fd_card_details)) {
						$customer_id = $fd_card_details['fd_token'];
					}
				}
				else if ($eauthnet) {
					$authorize_customer_id = authnet_customer_id();
					if (!empty($authorize_customer_id)) {
						$customer_id = $authorize_customer_id['authorizenet_payment_profile_id'];
					} else {
						$customer_id = "";
					}
				}
				$customer_id = apply_filters('idc_checkout_form_customer_id', $customer_id, $product_id, $gateways);

				$esc = $esc;
				$check_claim = apply_filters('md_level_owner', get_option('md_level_'.$product_id.'_owner'));
				if (!empty($check_claim)) {
					if ($esc == '1') {						
						$md_sc_creds = get_sc_params($check_claim);
						if (!empty($md_sc_creds)) {
							$sc_accesstoken = $md_sc_creds->access_token;
							$sc_pubkey = $md_sc_creds->stripe_publishable_key;
						}
					}
					if ($epp == '1') {
						$claimed_paypal = get_user_meta($check_claim, 'md_paypal_email', true);
					}
				}
			}
		}
	}

	$cc_currency_symbol = '$';
	$cc_currency = 'USD';
	if (isset($es) && $es == 1) {
		// #devnote can this move up?
		if (!class_exists('Stripe')) {
			require_once 'lib/stripe-php-4.2.0/init.php';
		}
		if (isset($test) && $test == '1') {
			\Stripe\Stripe::setApiKey($tsk);
			\Stripe\Stripe::setApiVersion($stripe_api_version);
		}
		else {
			\Stripe\Stripe::setApiKey($sk);
			\Stripe\Stripe::setApiVersion($stripe_api_version);
		}
		// get stripe currency
		$stripe_currency = 'USD';
		$stripe_symbol = '$';
		if (!empty($gateways)) {
			if (is_array($gateways)) {
				$stripe_currency = $gateways['stripe_currency'];
				$stripe_symbol = md_currency_symbol($stripe_currency);
			}
		}
	}
	else if (isset($efd) && $efd == 1) {
		$endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
		$wsdl = 'https://api.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
	}

	// use that id to get our level data
	$return = ID_Member_Level::get_level($product_id);
	// we have that data, lets store it in vars
	$level_name = $return->level_name;
	$txn_type = $return->txn_type;
	if ($txn_type == 'preauth') {
		$guest_checkout = 0;
	}
	if ($renewable) {
		$level_price = $return->renewal_price;
	}
	else {
		$renewable = false;
		$level_price = $return->level_price;
		if (isset($pwyw_price) && $pwyw_price > $level_price) {
			$level_price = $pwyw_price;
		}
	}
	if (!is_idc_free()) {
		// Check if this product is an upgrade of another product, if yes, then get the difference of level prices. But not for recurring levels.
		if ($return->level_type !== 'recurring' && !$renewable) {
			$idc_pathways = new ID_Member_Pathways(null, $product_id);
			$product_pathway = $idc_pathways->get_product_pathway();
			if (!empty($product_pathway)) {
				$idc_pathways->upgrade_pathway = $product_pathway->upgrade_pathway;
				$level_difference = $idc_pathways->get_lower_product_difference($level_price, (is_user_logged_in() ? $current_user->ID : ''));
				if ($level_difference > 0) {
					// Setting new level price
					$level_price = $level_difference;
					// New pay what you want price
					$pwyw_price = $level_price;
					$upgrade_level = true;
				}
			}
		}
	}

	$currency = memberdeck_pp_currency();
	if (!empty($currency)) {
		$pp_currency = $currency['code'];
		$pp_symbol = $currency['symbol'];
	}
	else {
		$pp_currency = 'USD';
		$pp_symbol = '$';
	}
	// If payment gateway for CC payments is Authorize.Net, and level is recurring, make instant_checkout false
	if ($return->level_type == 'recurring' && $gateways['eauthnet'] == 1) {
		$instant_checkout = false;
	}
	
	$type = $return->level_type;
	$recurring = $return->recurring_type;
	$limit_term = $return->limit_term;
	$term_length = $return->term_length;
	$combined_product = $return->combined_product;	

	$credit_value = $return->credit_value;
	$cf_level = false;
	if ($crowdfunding) {
		$cf_assignments = get_assignments_by_level($product_id);
		if (!empty($cf_assignments)) {
			$project_id = $cf_assignments[0]->project_id;
			$project = new ID_Project($project_id);
			$the_project = $project->the_project();
			$post_id = $project->get_project_postid();
			$id_disclaimer = get_post_meta($post_id, 'ign_disclaimer', true);
		}
	}

	// Getting credits value, if the product can be purchased using credits and if the user have credits, then add an option to purhcase using credits
	$paybycrd = 0;
	$member_credits = 0;
	if (isset($general['enable_credits']) && $general['enable_credits'] == 1) {
		if (isset($member)) {
			$member_credits = $member->get_user_credits();
		}
		if ($member_credits > 0) {
			if (isset($pwyw_price) && $global_currency == 'credits') {
				$credit_value = $pwyw_price;
			}
			if ($credit_value > 0 && $credit_value <= $member_credits) {
				$paybycrd = 1;
			}
		}
	}

	if (isset($ecb) && $ecb) {
		$cb_currency = (isset($gateways['cb_currency']) ? $gateways['cb_currency'] : 'BTC');
		$cb_symbol = md_currency_symbol($cb_currency);
	}

	// If there is a combined product for currency loaded product, then we have to see if payment gateway supports it or not
	// then show text in General text that this product is combined with another
	if ($combined_product) {
		$epp = $eppadap = $eauthnet = $ecb = $efd = $mc = $paybycrd = 0;
		// #devnote we should be using filters for this and modifying either gateways array or individual vars
		$combined_level = ID_Member_Level::get_level($combined_product);
		add_filter('idc_info_price', function($level_price, $return) use ($combined_level) {
			$info_price = $level_price + $combined_level->level_price;
			return $info_price;
		}, 10, 2);
		// Now see if any CreditCard gateway is active which supports recurring products, we just need to see if we have
		// to show that text or not in General text of different payment methods
		$combined_purchase_gateways = idc_combined_purchase_allowed($gateways);
	}
	else {
		$combined_purchase_gateways = array();
	}
	
	if (!isset($already_valid) || $return->enable_multiples || $renewable) {
		// they don't own this level, send forth the template
		$level_price = apply_filters('idc_product_price', $level_price, $product_id, $return);
		if ($level_price !== '' && $level_price > 0) {
			if ($global_currency == 'BTC' || $global_currency == 'credits') {
				$level_price = number_format(sprintf('%f', (float) $level_price), 2);
			}
			else {
				$level_price = number_format(floatval($level_price), 2, '.', ',');
			}
		}
		$info_price = apply_filters('idc_price_format', apply_filters('idc_info_price', $level_price, $return));
		// Getting the content of the terms page
		if (!empty($general['terms_page'])) {
			$terms_content = get_post( $general['terms_page'] );
		}
		if (!empty($general['privacy_page'])) {
			$privacy_content = get_post( $general['privacy_page'] );
		}
		
		include_once apply_filters('idc_checkout_template', 'templates/_checkoutForm.php');
		$content = ob_get_contents();
	}
	else {
		// they already own this one
		$content = '<form method="POST" id="idc_already_purchased" name="idc_already_purchased">';
		$content .= '<p>'.__('You already own this product. Please', 'memberdeck').' <a href="'.wp_logout_url().'">'.__('logout', 'memberdeck').'</a> '.__('and create a new account in order to purchase again', 'memberdeck').'.</p>';
		$content .= '<input type="hidden" name="user_email" class="user_vars" value="'.$email.'"/>';
		$content .= '<input type="hidden" name="user_login" class="user_vars" value="'.$current_user->user_login.'"/>';
		$content .= '</form>';
	}
	ob_end_clean();
	return $content;
}

function idc_button($args) {
	global $global_currency;
	if ($global_currency == "credits") {
		$currency_symbol = '$';
	} else {
		$currency_symbol = md_currency_symbol($global_currency);
	}
	$args = apply_filters('idc_button_args', $args);
	do_action('idc_button_before', $args);
	// Using GET variable to check if the form is submitted, as we need price as well in GET vars which is in GET var
	if (isset($_GET['idc_button_submit'])) {
		// we need to submit some args with this
		$price = (isset($_POST['price']) ? sanitize_text_field($_POST['price']) : sanitize_text_field($_POST['total']));
		$args['price'] = $price;
		do_action('idc_button_submit', $args);
	}
	$button = '<div class="memberdeck">';
	$button .= '<button type="'.(isset($args['type']) ? $args['type'] : '').'" id="'.(isset($args['id']) ? $args['id'] : '').'" class="idc_shortcode_button submit-button '.(isset($args['classes']) ? $args['classes'] : '').'" '.(isset($args['product']) ? 'data-product="'.$args['product'].'"' : '').' data-source="'.(isset($args['source']) ? $args['source'] : '.idc_button_lightbox').'">'.(isset($args['text']) ? $args['text'] : '').'</button>';
	$button .= '</div>';
	if (isset($args['product'])) {
		$product_id = $args['product'];
		if (strpos($product_id, ',')) {
			$product_id = explode(',', $product_id);
		}
		if (is_array($product_id)) {
			$level = array();
			foreach ($product_id as $k=>$v) {
				$level[] = ID_Member_Level::get_level($v);
			}
		}
		else {
			$level = ID_Member_Level::get_level($product_id);
		}
		ob_start();
		include_once 'templates/_idcButtonContent.php';
		$button .= ob_get_contents();
		ob_end_clean();
	}
	do_action('idc_button_after', $args);
	return apply_filters('idc_button', $button, $args);
}
?>