<?php

function advanced_user_accounts_admin_scripts() {
	global $pagenow;
	if ($pagenow == 'nav-menus.php') {
		$deps = array();
		$deps[] = 'idcommerce-admin-menus';
		$deps[] = 'jquery';
		wp_register_script('advanced_user_accounts_admin', plugins_url('/js/advanced_user_accounts_admin.js', __FILE__), $deps);
		wp_enqueue_script('advanced_user_accounts_admin');
		$localization_array = array(
			'backer_registration' => __('Backer Registration', 'memberdeck'),
			'creator_registration' => __('Creator Registration', 'memberdeck'),
			'backer_registration_slug' => apply_filters('idc_backer_registration_slug', 'backer'),
			'creator_registration_slug' => apply_filters('idc_creator_registration_slug', 'creator'),
		);
		foreach ($localization_array as $k=>$v) {
			wp_localize_script('advanced_user_accounts_admin', $k, $v);
		}
	}
}

function advanced_user_accounts_registration_fields() {
	if (isset($_GET['action']) && $_GET['action'] == 'register') {
		if (isset($_GET['account_type']) && $_GET['account_type'] == apply_filters('idc_backer_registration_slug', 'backer')) {
			echo '<input type="hidden" name="idc_account_type" value="'.apply_filters('idc_backer_registration_slug', 'backer').'"/>';
		}
		else {
			echo '<input type="hidden" name="idc_account_type" value="'.apply_filters('idc_creator_registration_slug', 'creator').'"/>';
		}
	}
}

function advanced_user_accounts_parse_registration($insert, $email, $fields) {
	foreach ($fields as $field) {
		if ($field['name'] == 'idc_account_type') {
			if ($field['value'] == apply_filters('idc_backer_registration_slug', 'backer')) {
				//remove_action('idc_register_success', 'idc_assign_product_on_register', 20, 2);
				add_filter('idc_enable_default_product', 'advanced_user_accounts_disable_default_product', 11);
			}
		}
	}
}

function advanced_user_accounts_disable_default_product() {
	return 0;
}

function advanced_user_accounts_profile_fields() {
	if (current_user_can('create_edit_projects')) {
		$user = wp_get_current_user();
		if (!empty($user)) {
			ob_start();
			$user_id = $user->ID;
			$profile_hero = get_user_meta($user_id, 'profile_hero', true);
			if (!empty($profile_hero)) {
				$profile_hero_data = wp_get_attachment_image_src($profile_hero);
				$profile_hero_url = $profile_hero_data[0];
			}
			$creator_tagline = get_user_meta($user_id, 'creator_tagline', true);
			if (isset($_POST['edit-profile-submit'])) {
				if (isset($_POST['profile_hero'])) {
					$profile_hero = sanitize_text_field($_POST['profile_hero']);
					update_user_meta($user_id, 'profile_hero', $profile_hero);
					// regenerate image display
					$profile_hero_data = wp_get_attachment_image_src($profile_hero);
					$profile_hero_url = $profile_hero_data[0];
				}
				if (isset($_POST['creator_tagline'])) {
					$creator_tagline = sanitize_text_field($_POST['creator_tagline']);
					update_user_meta($user_id, 'creator_tagline', $creator_tagline);
				}
			}
			include_once('templates/creatorProfileFields.php');
			$content = ob_get_contents();
			ob_end_clean();
			echo $content;
		}
	}
}

function advanced_user_accounts_menu_meta_box_items($items) {
	$items[] = array(
		'label' => __('Donor Registration', 'memberdeck'),
		'id' => 'advanced_user_accounts_donor_registration_link',
		'name' => 'menu-item[donor_registration_link]',
		'value' => md_get_durl().idf_get_querystring_prefix().'action=register&account_type='.apply_filters('idc_backer_registration_slug', 'backer')
	);
	$items[] = array(
		'label' => __('Organization Registration', 'memberdeck'),
		'id' => 'advanced_user_accounts_org_registration_link',
		'name' => 'menu-item[org_registration_link]',
		'value' => md_get_durl().idf_get_querystring_prefix().'action=register&account_type='.apply_filters('idc_creator_registration_slug', 'creator')
	);
	return $items;
}

add_action('wp_ajax_idc_add_menu_item', 'idc_add_menu_item');
?>