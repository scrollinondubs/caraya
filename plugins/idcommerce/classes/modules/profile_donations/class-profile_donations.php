<?php
class ID_Profile_Donations {
	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'profile_donations_hooks.php';
	}

	private static function set_filters() {
		add_filter('admin_menu', 'profile_donations_admin', 12);
		add_filter('id_creator_projects', 'profile_donations_display', 10);
	}
}
$profile_donations = new ID_Profile_Donations(); 
?>