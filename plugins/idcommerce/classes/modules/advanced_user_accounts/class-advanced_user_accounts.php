<?php
Class ID_Advanced_User_Accounts {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'advanced_user_accounts_hooks.php';
	}

	private static function set_filters() {
		add_action('admin_enqueue_scripts', 'advanced_user_accounts_admin_scripts');
		add_action('md_register_extrafields', 'advanced_user_accounts_registration_fields');
		add_action('idc_before_register_success', 'advanced_user_accounts_parse_registration', 9, 3);
		//add_filter('idc_menu_box_items', 'advanced_user_accounts_menu_meta_box_items');
		add_action('idc_profile_social_after', 'advanced_user_accounts_profile_fields');
	}
}

$advanced_user_accounts = new ID_Advanced_User_Accounts();
?>