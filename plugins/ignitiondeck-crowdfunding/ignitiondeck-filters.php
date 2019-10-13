<?php
/**
 * Dynamically filter text values based on project data
 * @param string $translated_text passing through the filter
 * @param string $text passing through the filter
 * @param string $domain passing through the filter
 */
function id_text_filters($translated_text, $text, $domain) {
	$domain_list = array('memberdeck', 'idf', 'ignitiondeck', 'fivehundred', 'crowdpress', 'fundify');
	if (in_array($domain, $domain_list) || strpos($domain, 'fivehundred') !== FALSE) {
		// days left -> hours left
		if (strpos($text, 'Days Left') !== false) {
			$converted_text = idcf_to_hour_hours('plural', $text);
			if ($converted_text !== $text) {
				$translated_text = $converted_text;
			}
		}
	}
	return $translated_text;
}

/**
 * Dynamically filter text values with context based on project data
 * @param string $translated_text passing through the filter
 * @param string $text passing through the filter
 * @param string $context passing through the filter
 * @param string $domain passing through the filter
 */
function id_text_filters_n($translated_text, $single, $plural, $number, $domain) {
	// used primarily by fundify
	$domain_list = array('memberdeck', 'idf', 'ignitiondeck', 'fivehundred', 'crowdpress', 'fundify');
	if (in_array($domain, $domain_list) || strpos($domain, 'fivehundred') !== FALSE) {
		// days left -> hours left
		if (strpos($plural, 'Days Left') !== false) {
			$converted_text = idcf_to_hour_hours('plural', $plural);
		}
		else if (strpos($single, 'Day Left') !== false) {
			$converted_text = idcf_to_hour_hours('singular', $single);
		}
		if (!empty($converted_text)) {
			if ($converted_text !== $single && $converted_text !== $plural) {
				$translated_text = $converted_text;
			}
		}
	}
	return $translated_text;
}

function idcf_to_hour_hours($case, $text) {
	global $post;
	if (!empty($post)) {
		$project_id = get_post_meta($post->ID, 'ign_project_id', true);
		if ($project_id > 0) {
			$project = new ID_Project($project_id);
			$end_date = $project->end_date();
			$tz = get_option('timezone_string');
			if (empty($tz)) {
				$tz = 'UTC';
			}
			date_default_timezone_set($tz);
			if ($end_date == date(idf_date_format())) {
				switch ($case) {
					case 'plural':	
						$text = str_replace('Days', __('Hours', 'memberdeck'), $text);
						break;
					
					case 'singular':		
						$text = str_replace('Day', __('Hour', 'memberdeck'), $text);
						break;
				}
			}
		}
	}
	return $text;
}

/**
Project Filters
*/

/**
 * Automatically adds complete project shortcode to the content when enabled via IDCF Project Settings
 * @param string $content passing through the filter
 */
function idcf_auto_insert($content) {
	global $post;
	if (is_admin()) {
		return $content;
	}
	if (empty($post->post_type)) {
		return $content;
	}
	if (is_id_pro()) {
		return $content;
	}
	$is_id_theme = idf_is_id_theme();
	if ($post->post_type == 'ignition_product' && !$is_id_theme) {
		$auto_insert = get_option('idcf_auto_insert');
		if ($auto_insert) {
			$post_id = $post->ID;
			$project_id = get_post_meta($post_id, 'ign_project_id', true);
			$content = do_shortcode('[project_page_complete product="'.$project_id.'"]');
		}
	}
	return $content;
}

add_filter('the_content', 'idcf_auto_insert');

/**
 * The filter to format the currency display anywhere for project
 * @param integer $amount The amount to be formatted
 * @param integer $post_id The post id of the project
 */
function id_funds_raised($amount) {
	return apply_filters('id_number_format', $amount, '0', '.', '');
}
add_filter('id_funds_raised', 'id_funds_raised', 10, 3);

/**
 * Filter for Percentage pledged for a project
 * @param double  $percentage The percentage value of the project goal
 * @param double  $pledged 		  Pledged of project
 * @param integer $post_id 		  Post ID of the project
 * @param double  $goal 		  Total Goal for the project
 */
