<?php

/*
Plugin Name: IgnitionDeck Crowdfunding
URI: http://IgnitionDeck.com
Description: A custom crowdfunding platform for WordPress. IgnitionDeck allows you to create unlimited and dynamic fundraising campaigns for physical and/or digital goods, integrates with a variety of email and ecommerce platforms, and is compatible with all WordPress themes 3.1+.
Version: 1.7.1
Author: IgnitionDeck
Author URI: http://ignitiondeck.com
License: GPL2
*/

/*
This sections handles the following:

1. IgnitionDeck Pro Activation
2. WordPress Multisite Activation
3. Standard WordPress Activation
*/


global $ign_db_version;
global $ign_installed_ver;
$ign_db_version = "1.7.1";
$ign_installed_ver = get_option( "ign_db_version" );

add_action( 'plugins_loaded', 'idcf_loaded');

function idcf_loaded() {
	id_idf_check();
}

add_action('init', 'idcf_init');

function idcf_init(){
	idcf_init_scripts();
	idcf_localization();
}

function id_idf_check() {
	if (!class_exists('IDF')) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		deactivate_plugins(plugin_basename(__FILE__));
		wp_die( __("IgnitionDeck Crowdfunding requires installation of the IgnitionDeck Framework prior to activation.", "ignitiondeck")."<br/> <a href='".admin_url('plugin-install.php?tab=search&s=ignitiondeck')."'>".__("Click here to install", "ignitiondeck")."</a>" );
	}
}

function idcf_init_scripts() {
	 wp_register_script( 'ignitiondeck', plugins_url('/js/ignitiondeck-min.js', __FILE__));
}

function idcf_localization() {
	$localization_vars = array(
		'level' => __('Level', 'ignitiondeck'),
		'level_title' => __('Level Title', 'ignitiondeck'),
		'level_price' => __('Level Price', 'ignitiondeck'),
		'level_limit' => __('Level Limit', 'ignitiondeck'),
		'level_order' => __('Level Order', 'ignitiondeck'),
		'short_description' => __('Short Description', 'ignitiondeck'),
		'long_description' => __('Long Description', 'ignitiondeck')
	);
	wp_localize_script('ignitiondeck', 'idcf_localization_vars', $localization_vars);
}


function is_id_network_activated() {
	$active_plugins = get_site_option( 'active_sitewide_plugins');
	if (isset($active_plugins['ignitiondeck-crowdfunding/ignitiondeck.php'])) {
		if (is_multisite()) {
			return true;
		}
	}
	return false;
}

if (is_multisite() && is_id_pro()) {
	// we only run this if we're network activating
	if (is_network_admin()) {
		register_activation_hook(__FILE__,'install_id_for_blogs');
	}
	// we are not in network admin, so we run regular activation script
	else {
		register_activation_hook(__FILE__,'ign_pre_install');
	}
}

else {
	register_activation_hook(__FILE__,'ign_pre_install');
	register_activation_hook(__FILE__,'ign_set_defaults');
}

if (is_id_network_activated() && is_id_pro()) {
	add_action('wpmu_new_blog', 'ign_pre_install', 1, 1);
	add_action('wpmu_new_blog', 'ign_set_defaults');
}

function install_id_for_blogs() {
	id_idf_check();
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->base_prefix.'blogs';
	$res = $wpdb->get_results($sql);
	foreach ($res as $blog) {
		ign_pre_install($blog->blog_id);
	}
}

