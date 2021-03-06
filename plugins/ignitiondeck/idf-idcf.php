<?php

function idf_idcf_validate_license($key) {
	$id_account = get_option('id_account');
	$api_url = 'https://www.ignitiondeck.com/id/';
	$query = array(
		'action' => 'md_validate_license',
		'key' => $key,
		'id_account' => $id_account
	);
	$querystring = http_build_query($query);
	$url = $api_url.'?'.$querystring;

	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_REFERER, home_url());
    $response = curl_exec($ch);
    $response_array = array('valid' => false, 'download' => null);
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
    	else {
    		$response_array = idf_process_validation($response);
    	}
    }
   	else {
   		$response_array = idf_process_validation($response);
   	}
	curl_close($ch);
    return array('response' => $response_array['valid'], 'download' => $response_array['download']);
}

function is_idcf_key_valid($data) {
	$valid = 0;
	if (isset($data['response'])) {
		if ($data['response']) {
			if (isset($data['download'])) {
				if ($data['download'] == '30') {
					$valid = 1;
				}
				else if ($validate['download'] == '1') {
					$valid = 1;
				}
			}
		}
	}
	return $valid;
}

function idf_idcf_license_type($data) {
	switch ($data['download']) {
		case '30':
			return 3;
			break;
		case '1':
			return 1;
			break;
		default:
			return 0;
			break;
	}
}

function idcf_mode() {
	$mode = idf_platform();
	if ($mode == 'idc') {
		if (function_exists('is_idc_free') && is_idc_free()) {
			$mode = 'idc_free';
		}
	}
	return $mode;
}

add_action('idcf_license_update', 'idcf_license_update');

function idcf_license_update($license_key) {
	$is_pro = 0;
	$is_basic = 0;
	update_option('id_license_key', $license_key);
	$validate = idf_idcf_validate_license($license_key);
	if (isset($validate['response'])) {
		if ($validate['response']) {
			if (isset($validate['download'])) {
				if ($validate['download'] == '30') {
					$is_pro = 1;
				}
				else if ($validate['download'] == '1') {
					$is_basic = 1;
				}
			}
		}
	}

	update_option('is_id_pro', $is_pro);
	update_option('is_id_basic', $is_basic);
	set_transient('is_id_pro', $is_pro);
	set_transient('is_id_basic', $is_basic);
}

function idf_schedule_twicedaily_idcf_cron() {
	$license_option = get_option('idf_license_entry_options');
	if (empty($license_option) || $license_option == 'keys') {
		$key = get_option('id_license_key');
		idcf_license_update($key);
	}
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_idcf_cron');
?>