function id_percentage_raised($percentage, $pledged, $post_id, $goal) {
	return apply_filters('id_percentage_format', $percentage);
}
add_filter('id_percentage_raised', 'id_percentage_raised', 10, 4);

/**
 * The filter to format the currency display anywhere for project
 * @param integer $goal The amount to be formatted
 * @param integer $post_id The post id of the project
 */
function id_project_goal($goal, $post_id, $noformat = false) {
	if ($noformat) {
		return $goal;
	}
	else {
		return apply_filters('id_display_currency', apply_filters('id_number_format', $goal), $post_id);
	}
}
add_filter('id_project_goal', 'id_project_goal', 10, 3);

/**
 * The filter to format the currency display anywhere for project
 * @param integer $pledges The amount to be formatted
 * @param integer $post_id The post id of the project
 */
function id_number_pledges($pledges, $post_id) {
	return apply_filters('id_number_format', $pledges);
}
add_filter('id_number_pledges', 'id_number_pledges', 10, 2);

/**
 * The filter to format the currency display anywhere for project
 * @param integer $amount The amount to be formatted
 * @param integer $post_id The post id of the project
 */
function id_price_selection($amount, $post_id) {
	return apply_filters('id_display_currency', apply_filters('id_price_format', $amount, $post_id), $post_id);
}
add_filter('id_price_selection', 'id_price_selection', 10, 2);

/**
General Filters
*/

/**
 * The filter to format the currency display anywhere for project
 * @param integer $amount  The amount to be formatted
 * @param integer $post_id The post id of the project
 */
function id_price_format($amount) {
	// Formatting the amount with currency code
	$amount = preg_replace('/[^0-9.]+/', "", $amount);
	if ($amount > 0) {
		$amount = number_format($amount, 2, '.', ',');
	}
	return $amount;
}
add_filter('id_price_format', 'id_price_format', 10, 2);

function id_number_format($number, $dec = 0, $dec_point = '.', $sep = ',') {
	if ($number > 0) {
		$number = number_format($number, $dec, $dec_point, $sep);
	}
	return $number;
}
add_filter('id_number_format', 'id_number_format', 10, 4);

/**
 * Filter for Percentage pledged for a project
 * @param double  $percentage The percentage value of the project goal
 */
function id_percentage_format($percentage) {
	return ($percentage > 0 ? number_format($percentage) : '0');
}
add_filter('id_percentage_format', 'id_percentage_format');

/**
 * Filter for order currency
 */
function id_order_price_filter($post_id, $order) {
	// // Getting the currency of the project, first getting project id if currency code is not coming in the arguments
	// $project_id = get_post_meta($post_id, 'ign_project_id', true);
	// // Now getting currency
	// $project = new ID_Project($project_id);
	// $order = new ID_Order($order_id);
	// $order_info = $order->get_order();
	// // Formatting the amount with currency code
	// if ($order_info->prod_price > 0) {
	// 	// $amount = apply_filters('id_price_format', $order_info->prod_price, $post_id);
	// 	$amount = number_format($order_info->prod_price, 2);
	// }
	// $formatted = apply_filters('id_display_currency', $project->currency_code(), $amount, $order_id);

	// return $formatted;
	if ($order->prod_price > 0) {
		$amount = apply_filters('id_price_format', $order->prod_price, $post_id);
	}
	return $amount;
}
add_filter('id_order_price', 'id_order_price_filter', 2, 2);

/**
 * Filter function to display formatted currency only with currency symbol
 */
function id_display_currency_filter($amount, $post_id) {
	$idf_platform = idf_platform();
	
	if ($idf_platform == 'wc') {
		$currency_code = get_woocommerce_currency_symbol();
		$currency_position = get_option('woocommerce_currency_pos');
	}
	else {
		// Getting the currency of the project, first getting project id if currency code is not coming in the arguments
		$project_id = get_post_meta($post_id, 'ign_project_id', true);
		// Now getting currency
		$project = new ID_Project($project_id);
		$currency_code = $project->currency_code();
		$currency_position = apply_filters('id_currency_symbol_position', 'left', $post_id);
	}
	switch ($currency_position) {
		case 'right':
			$amount = $amount.$currency_code;
			break;
		case 'right_space':
			$amount = $amount.' '.$currency_code;
			break;
		case 'left_space':
			$amount = $currency_code.' '.$amount;
			break;
		default:
			$amount = $currency_code.$amount;
			break;
	}
	return $amount;
}
add_filter('id_display_currency', 'id_display_currency_filter', 2, 2);

