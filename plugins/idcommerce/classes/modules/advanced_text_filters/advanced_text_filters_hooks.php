<?php
function advanced_text_filters_text($translated_text, $text, $domain) {
	$domain_list = array('memberdeck', 'idf', 'ignitiondeck', 'fivehundred');
	if (in_array($domain, $domain_list)) {
		if (strpos($text, 'backer') !== false) {
			$translated_text = str_replace('backer', __('donor', 'memberdeck'), $text);
		}
		else if (strpos($text, 'Backer') !== false) {
			$translated_text = str_replace('Backer', __('Donor', 'memberdeck'), $text);
		}
		else if (strpos($text, 'Creator') !== false) {
			$translated_text = str_replace('Creator', __('Organization', 'memberdeck'), $text);
		}
		else if (strpos($text, 'creator') !== false) {
			$translated_text = str_replace('creator', __('organization', 'memberdeck'), $text);
		}
	}
	return $translated_text;
}

function advanced_text_filters_backer_profile_slug($slug) {
	return 'donor_profile';
}

function advanced_text_filters_creator_profile_slug($slug) {
	return 'organization_profile';
}

function advanced_text_filters_creator_projects_slug($slug) {
	return 'organization_projects';
}

function advanced_text_filters_backer_registration_slug($slug) {
	return 'donor';
}

function advanced_text_filters_creator_registration_slug($slug) {
	return 'organization';
}
?>