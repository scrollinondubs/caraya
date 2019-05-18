<?php

class ID_Custom_Dash {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('ide_before_enterprise_settings', array($this, 'platform_settings'));
		add_action('ide_enterprise_settings_submit', array($this, 'platform_settings_submit'));
	}

	function platform_settings() {
		$enterprise_settings = get_option('idc_enterprise_settings');
		include_once('templates/admin/_platformSettings.php');
	}

	function platform_settings_submit($raw_post) {
		print_r($raw_post);
	}

}
new ID_Custom_Dash();
?>