function ign_pre_install ($blog_id = null) {
	id_idf_check();
    global $wpdb;
    global $ign_db_version;
    global $charset_collate;
	
	if (!empty($blog_id) && is_id_network_activated() && is_id_pro()) {
		if ($blog_id == 1) {
			$prefix = $wpdb->base_prefix;
			//$wpdb->base_prefix = $wpdb->base_prefix;
		}
		else {
			$prefix = $wpdb->base_prefix.$blog_id.'_';
			//$wpdb->base_prefix = $wpdb->base_prefix.$blog_id.'_';
		}
	}
	else if (!empty($blog_id) && is_id_pro()) {
		if ($blog_id == 1) {
			$prefix = $wpdb->prefix;
			//$wpdb->base_prefix = $wpdb->prefix;
		}
		else {
			$prefix = $wpdb->prefix.$blog_id.'_';
			//$wpdb->base_prefix = $wpdb->prefix.$blog_id.'_';
		}
	}
	else {
		$prefix = $wpdb->prefix;
		//$wpdb->base_prefix = $wpdb->prefix;
	}

	$table_name = $prefix . "ign_settings";
    
    $sql = "CREATE TABLE " . $table_name . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
	theme_value VARCHAR( 250 ) NOT NULL DEFAULT 'style1',
	theme_choices TEXT (65535),
	id_widget_logo_on TINYINT( 1 ) NOT NULL DEFAULT '1',
	id_widget_link VARCHAR( 200 ) NOT NULL DEFAULT 'http://ignitiondeck.com',
	UNIQUE KEY id (id)
	) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    update_option("ign_db_version", $ign_db_version);
	
    $table_name = $prefix . "ign_products";
    
    $sql = "CREATE TABLE " . $table_name . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    product_image VARCHAR( 250 ) NOT NULL ,
    product_name VARCHAR( 250 ) NOT NULL ,
    product_url VARCHAR( 250 ) NOT NULL ,
    ign_product_title VARCHAR ( 250 ) NOT NULL,
    ign_product_limit VARCHAR ( 250 ),
    product_details TEXT NOT NULL ,
    product_price DOUBLE NOT NULL ,
    goal DOUBLE NOT NULL ,
    created_at DATETIME, 
    UNIQUE KEY id (id)
    ) $charset_collate;";
    dbDelta($sql);
	
	$table_name = $prefix . "ign_product_settings";

    $sql = "CREATE TABLE " . $table_name . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	product_id VARCHAR( 250 ) NOT NULL,
	mailchimp_api_key VARCHAR( 250 ) NOT NULL,
	mailchimp_list_id VARCHAR( 250 ) NOT NULL,
	aweber_email VARCHAR( 250 ) NOT NULL,
	active_mailtype enum('mailchimp','aweber') NOT NULL,
	form_settings TEXT NOT NULL,
	paypal_email VARCHAR( 250 ) NOT NULL,
	currency_code VARCHAR( 10 ) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql);
	
    $pay_info = $prefix . "ign_pay_info";

    $sql = "CREATE TABLE " . $pay_info . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	first_name VARCHAR( 250 ) NOT NULL ,
	last_name VARCHAR( 250 ) NOT NULL ,
	email VARCHAR( 250 ) NOT NULL ,
	address VARCHAR( 250 ) NOT NULL ,
	country VARCHAR( 250 ) NOT NULL ,
	state VARCHAR( 250 ) NOT NULL ,
	city VARCHAR( 250 ) NOT NULL ,
	zip VARCHAR( 250 ) NOT NULL ,
	product_id INT( 20 ) NOT NULL ,
	transaction_id varchar( 250 ) NOT NULL,
	preapproval_key varchar (250) NOT NULL,
	product_level INT( 2 ) NOT NULL,
	prod_price VARCHAR(200) NOT NULL,
	status VARCHAR( 250 ) NOT NULL DEFAULT 'P',
	created_at DATETIME, 
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql);
    
	// Payment selection settings
	$pay_method_selection = $prefix . "ign_pay_selection";
    
    $sql_pay_sett = "CREATE TABLE " . $pay_method_selection . " (
	id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
	payment_gateway VARCHAR( 100 ) NOT NULL,
	modified_date DATETIME NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql_pay_sett);
	
	// Standard Payment settings
	$pay_settings = $prefix . "ign_pay_settings";

    $sql_pay_sett = "CREATE TABLE " . $pay_settings . " (
	id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
	identity_token VARCHAR( 250 ) NOT NULL,
	paypal_email VARCHAR( 250 ) NOT NULL,
	paypal_override TINYINT( 1 ) NOT NULL,
	paypal_mode ENUM('sandbox','production') NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql_pay_sett);
	
	// Payment settings for Adaptive payments
	$adaptive_pay_settings = $prefix . "ign_adaptive_pay_settings";
    
    $sql_pay_sett = "CREATE TABLE " . $adaptive_pay_settings . " (
	id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
	paypal_email VARCHAR( 100 ) NOT NULL,
	app_id VARCHAR( 100 ) NOT NULL,
	api_username VARCHAR( 100 ) NOT NULL,
	api_password VARCHAR( 100 ) NOT NULL,
	api_signature VARCHAR( 200 ) NOT NULL,
	pre_approval_key VARCHAR( 100 ) NOT NULL,
	paypal_mode ENUM('sandbox','production') NOT NULL,
	fund_type ENUM('standard', 'fixed') NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql_pay_sett);

    // Mailchimp
    $mailchimp_subscription = $prefix . "ign_mailchimp_subscription";

    $sql_pay_sett = "CREATE TABLE " . $mailchimp_subscription . " (
	id mediumint(9) NOT NULL AUTO_INCREMENT,
	api_key VARCHAR( 250 ) NOT NULL ,
	list_id VARCHAR( 250 ) NOT NULL,
	region VARCHAR( 50 ) NOT NULL ,
	is_active TINYINT( 2 ) NOT NULL,
	UNIQUE KEY id (id)
	) $charset_collate;";
    dbDelta($sql_pay_sett);

    $form_settings = $prefix . "ign_form";

    $sql = "CREATE TABLE " . $form_settings . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    form_settings TEXT NOT NULL ,
    UNIQUE KEY id (id)
    ) $charset_collate;";
    dbDelta($sql);
	
    $ign_form_settings = $prefix . "ign_prod_default_settings";

    $sql = "CREATE TABLE " . $ign_form_settings . " (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    form_settings TEXT NOT NULL,
	currency_code VARCHAR( 10 ) NOT NULL DEFAULT 'USD',
    UNIQUE KEY id (id)
    ) $charset_collate;";
    dbDelta($sql);

    $ign_deck_settings = $prefix."ign_deck_settings";

    $sql = "CREATE TABLE ".$ign_deck_settings." (
    	id mediumint(9) NOT NULL AUTO_INCREMENT,
    	attributes LONGTEXT NOT NULL,
    	UNIQUE KEY id (id)
    ) $charset_collate;";
	dbDelta($sql);

    $url = dirname(dirname(dirname(dirname(__FILE__))));
	if (file_exists($url.'/log.txt')) {
		unlink($url.'/log.txt');
	}

	do_action('id_after_install');

}

