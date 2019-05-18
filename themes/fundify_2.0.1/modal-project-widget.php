<?php
global $post;
$post_id = $post->ID;
if ($post_id > 0) {
	$project_id = get_post_meta($post_id, 'ign_project_id', true);
	if ($project_id > 0) {
		$widget_url = home_url('/').'?ig_embed_widget=1&amp;product_no='.$project_id;
	}
}
?>
<?php if (isset($widget_url)) { ?>
<div class="campaign-widget-preview">
	<h2 class="modal-title"><?php _e( 'Embed a widget on your site', 'fundify' ); ?></h2>

	<div class="campaign-widget-preview-widget">
		<iframe src="<?php echo $widget_url; ?>" width="214" height="366" frameborder="0" scrolling="no" /></iframe>
	</div>

	<div class="campaign-widget-preview-use">
		<p><?php _e( 'Help raise awareness for this campaign by sharing this widget. Simply paste the following HTML code most places on the web.', 'fundify' ); ?></p>

		<p><strong><?php _e( 'Embed Code', 'fundify' ); ?></strong></p>

		<pre>&lt;iframe src="<?php echo $widget_url; ?>" width="214" height="366" frameborder="0" scrolling="no" /&gt;&lt;/iframe&gt;</pre>
	</div>
</div>
<?php } ?>