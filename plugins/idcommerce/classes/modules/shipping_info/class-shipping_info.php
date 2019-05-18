<?php
Class ID_Shipping_Info {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'shipping_info_hooks.php';
	}

	private static function set_filters() {
		add_action('wp_enqueue_scripts','shipping_info_scripts');
		add_action('md_purchase_extrafields','idc_shipping_info_template');
		add_action('memberdeck_payment_success', 'idc_save_shipping_info', 100, 5);
		add_action('memberdeck_preauth_success', 'idc_save_shipping_info', 100, 5);
		add_action('id_payment_success', 'idc_schedule_idcf_shipping_update', 100, 1);
		add_action('idc_update_idcf_shipping', 'idc_update_idcf_shipping');
	}
}

$shipping_info = new ID_Shipping_Info();
?>