function ign_set_defaults() {
	global $wpdb;
	global $ign_installed_ver;
	// auto activate logo on widget
	$check_db = 'SELECT id_widget_logo_on FROM '.$wpdb->prefix.'ign_settings WHERE id="1"';
	$return_check = $wpdb->get_row($check_db);

	if( !isset($return_check) ) {
		$sql = "INSERT INTO ".$wpdb->prefix."ign_settings (id_widget_logo_on) VALUES ('1')";
		$update = $wpdb->query($sql);
		idf_flush_object('idcf-getSettings');
	}
	update_option('id_email_inactive', 1);
	do_action('id_set_defaults');

	// auto insert project template on project pages
	if (empty($ign_installed_ver)) {
		update_option('idcf_auto_insert', 1);
	}
}

//register_deactivation_hook( __FILE__, 'ignitiondeck_deactivate' );

function ignitiondeck_deactivate(){
    global $wpdb;
    $check = 'SELECT * FROM '.$wpdb->prefix.'ign_pay_settings WHERE id = 1';
    $return_check = $wpdb->get_results($check);
    if (isset($return_check->charge_mode)) {
    	$sql = 'ALTER TABLE '.$wpdb->prefix.'ign_pay_settings DROP COLUMN charge_mode';
    	$wpdb->query($sql);
    }
    $check2 = 'SELECT * FROM '.$wpdb->prefix.'ign_adaptive_pay_settings WHERE id = 1';
    $return_check2 = $wpdb->get_results($check2);
    if (isset($return_check2->charge_mode)) {
    	$sql2 = 'ALTER TABLE '.$wpdb->prefix.'ign_adaptive_pay_settings DROP COLUMN charge_mode';
    	$wpdb->query($sql2);
    }
}

