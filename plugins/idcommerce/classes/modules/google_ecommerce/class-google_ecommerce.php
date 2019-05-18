<?php
class ID_Google_Ecommerce {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'google_ecommerce_hooks.php';
	}

	private static function set_filters() {
		add_action('wp_enqueue_scripts', 'google_ecommerce_scripts');
		add_action('admin_menu', 'google_ecommerce_admin', 12);
		add_action('wp_ajax_google_ecommerce_order_data', 'google_ecommerce_order_data');
		add_action('wp_ajax_nopriv_google_ecommerce_order_data', 'google_ecommerce_order_data');
		add_action('memberdeck_payment_success', 'google_ecommerce_pay_triggers', 10, 5);
		add_action('memberdeck_free_success', 'google_ecommerce_free_triggers', 10, 2);
	}
	
}
new ID_Google_Ecommerce();
?>