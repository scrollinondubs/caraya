<?php
add_action('init', 'id_secupay_tests');

function id_secupay_tests() {
	require_once('lib/secupay-php/secupay_api.php');
	$settings = id_secupay_settings();
	$api_key = (isset($settings['api_key']) ? $settings['api_key'] : '');
	$request_data = array("apikey" => $api_key, "apiversion" => secupay_api::get_api_version());
	//id_secupay_tests_gettypes($request_data);
	//id_secupay_tests_debit($request_data);
}

function id_secupay_tests_gettypes($request_data) {

	$sp_api = new secupay_api($request_data, 'gettypes', 'application/json', true);
	$api_return = $sp_api->request();

	echo "Parameter an Secupay:\n\n";
	print_r($request_data);
	echo "\n---------\n\nAntwort von Secupay:\n\n";
	print_r($api_return);
}

function id_secupay_tests_debit($request_data) {
	$new_data = array(
		'demo' => apply_filters('id_secupay_demo_mode', 1),
		'payment_type' => 'prepay',
		'payment_action' => 'sale',
		'url_success' => 'http://nathanhangen.com',
		'url_failure' => 'http://nathanhangen.com',
		'url_push' => 'http://nathanhangen.com',
		'amount' => '100',
	);
	$request_data = array_merge($request_data, $new_data);
	print_r($request_data);
	$sp_api = new secupay_api($request_data, 'init', 'application/json', true);
	$api_return = $sp_api->request();
	print_r($api_return);
}
?>