if (is_id_network_activated() && is_id_pro()) {
	add_action('delete_blog', 'ignitiondeck_uninstall', 1, 1);
	register_uninstall_hook(__FILE__,'id_remove_all_traces');
}
else {
	register_uninstall_hook(__FILE__, 'ignitiondeck_uninstall');
}

function id_remove_all_traces() {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->base_prefix.'blogs';
	$res = $wpdb->get_results($sql);
	foreach ($res as $blog) {
		ignitiondeck_uninstall($blog->blog_id);
	}
}

function ignitiondeck_uninstall($blog_id = null) {
	global $wpdb;
	if (!empty($blog_id) && is_id_network_activated() && is_id_pro()) {
		if ($blog_id == 1) {
			$wpdb->base_prefix = $wpdb->base_prefix;
		}
		else {
			$wpdb->base_prefix = $wpdb->base_prefix.$blog_id.'_';
		}
	}
	else if (!empty($blog_id) && is_id_pro()) {
		if ($blog_id == 1) {
			$wpdb->base_prefix = $wpdb->prefix;
		}
		else {
			$wpdb->base_prefix = $wpdb->prefix.$blog_id.'_';
		}
	}
	else {
		$wpdb->base_prefix = $wpdb->prefix;
	}
	$sql = 'DROP TABLE IF EXISTS '.$wpdb->base_prefix.'ign_adaptive_pay_settings, '.$wpdb->base_prefix.'ign_aweber_settings, '.$wpdb->base_prefix
	.'ign_customers, '.$wpdb->base_prefix.'ign_facebookapp_settings, '.$wpdb->base_prefix.'ign_form, '.$wpdb->base_prefix.'ign_mailchimp_subscription, '.
	$wpdb->base_prefix.'ign_pay_info, '.$wpdb->base_prefix.'ign_pay_selection, '.$wpdb->base_prefix.'ign_pay_settings, '.$wpdb->base_prefix
	.'ign_products, '.$wpdb->base_prefix.'ign_product_settings, '.$wpdb->base_prefix.'ign_prod_default_settings, '.$wpdb->base_prefix.'ign_questions, '.
	$wpdb->base_prefix.'ign_settings, '.$wpdb->base_prefix.'ign_twitterapp_settings, '.$wpdb->base_prefix.'ign_deck_settings';
	$res = $wpdb->query($sql);

	$options = array(
		'id_license_key',
		'is_id_pro',
		'is_id_basic',
		'id_settings_option',
		'id_defaults_notice',
		'id_settings_notice',
		'id_products_notice',
		'id_purchase_default',
		'id_ty_default',
		'id_email_inactive',
		'ign_db_version',
		);
	foreach ($options as $option) {
		delete_option($option);
	}
	ID_Project::delete_project_posts();
}
/*
End Pro Activation, Multisite Activation, Standard Activation
*/

define( 'ID_PATH', plugin_dir_path(__FILE__) );

