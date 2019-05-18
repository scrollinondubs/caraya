<?php
Class ID_Anon_Checkout {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'anon_checkout_hooks.php';
	}

	private static function set_filters() {
		add_action('wp_enqueue_scripts', 'anon_checkout_scripts');
		add_action('md_purchase_extrafields', 'idc_anon_checkout_template');
		add_action('memberdeck_payment_success', 'idc_save_anon_checkout_selections', 100, 5);
		add_action('memberdeck_preauth_success', 'idc_save_anon_checkout_selections', 100, 5);
		add_filter('idc_backers_listing_level_name', 'idc_anon_checkout_comments', 10, 3);
		add_filter( 'idc_backers_listing_name', 'idc_anon_checkout_name', 10, 3 );
		add_filter('idc_backer_userdata', 'idc_anon_checkout_link', 10, 2);
		add_filter('idc_backers_avatar_id', 'idc_anon_checkout_avatar', 10, 2);
	}
}

$anonymous_checkout = new ID_Anon_Checkout();
?>