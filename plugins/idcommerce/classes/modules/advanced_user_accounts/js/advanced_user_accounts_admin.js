jQuery(document).ready(function() {
	//console.log('aua-admin loaded');
	var ddownMarkup = 
		'<option value="' + backer_registration + '" data-url="' + durl + idf_prefix + 'action=register&account_type=' + backer_registration_slug + '">' + backer_registration + '</option>' +
		'<option value="' + creator_registration + '" data-url="'+ durl + idf_prefix + 'action=register&account_type=' + creator_registration_slug + '">' + creator_registration + '</option>';
	jQuery('.customlinkdiv select[name="idc_custom_links"] option').last().after(ddownMarkup);
});