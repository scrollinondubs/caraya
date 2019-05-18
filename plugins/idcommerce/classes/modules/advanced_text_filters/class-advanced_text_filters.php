<?php
Class ID_Advanced_Text_Filters {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'advanced_text_filters_hooks.php';
	}

	private static function set_filters() {
		//add_action('wp_enqueue_scripts','advanced_text_filters_scripts');
		add_filter('gettext', 'advanced_text_filters_text', 21, 3);
		add_filter('idc_backer_profile_slug', 'advanced_text_filters_backer_profile_slug', 11, 1);
		add_filter('idc_creator_profile_slug', 'advanced_text_filters_creator_profile_slug', 11, 1);
		add_filter('idc_creator_projects_slug', 'advanced_text_filters_creator_projects_slug', 11, 1);
		add_filter('idc_backer_registration_slug', 'advanced_text_filters_backer_registration_slug', 11, 1);
		add_filter('idc_creator_registration_slug', 'advanced_text_filters_creator_registration_slug', 11, 1);
	}
}

$advanced_text_filters = new ID_Advanced_Text_Filters();
?>