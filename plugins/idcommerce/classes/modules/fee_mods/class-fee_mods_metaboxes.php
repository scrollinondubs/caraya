<?php
class IDC_Fee_Mods_Metaboxes {

	function __construct() {
		add_action('add_meta_boxes', array(&$this, 'generate_metabox'));
		add_action('save_post', array(&$this, 'save_metabox'));
	}

	function generate_metabox($post) {
		$post_types = get_post_types();
		$types = array('ignition_product');
		foreach ($types as $type) {
			add_meta_box('idc_fee_mods', __('Platform Settings', 'memberdeck'), array(&$this, 'render_metabox'), $type, 'normal', 'high');
		}
	}

	function render_metabox($post) {
		wp_nonce_field('idc_fee_mods', 'idc_fee_mods_metabox');
		$application_fee = get_post_meta($post->ID, 'application_fee', true);
		include DIRNAME(__FILE__).'/templates/admin/_feeModsMetaboxContent.php';
	}

	function save_metabox($post_id) {
		if (isset($_POST)) {
			if ( isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		    	if ( !current_user_can( 'edit_page', array($post_id) ) ) {
		    		return;
		    	}
		  	} else {
		   		 if ( !current_user_can( 'edit_posts' ) ) {
		        	return;
		    	}
		  	}
		  	if ( ! isset( $_POST['application_fee'] ) || ! wp_verify_nonce( $_POST['idc_fee_mods_metabox'], 'idc_fee_mods' ) ) {
		      	return;
		  	}
		  	$post_id = absint($_POST['post_ID']);
		  	$application_fee = sanitize_text_field($_POST['application_fee']);
		  	update_post_meta($post_id, 'application_fee', $application_fee);
		}
	}
}
?>