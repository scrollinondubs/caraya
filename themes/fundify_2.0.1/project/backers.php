<?php
/**
 * Campaign sharing.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $campaign;
global $post;
global $backer_list;
$post_id = $post->ID;
$project_id = get_post_meta($post_id, 'ign_project_id', true);
?>

<div id="backers">
	<?php if ($project_id > 0 && use_idc()) {
		mdid_backers_list($project_id);
	}
	?>
</div>