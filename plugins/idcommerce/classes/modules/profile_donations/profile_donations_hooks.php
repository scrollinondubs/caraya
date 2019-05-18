<?php

function profile_donations_admin() {
	$settings = add_submenu_page('idf', __('Profile Donations', 'memberdeck'), __('Profile Donations', 'memberdeck'), 'manage_options', 'profile-donations', 'profile_donations_menu');
	add_action('admin_print_styles-'.$settings, 'idf_admin_enqueues');
	add_action('admin_print_styles-'.$settings, 'profile_donations_admin_scripts');
}

function profile_donations_admin_scripts() {
	wp_register_script('profile_donations-admin_script', plugins_url('js/profile_donations-admin.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('profile_donations-admin_script');
}

function profile_donations_menu() {
	$settings = get_option('idc_profile_donations_settings');
	$idc_products = ID_Member_Level::get_levels();
	if (isset($_POST['save_profile_donations_settings'])) {
		$settings = array();
		foreach ($_POST as $k=>$v) {
			if ($k !== 'save_profile_donations_settings') {
				if (!empty($v)) {
					$settings[$k] = sanitize_text_field($v);
				}
			}
		}
		update_option('idc_profile_donations_settings', $settings);
	}
	if (!empty($settings['profile_donations_image'])) {
		$profile_donations_image = wp_get_attachment_image_src($settings['profile_donations_image'], 'id_checkout_image');
		$settings['profile_donations_image_url'] = $profile_donations_image[0];
	}
	include_once (dirname(__FILE__) . '/templates/admin/_profileDonationsSettings.php');
}

function profile_donations_display($creator_projects) {
	if (!empty($creator_projects)) {
		add_action('ide_after_backer_data', 'profile_donations_button');
	} else {
		add_action('ide_after_creator_profile', 'profile_donations_button');
	}
	return $creator_projects;
}

function profile_donations_button() {
	if (isset($_GET[apply_filters('idc_creator_profile_slug', 'creator_profile')])) {
		$user_id = absint($_GET[apply_filters('idc_creator_profile_slug', 'creator_profile')]);
		if ($user_id > 0) {

				$current_filter = current_filter();
				$output = '';

				$prefix = idf_get_querystring_prefix();
				$current_user = wp_get_current_user();

				// Getting donation product
				$settings = get_option('idc_profile_donations_settings');
				if (!empty($settings)) {
					$product_id = array();
					foreach ($settings as $k=>$v) {
						if (strpos($k, '_donation_product') > 0) {
							$product_id[] = $v;
						}
					}
					$product_id = implode(',', $product_id);
					// lightbox header image
					$image = wp_get_attachment_image_src($settings['profile_donations_image'], 'id_checkout_image');
					if (!empty($image[0])) {
						$thumb = $image[0];
					}
					$donate_button = do_shortcode('[idc_button type="button" id="direct-donation-button" product="'.$product_id.'" text="'.__('Donate', 'memberdeck').'" thumb="'.(isset($thumb) ? $thumb : '').'" source=".idc_button_lightbox"]');
					
					ob_start();
					include_once(dirname(__FILE__) . '/templates/_donateButton.php');
					$output .= ob_get_contents();
					ob_clean();
				}
				echo $output;
		}
	}
}
?>