include_once 'classes/class-id_form.php';
include_once 'classes/class-project_widget.php';
include_once 'classes/class-id_project.php';
include_once 'classes/class-deck.php';
if (is_id_pro()) {
	include_once 'classes/class-id_fes.php';
	include_once 'classes/class-id_fes_team_info.php';
}
include_once 'classes/class-id_order.php';
include_once 'ignitiondeck-functions.php';
include_once 'ignitiondeck-cron.php';
include_once 'ignitiondeck-admin.php';
include_once 'ignitiondeck-postmeta.php';
include_once 'ignitiondeck-shortcodes.php';
//include_once 'ignitiondeck-globals.php';
if (is_id_pro()) {
	include_once 'ignitiondeck-enterprise.php';
}
include_once 'ignitiondeck-update.php';
$active_plugins = get_option('active_plugins', true);
if (in_array('ignitiondeck/idf.php', $active_plugins) && is_id_licensed()) {
	include_once plugin_dir_path(dirname(__FILE__)).'/ignitiondeck/idf.php';
}
else if (is_multisite() && is_id_network_activated() && file_exists(plugin_dir_path(dirname(__FILE__)).'/ignitiondeck/idf.php')) {
	include_once plugin_dir_path(dirname(__FILE__)).'/ignitiondeck/idf.php';
}
if (idf_exists()) {
	include_once 'idf/ignitiondeck-idf.php';
}
if (idf_exists() && idf_platform() == 'idc') {
	include_once 'ignitiondeck-idc.php';
}
include_once 'ignitiondeck-api.php';
include_once 'ignitiondeck-filters.php';
/*if (idf_has_gutenberg()) {
	include_once 'ignitiondeck-blocks.php';
}*/

/**
 * Register ignitiondeck domain for translation texts
 */
add_action( 'init', 'languageLoad' );
function languageLoad() {
	//global $wp_filter;
	//print_r($wp_filter);
	load_plugin_textdomain( 'ignitiondeck', false, dirname( plugin_basename( __FILE__ ) ).'/languages/' );
	add_filter('gettext', 'id_text_filters', 21, 3);
	add_filter('ngettext', 'id_text_filters_n', 21, 5);
	//add_filter('gettext_with_context', 'id_context_text_filters', 21, 4);
}

// Deregister Woo Media Uploader on Project Pages
function disable_woo_media($hook) {
	global $post_type;
	if ($post_type == 'ignition_product') {
		wp_dequeue_script('woo-medialibrary-uploader');
		wp_deregister_script('woo-medialibrary-uploader');
	}
}
add_action('wp_print_scripts', 'disable_woo_media', 20);

/**
 * Include stylesheets
 */
function enqueue_front_css(){

	$theme_name = getThemeFileName();
	
    wp_register_style('ignitiondeck-base', plugins_url('/ignitiondeck-base.css', __FILE__));
    if (file_exists(ID_PATH.'/ignitiondeck-custom.css')) {
    	wp_register_style('ignitiondeck-custom', plugins_url('/ignitiondeck-custom.css', __FILE__));
    	wp_enqueue_style('ignitiondeck-custom');
    }
    wp_register_style($theme_name, plugins_url('/skins/'.$theme_name.'-min.css', __FILE__));
    wp_enqueue_style('ignitiondeck-base');
    wp_enqueue_style($theme_name);
}
add_action('wp_enqueue_scripts', 'enqueue_front_css');

/**
 * includeJavascript files
 */
function enqueue_front_js() {
    wp_enqueue_script('jquery');
    wp_enqueue_script( 'ignitiondeck' );
    $settings = getSettings();
	if (is_multisite() && is_id_network_activated()) {
		$id_ajaxurl = network_home_url('/', 'relative').'wp-admin/admin-ajax.php';
	}
	else {
    	$id_ajaxurl = site_url('/', 'relative').'wp-admin/admin-ajax.php';
    }
    $id_siteurl = site_url('/');
    wp_localize_script('ignitiondeck', 'id_ajaxurl', $id_ajaxurl);
    wp_localize_script('ignitiondeck', 'id_siteurl', $id_siteurl);
}
add_action('wp_enqueue_scripts', 'enqueue_front_js');

// Initializing our widget in the admin area
add_action( 'widgets_init', 'showproduct_load_widgets' );
function showproduct_load_widgets() {
    register_widget( 'Product_Widget' );
}

/*
 *
 */
