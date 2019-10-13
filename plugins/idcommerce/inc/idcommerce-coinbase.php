<?php

// #devnote maybe move to global file
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Users;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Checkout;
use Coinbase\Wallet\Value\Money;

function idc_get_coinbase_currency() {
	$settings = get_option('memberdeck_gateways');
	$cb_currency = (isset($settings['cb_currency']) ? $settings['cb_currency'] : 'BTC');
	return $cb_currency;
}

function idc_init_coinbase_client() {
	$settings = get_option('memberdeck_gateways');
	$cb_api_key = (isset($settings['cb_api_key']) ? $settings['cb_api_key'] : '');
	$cb_api_secret = (isset($settings['cb_api_secret']) ? $settings['cb_api_secret'] : '');

	require( IDC_PATH . "/lib/Coinbase/vendor/autoload.php" );

	$cb_configuration = Configuration::apiKey($cb_api_key, $cb_api_secret);
	$cb_client = Client::create($cb_configuration);
	return $cb_client;
}

function idc_test_coinbase_client($cb_client) {
	try {
		$data = $cb_client->getAccounts();
		$status = 1;
	}
	catch (exception $e) {
		$data = idc_return_coinbase_exception($e);
		$status = 0;
	}
	return (object) array(
		'status' => $status,
		'data' => $data
	);
}

function idc_return_coinbase_exception($e) {
	$message = $e->getMessage();
	return $message;
}

function idmember_get_coinbase_button() {
	// #devnote disable test mode and recurring, collect address on checkout, set up oauth/fees, fix ipn
	global $global_currency;
	require( IDC_PATH . "/lib/Coinbase/vendor/autoload.php" );

	$prefix = '?';
	$permalink_structure = get_option('permalink_structure');
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	$cb_currency = idc_get_coinbase_currency();
	$query_string = sanitize_text_field($_POST['query_string']);

	$client = idc_init_coinbase_client();

	// Generating the button instead
	//$coinbase = Coinbase::withApiKey($cb_api_key, $cb_api_secret);
	$email = (isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '');
	$fname = (isset($_POST['fname']) ? sanitize_text_field($_POST['fname']) : '');
	$lname = (isset($_POST['lname']) ? sanitize_text_field($_POST['lname']) : '');
	$price = sanitize_text_field($_POST['product_price']);
	$product_id = absint($_POST['product_id']);
	$options = array(
		'amount' => new Money($price, $cb_currency),
		'name' => $price,
		'notifications_url' => home_url('/').urlencode($prefix."coinbase_success=1" ."&". "email=" . $email . $query_string),
		'style' => 'custom_small',
		'type' => 'order',
		'success_url' => md_get_durl().$prefix.'idc_product='.$product_id.'&paykey='.md5($_POST['email'].time()).$query_string,
		'auto_redirect' => true,
		'metadata' => array(
			'user_id' => '',
			'user_email' => $email,
			'user_fname' => $fname,
			'user_lname' => $lname,
			'product_id' => $product_id
		),
		'collect_email' => true,
	);
	try {
		$checkout = new Checkout($options);
		$client->createCheckout($checkout);
		$code = $checkout->getEmbedCode();
		print_r(json_encode(array("response" => "success", "code" => $code, 'message' => '')));
	}
	catch (Exception $e) {
		$message = idc_return_coinbase_exception($e);
		print_r(json_encode(array("response" => 'failure', 'code' => null, 'message' => $message)));
	}
	exit();
}

add_action('wp_ajax_idmember_get_coinbase_button', 'idmember_get_coinbase_button');
add_action('wp_ajax_nopriv_idmember_get_coinbase_button', 'idmember_get_coinbase_button');
?>