/**
Parent/Child Filters
*/

/**
 * The filter to format the currency display anywhere for project
 * @param integer $amount  The amount of the project
 * @param integer $post_id The post id of the project
 */
function id_funds_raised_parent($amount, $post_id) {
	$project_children = get_post_meta($post_id, 'ign_project_children', true);
	if (!empty($project_children)) {
		foreach ($project_children as $child_project) {
			$child_project_id = get_post_meta($child_project, 'ign_project_id', true);
			$project = new ID_Project($child_project_id);
			$raised = $project->get_project_raised(true);
			$amount = $amount + $raised;
			//$sub_children = get_post_meta($child_project, 'ign_project_children', true);
			if (!empty($sub_children)) {
				foreach ($sub_children as $subchild_id) {
					$subchild_project_id = get_post_meta($subchild_id, 'ign_project_id', true);
					$subproject = new ID_Project($subchild_project_id);
					$raised = $subproject->get_project_raised(true);
					$amount = $amount + $raised;
				}
			}
		}
	}
	return $amount;
}
add_filter('id_funds_raised', 'id_funds_raised_parent', 2, 2);

/**
 * Filter to show the number of pledgers of a project and its children
 */
function id_number_pledges_parent($pledgers, $post_id) {
	// Getting the children projects if any to add the total in $amount
	$project_children = get_post_meta($post_id, 'ign_project_children', true);
	if (!empty($project_children)) {
		foreach ($project_children as $child_project) {
			$child_project_id = get_post_meta($child_project, 'ign_project_id', true);
			$project = new ID_Project($child_project_id);
			$orders = $project->get_project_orders();
			$pledgers = $pledgers + $orders;
			$sub_children = get_post_meta($child_project, 'ign_project_children', true);
			if (!empty($sub_children)) {
				foreach ($sub_children as $subchild_id) {
					$subchild_project_id = get_post_meta($subchild_id, 'ign_project_id', true);
					$subproject = new ID_Project($subchild_project_id);
					$orders = $subproject->get_project_orders();
					$pledgers = $pledgers + $orders;
				}
			}
		}
	}
	return $pledgers;
}
add_filter('id_number_pledges', 'id_number_pledges_parent', 2, 2);

/**
 * Filter for Percentage pledged for a project
 * @param double  $rating_percent The percentage value of the project goal
 * @param double  $pledged 		  Pledged of project
 * @param integer $post_id 		  Post ID of the project
 * @param double  $goal 		  Total Goal for the project
 */
function id_percentage_raised_parent($percentage, $pledged, $post_id, $goal) {
	// Calculating the new percentage with children
	if ($goal > 0) {
		$percentage = floatval($pledged / $goal * 100);
	}
	return $percentage;
}
add_filter('id_percentage_raised', 'id_percentage_raised_parent', 2, 4);

/**
 * Filter to remove unused metaboxes from add/edit project screen
 * @param array $fields IDCF project meta fields
 */
function id_postmeta_box_fields($fields) {
	$mode = idcf_mode();
	$i = 0;
	$disabled = array();
	foreach ($fields as $field) {
		if (empty($field['type'])) {
			$disabled[] = $i;
		}
		if (isset($field['id'])) {
			$id = $field['id'];
			if ($id == 'ign_end_type' && ($mode == 'legacy' || $mode == 'idc_free')) {
				$disabled[] = $i;
			}
		}
		$i++;
	}
	foreach ($disabled as $k=>$v) {
		unset($fields[$v]);
	}
	return $fields;
}

add_filter('id_postmeta_box_fields', 'id_postmeta_box_fields');
?>