function id_query_vars($vars) {
	// add my_plugin to the valid list of variables
	$new_vars = array('ipn_handler', 'fname', 'lname', 'email', 'address', 'country', 'state', 'city', 'zip', 'product_id', 'level', 'prod_price');
	if (is_array($vars))
		$vars = array_merge($vars, $new_vars); //$vars = $new_vars + vars;
    return $vars;
}
add_filter('query_vars', 'id_query_vars');

function embedWidget() {
	global $wpdb;
	$tz = get_option('timezone_string');
	if (empty($tz)) {
		$tz = 'UTC';
	}
	date_default_timezone_set($tz);
	$theme_name = getThemeFileName();
	
	echo "<link rel='stylesheet' id='ignitiondeck-iframe-css'  href='".plugins_url('/ignitiondeck-iframe-min.css?ver=3.1.3', __FILE__)."' type='text/css' media='all' />";
	if (isset($_GET['product_no'])) {
		$project_id = $_GET['product_no'];
	}

	if (!empty($project_id)) {
		$deck = new Deck($project_id);
		$the_deck = $deck->the_deck();
		$post_id = $deck->get_project_postid();

		$project_desc = get_post_meta( $post_id, "ign_project_description", true );
		$project_desc = get_post_meta( $post_id, "ign_project_description", true );
		
		//GETTING the main settings of ignitiondeck
		$settings = getSettings();
		$logo_on = true;
		if (is_id_pro() && $settings->id_widget_logo_on !== '1') {
			$logo_on = false;
		}
		
		//GETTING project URL
		$product_url = getProjectURLfromType($project_id);
		
		include 'templates/_embedWidget.php';
	}
	exit;
}
if (isset($_GET['ig_embed_widget'])) {
	add_action('init', 'embedWidget');
}

/*
 *  Adding METABoxes code for displaying widget short codes
 */

add_action( 'add_meta_boxes', 'add_project_url' );
if (is_id_licensed()) {
	add_action( 'add_meta_boxes', 'add_purchase_url' );
	add_action( 'add_meta_boxes', 'shortcode_side_meta' );
	add_action( 'add_meta_boxes', 'shortcode_on_post' );
	add_action( 'add_meta_boxes', 'shortcode_on_page' );
	add_action( 'add_meta_boxes', 'add_project_parent' );
}

/* Adds a box to the main column on the Post and Page edit screens */
function shortcode_side_meta() {
	global $post;
	if (isset($post) && $post->filter == 'edit') {
    	add_meta_box("shortcode_meta", __("Shortcodes", "ignitiondeck"), "add_shortcode_meta", "ignition_product", "side", "low");
    }
}
function shortcode_on_post() {
    add_meta_box("shortcode_meta", __("IgnitionDeck Shortcodes", "ignitiondeck"), "shortcode_normal_post", "post", "side", "default");
}
function shortcode_on_page() {
    add_meta_box("shortcode_meta", __("IgnitionDeck Shortcodes", "ignitiondeck"), "shortcode_normal_post", "page", "side", "default");
}
function add_project_url() {
	add_meta_box("add_project_url_box", __("Project URL", "ignitiondeck"), "add_project_url_box", "ignition_product", "side", "default");
}
function add_purchase_url() {
	add_meta_box("add_purchase_url_box", __("Purchase URL", "ignitiondeck"), "add_purchase_url_box", "ignition_product", "side", "default");
}
function add_project_parent() {
	add_meta_box("add_project_parent_box", __("Project Parent", "ignitiondeck"), "add_project_parent_box", "ignition_product", "side", "default");
}
/* Prints the box content */
function add_shortcode_meta( $post ) {
	// USE nonce for verification
  	wp_nonce_field( plugin_basename( __FILE__ ), 'ignitiondeck' );
	
  	// THE output
	getAllShortCodes();
}
/* TO print the shortcodes in the Post/Page adding screen in meta box */
function shortcode_normal_post ($post) {
	// USE nonce for verification
  	wp_nonce_field( plugin_basename( __FILE__ ), 'ignitiondeck' );
	
	// THE output
	getShortCodesPostPage();
}

