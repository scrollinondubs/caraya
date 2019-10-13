<?php
function is_idc_key_valid($data) {
	$valid = 0;
	if (isset($data['response'])) {
		if ($data['response']) {
			if (isset($data['download'])) {
				if ($data['download'] == '29') {
					$valid = 1;
				}
			}
		}
	}
	return $valid;
}

function idf_idc_license_type($valid) {
	switch ($valid) {
		case 1:
			return 2;
			break;
		
		default:
			return 0;
			break;
	}
}

function idf_idc_validate_key($key) {
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

add_action('idc_license_update', 'idc_license_update');

function idc_license_update($idc_license_key) {
	$valid = 0;
	$general = get_option('md_receipt_settings');
	$general = maybe_unserialize($general);
	$general['license_key'] = $idc_license_key;
	update_option('md_receipt_settings', $general);
	$validate = idf_idc_validate_key($idc_license_key);
	if (isset($validate['response'])) {
		if ($validate['response']) {
			if (isset($validate['download'])) {
				if ($validate['download'] == '29') {
					$valid = 1;
					if (!was_idc_licensed()) {
						update_option('was_idc_licensed', $valid);
					}
				}
			}
		}
	}
	update_option('is_idc_licensed', $valid);
	set_transient('is_idc_licensed', $valid);
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_idc_cron');

function idf_schedule_twicedaily_idc_cron() {
	$license_option = get_option('idf_license_entry_options');
	if (empty($license_option) || $license_option == 'keys') {
		$general = get_option('md_receipt_settings');
		$general = maybe_unserialize($general);
		$idc_license_key = (isset($general['license_key']) ? $general['license_key'] : '');
		idc_license_update($idc_license_key);
	}
}
?>