<?php

function idc_simple_fes_form_wrapper_class($class) {
	return $class.' simple-fes';
}

function simple_fes_init() {
	do_action('simple_fes_init');
}

function simple_fes_scripts() {
	wp_register_style('simple_fes', plugins_url('/css/style-min.css', __FILE__));
	wp_enqueue_style('simple_fes');
}

function simple_fes_text_filters($translated_text, $text, $domain) {
	$domain_list = array('memberdeck', 'idf', 'ignitiondeck', 'fivehundred', 'fundify', 'crowdpress');
	if (in_array($domain, $domain_list)) {
		if (strpos($text, 'Contribution Level') !== false) {
			$translated_text = __('&nbsp;', 'memberdeck');
		}
		else if (strpos($text, 'Specify your contribution amount for') !== false) {
			$translated_text = __('Finalize your contribution to', 'memberdeck');
		}
	}
	return $translated_text;
}

function idc_simple_fes_level_1_title($title = '', $post_id) {
	return $title;
	//return __('Donate', 'memberdeck');
}

function idc_simple_fes_level_1_limit($limit = '', $post_id) {
	return $limit;
}

function idc_simple_fes_level_1_desc($desc = '', $post_id) {
	return $desc;
}

function idc_simple_fes_level_1_price($price = '', $post_id) {
	return $price;
}

function idc_simple_fes_saved_levels($levels, $post_id) {
	$post = get_post($post_id);
	if (!empty($post)) {
		$level = array();
		$level['title'] = $post->post_title;
		$level['short'] = $post->post_excerpt;
		$level['long'] = $post->post_content;
		$level['price'] = '';
		$levels[] = $level;
	}
	return $levels;
}

function idc_simple_fes_saved_funding_types($funding_types, $post_id) {
	return 'capture';
}
?>