// To place a box on the right sidebar of Add New Project page
function add_project_url_box ($post) {
	//echo $post->ID;
	
	echo '<input type="hidden" name="add_project_url_box_nonce" value="'. wp_create_nonce('add_project_url_box'). '" />';
	echo '<table width="100%" border="0">
			<tr>
				<td>&nbsp;</td>
				<td></td>
			</tr>
			<tr>
				<td>'.__('Project Page URL', 'ignitiondeck').'</td>
				<td>
					<select name="ign_option_project_url" id="select_pageurls" onchange=storeurladdress();>
						<option value="current_page" '.((get_post_meta($post->ID, 'ign_option_project_url', true) == "current_page") ? 'selected' : '').'>'.__('Current Project Page', 'ignitiondeck').'</option>
						<option value="page_or_post" '.((get_post_meta($post->ID, 'ign_option_project_url', true) == "page_or_post") ? 'selected' : '').'>'.__('Page or Post', 'ignitiondeck').'</option>
						<option value="external_url" '.((get_post_meta($post->ID, 'ign_option_project_url', true) == "external_url") ? 'selected' : '').'>'.__('External URL', 'ignitiondeck').'</option>
					</select>
				</td>
			</tr>
			<tr>
			<td>
			</td>
			</tr>
			<tr>
			<td>
			</td>
			<td '.((get_post_meta($post->ID, 'ign_option_project_url', true) == "external_url") ? 'style="display:block;"' : 'style="display:none;"').' id="proj_url_cont" ><input class="product-url-container" name="id_project_URL" type="text" id="id_project_URL" value="'.get_post_meta($post->ID, 'id_project_URL', true).'"></td>
			</tr>
			<tr>
			<td>
			</td>';
			?>
            
			<td>
			<div id="proj_posts" <?php echo ((get_post_meta($post->ID, 'ign_option_project_url', true) == "page_or_post") ? 'style="display:block;"' : 'style="display:none;"') ?>>
			<?php
			global $wpdb;

			$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE (post_type = 'post' OR post_type = 'page') AND post_status = 'publish' ORDER BY post_title ASC";
			$results = $wpdb->get_results( $sql );
			?>
            <select name="ign_post_name" id="posts_pro">
            	<option value=""><?php _e('Select', 'ignitiondeck'); ?></option>
				<?php
				$post_name_value = get_post_meta($post->ID, 'ign_post_name', true);
				foreach( $results as $single_post ) {
					//setup_postdata($post);
					echo '<option value="'.$single_post->post_name.'" '.(($post_name_value == $single_post->post_name) ? 'selected' : '').'>'.$single_post->post_title.'</option>';
				}
				?>
            </select>
            </td>
          <?php
			echo '</div>
			</td>
			</tr>
		  </table>';
}

