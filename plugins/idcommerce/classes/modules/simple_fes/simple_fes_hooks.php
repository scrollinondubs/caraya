<?php

function idc_simple_fes_form_wrapper_class($class) {
	return $class.' simple-fes';
}

function simple_fes_init() {
	do_action('simple_fes_init');
}

function simple_fes_scripts() {
	
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