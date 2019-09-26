<?php
add_action('idf_id_update_account', 'idf_id_update_account');

function idf_id_set_validation_type($type = 'email') {
	update_option('idf_license_entry_options', $type);
}

function idf_id_update_account($id_account) {
	update_option('id_account', $id_account);
	$license_level = idf_id_validate_account($id_account);
	switch ($license_level) {
		case 'ide':
			$is_pro = 1;
			$is_idc_licensed = 1;
			$is_basic = 0;
			break;
		case 'idc':
			$is_idc_licensed = $is_basic = 1;
			$is_pro = 0;
			break;
		default:
			$is_pro = $is_idc_licensed = $is_basic = 0;
			break;
	}
	#devnote we can set transients from the option? Can we push these to idcf/idc php?
	update_option('is_id_pro', $is_pro);
	update_option('is_idc_licensed', $is_idc_licensed);
	update_option('is_id_basic', $is_basic);
	set_transient('is_id_pro', $is_pro);
	set_transient('is_idc_licensed', $is_idc_licensed);
	set_transient('is_id_basic', $is_basic);
}

function idf_id_validate_account($id_account) {
	$download_list = array(
		'ide' => '30',
		'idc' => '29',
		'free' => '1'
	);
	$api_url = 'https://www.ignitiondeck.com/id/';
	$query = array(
		'action' => 'md_validate_account',
		'id_account' => $id_account,
		'download_list' => $download_list
	);
	$querystring = http_build_query($query);
	$url = $api_url.'?'.$querystring;

	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_REFERER, home_url());
    $response = curl_exec($ch);
    if (!$response) {
    	// curl failed https, lets try http
    	curl_close($ch);
    	$api_url = 'http://www.ignitiondeck.com/id/';
    	$url = $api_url.'?'.$querystring;
    	$ch = curl_init($url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    	curl_setopt($ch, CURLOPT_REFERER, home_url());
    	$response = curl_exec($ch);
    	if (!$response) {
    		// final curl fail
    		echo 'Curl error: '.curl_error($ch);
    	}
    }
    curl_close($ch);
    $license_level = idf_process_account_validation($response);
    return array_search($license_level, $download_list);
}

function idf_schedule_twicedaily_id_account_cron() {
	$id_account = get_option('id_account');
	$license_option = get_option('idf_license_entry_options');
	if ($license_option == 'email') {
		idf_id_update_account($id_account);
	}
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_id_account_cron');

function idf_parse_license($key_data) {
	$scale = max($key_data['types']);
	$return = 0;
	switch ($scale) {
		case '1':
			$return = update_option('idf_key', $key_data['keys']['idcf_key']);
			break;
		case '2':
			$return = update_option('idf_key', $key_data['keys']['idc_key']);
			break;
		case '3':
			$return = update_option('idf_key', $key_data['keys']['idcf_key']);
		default:
			$return = update_option('idf_key', '');
			break;
	}
	return $return;
}
?>