// To place a box on the right sidebar of Add New Project page
function add_purchase_url_box ($post) {
	//echo $post->ID;
	
	echo '<input type="hidden" name="add_purchase_url_box_nonce" value="'. wp_create_nonce('add_purchase_url_box'). '" />';
	echo '<table width="100%" border="0">
			<tr>
				<td>&nbsp;</td>
				<td></td>
			</tr>
			<tr>
				<td>Checkout Page</td>
				<td>
					<select name="ign_option_purchase_url" id="select_purchase_pageurls" onchange=storepurchaseurladdress();>
						<option value="default" '.((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "default") ? 'selected' : '').'>'.__('Default', 'ignitiondeck').'</option>
						<option value="current_page" '.((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "current_page") ? 'selected' : '').'>Current Project Page</option>
						<option value="page_or_post" '.((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "page_or_post") ? 'selected' : '').'>Page/Post</option>
						<option value="external_url" '.((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "external_url") ? 'selected' : '').'>External URL</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>
				</td>
			</tr>
			<tr>
				<td>
				</td>
				<td '.((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "external_url") ? 'style="display:block;"' : 'style="display:none;"').' id="purchase_url_cont" >
					<input class="purchase-url-container" name="purchase_project_URL" type="text" id="purchase_project_URL" value="'.get_post_meta($post->ID, 'purchase_project_URL', true).'">
				</td>
			</tr>
			<tr>
				<td>
				</td>';
			?>
            
			<td>
				<div id="purchase_posts" <?php echo ((get_post_meta($post->ID, 'ign_option_purchase_url', true) == "page_or_post") ? 'style="display:block;"' : 'style="display:none;"') ?>>
			<?php
			global $wpdb;

			$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE (post_type = 'ignition_product' OR post_type = 'post' OR post_type = 'page') AND post_status = 'publish' ORDER BY post_title ASC";
			$results = $wpdb->get_results( $sql );
			?>
            	<select name="ign_purchase_post_name" id="purchase_posts_pro">
            		<option value="">Select</option>
					<?php
					$post_name_value = get_post_meta($post->ID, 'ign_purchase_post_name', true);
					foreach( $results as $single_post ) {
						//setup_postdata($post);
						echo '<option value="'.$single_post->post_name.'" '.(($post_name_value == $single_post->post_name) ? 'selected' : '').'>'.$single_post->post_title.'</option>';
					}
					?>
	            </select>
            </td>
          <?php
			echo '</div>
			</td>
			</tr>
		  </table>';
}

function add_project_parent_box($post) {
	// Getting the parent if any for auto selection
	$parent_id = get_post_meta( $post->ID, 'ign_project_parent', true );
	// Getting the list of ID projects
	$projects = ID_Project::get_project_posts();
	// If the screen is edit post, then don't show the current post id in dropdown
	if (isset($_GET['action']) && $_GET['action'] == 'edit') {
		$screen = 'edit';
	} else {
		$screen = 'add';
	}

	// Making the markup
	echo '<input type="hidden" name="add_project_parent_box" value="'. wp_create_nonce('add_project_parent_box'). '" />';
	echo '<table width="100%" border="0">
			<tr>
				<td>&nbsp;</td>
				<td></td>
			</tr>
			<tr>
				<td>Parent Project</td>
				<td>
					<select name="ign_option_project_parent" id="ign_option_project_parent">
						<option value="">'.__('No Parent', 'ignitiondeck').'</option>';
	if (!empty($projects)) {
		foreach ($projects as $project) {
			if ($screen == "add" || ($screen == 'edit' && $post->ID != $project->ID)) {
				echo '		<option value="'.$project->ID.'" '.(($parent_id == $project->ID) ? 'selected="selected"' : '').'>'.$project->post_title.'</option>';
			}
		}
	}
	echo '			</select>
				</td>
				
			</tr>
			<tr>
				<td>
				</td>
			</tr>
			
		</table>';

}

function idf_exists() {
	return (class_exists('IDF'));
}

function is_id_pro() {
	// do some validation here to check serial number
	$is_pro = get_transient('is_id_pro');
	if (!$is_pro) {
		$is_pro = get_option('is_id_pro', false);
	}
	if ($is_pro) {
		return true;
	}
	else {
		return false;
	}
}

function was_id_pro() {
	return get_option('was_id_pro', false);
}

function is_id_basic() {
	$is_basic = get_transient('is_id_basic');
	if (!$is_basic) {
		$is_basic = get_option('is_id_basic', false);
	}
	return $is_basic;
}

function is_id_licensed() {
	// default idcf features are now free
	return true;
	$is_pro = is_id_pro();
	$is_basic = is_id_basic();
	if ($is_pro || $is_basic || $was_licensed) {
		return true;
	}
	else {
		return false;
	}
}

function was_id_licensed() {
	return get_option('was_id_licensed', false);
}

add_action('activated_plugin','id_save_error');
function id_save_error(){
    update_option('id_plugin_error',  ob_get_contents());
}

//add_action('init', 'id_print_error');

function id_print_error() {
	echo get_option('id_plugin_error');
}

//add_action('init', 'id_debug');

function id_debug() {

}
?>