<?php
global $post;
$id = $post->ID;
$project_id = get_post_meta($id, 'ign_project_id', true);
?>
<?php echo apply_filters('the_content', do_shortcode('[project_purchase_form]')); ?>