<?php

class ID_Recaptcha {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('init', array($this, 'recaptcha_init'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
		// check for idc
		add_action('md_register_extrafields', array($this, 'render_reg_captcha'));
		add_filter('login_form_middle', array($this, 'render_login_captcha'));
		// default forms
		add_action('login_form', array($this, 'echo_login_captcha'));
		// wc forms
		// reserved
		add_action('admin_menu', array($this, 'admin_menus'), 20);
	}

	function recaptcha_init() {
		self::register_scripts();
	}

	function admin_menus() {
		add_submenu_page('idf', __('reCAPTCHA', 'idf'), __('reCAPTCHA', 'idf'), 'manage_options', 'idc_recaptcha', array($this, 'admin_menu'));
	}

	function admin_menu() {
		$settings = get_option('id_recaptcha_settings');
		if (isset($_POST['submit_id_recaptcha_settings'])) {
			foreach ($_POST as $k=>$v) {
				$settings[$k] = sanitize_text_field($v);
				update_option('id_recaptcha_settings', $settings);
			}
		}
		include_once('templates/admin/_settingsMenu.php');
	}

	function register_scripts() {
		$language = get_bloginfo('language');
		wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js?onload=idRecaptchaLoad&render=explicit&hl='.$language.' async defer');
		wp_register_script('id_recaptcha', plugins_url('js/id_recaptcha-min.js', __FILE__));
		wp_register_style('id_recaptcha', plugins_url('css/id_recaptcha-min.css', __FILE__));
		$settings = get_option('id_recaptcha_settings');
		wp_localize_script('id_recaptcha', 'id_recaptcha_site_id', (isset($settings['id_recaptcha_site_id']) ? $settings['id_recaptcha_site_id'] : ''));
	}

	function enqueue_scripts() {
		if ($this::has_site_id()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('recaptcha');
			wp_enqueue_script('id_recaptcha');
			wp_enqueue_style('id_recaptcha');
		}
	}

	function has_site_id() {
		$settings = get_option('id_recaptcha_settings');
		return !empty($settings['id_recaptcha_site_id']);
	}

	function captcha_content($wrapper = 'div') {
		return '<'.$wrapper.' class="form-row id_recaptcha_placeholder" data-callback="idRecaptchaCallback"></'.$wrapper.'>';
	}

	function render_reg_captcha() {
		echo $this::captcha_content();
	}

	function render_login_captcha($content = '') {
		return self::captcha_content('p');
	}

	function echo_login_captcha() {
		if ($this::has_site_id()) {
			echo self::render_login_captcha();
		}
	}
}
new ID_Recaptcha(); ?>