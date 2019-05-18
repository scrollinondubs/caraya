<?php

class ID_Affiliate_WP {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('plugins_loaded', array($this, 'affiliate_wp_extend'));
		add_action('admin_init', array($this, 'register_integration_hook'));
	}

	function affiliate_wp_extend() {
		if (class_exists('Affiliate_WP_Base')) {
			// load during wp hook to ensure Affiliate_WP_Base class is present
			require('class-affiliate_wp_extend.php');
			new ID_Affiliate_WP_Extend();
		}
	}

	function register_integration_hook() {
		add_filter('affwp_integrations', array($this, 'register_integration'));
	}

	function register_integration($integrations) {
		$integrations['idcommerce'] = __('IgnitionDeck Commerce', 'memberdeck');
		ksort($integrations);
		return $integrations;
	}

}
new ID_Affiliate_WP();
?>