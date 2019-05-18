<?php
Class ID_Fee_Mods {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'fee_mods_hooks.php';
		require dirname(__FILE__) . '/' . 'class-fee_mods_metaboxes.php';
	}

	private static function set_filters() {
		add_action('admin_init', 'fee_mods_metabox');
		add_filter('idc_app_fee', 'fee_mods_fee', 10, 2);
	}
}

$fee_mods = new ID_Fee_Mods();
?>