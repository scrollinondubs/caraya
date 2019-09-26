<?php
add_action('enqueue_block_editor_assets', 'idcf_block_assets');

function idcf_block_assets() {
	wp_register_script('idcf_blocks', plugins_url('/js/idcf_blocks-min.js', __FILE__), array('wp-blocks', 'wp-element'));
	wp_enqueue_script('idcf_blocks');
}
?>