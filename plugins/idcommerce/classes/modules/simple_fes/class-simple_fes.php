<?php
Class ID_Simple_Fes {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'simple_fes_hooks.php';
		require dirname(__FILE__) . '/' . 'class-simple_fes_form.php';
	}

	private static function set_filters() {
		add_action('init', 'simple_fes_init');
		add_action('wp_enqueue_scripts','simple_fes_scripts');
		add_filter('gettext', 'simple_fes_text_filters', 10, 3);
		add_filter('id_fes_form', array('ID_Simple_Fes_Form', 'simple_fes_form'), 10, 2);
		add_filter('id_fes_form_wrapper_class', 'idc_simple_fes_form_wrapper_class');
		//add_filter('ide_level_1_title', 'idc_simple_fes_level_1_title', 10, 2);
		add_filter('ide_level_1_limit', 'idc_simple_fes_level_1_limit', 10, 2);
		add_filter('ide_level_1_desc', 'idc_simple_fes_level_1_desc', 10, 2);
		add_filter('ide_level_1_price', 'idc_simple_fes_level_1_price', 10, 2);
		//add_filter('ide_saved_levels', 'idc_simple_fes_saved_levels', 10, 2);
		//add_filter('ide_saved_funding_types', 'idc_simple_fes_saved_funding_types', 10, 2);
	}
}

$simple_fes = new ID_Simple_Fes();
?>