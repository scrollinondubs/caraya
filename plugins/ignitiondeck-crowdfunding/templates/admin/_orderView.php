<div class="wrap ignOrders">
<a href="admin.php?page=order_details"> &lt;<?php _e('Return to Orders List', 'ignitiondeck'); ?> List</a>
	<h3><?php _e('Order Details', 'ignitiondeck'); ?></h3>
	<ul>
		<li class="first">
			<label><?php _e('First Name', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->first_name)); ?></div>
		</li>
		<li class="second">
			<label><?php _e('Last Name', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->last_name)); ?></div>
		</li>
		<li>
			<label><?php _e('Email Address', 'ignitiondeck'); ?></label>
			<div><a href="mailto:<?php echo stripslashes(html_entity_decode($order_data->email)); ?>"><?php echo stripslashes(html_entity_decode($order_data->email)); ?></a></div>
		</li>
		<li>
			<label><?php _e('Street Address', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->address)); ?></div>
		</li>
		<li class="first">
			<label><?php _e('City', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->city)); ?></div>
		</li>
		<li class="second">
			<label><?php _e('State or Territory', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->state)); ?></div>
		</li>
		<li class="first">
			<label><?php _e('Postal Code', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->zip)); ?></div>
		</li>
		<li class="second">
			<label><?php _e('Country', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($order_data->country)); ?></div>
		</li>
	</ul>
	<h3><?php _e('Project', 'ignitiondeck'); ?></h3>
	<ul>
		<li>
			<label><?php _e('Project Name', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode(get_the_title($post_id))); ?></div>
		</li>
		<li>
			<label><?php _e('Level', 'ignitiondeck'); ?></label>
			<div><?php echo absint($order_data->product_level); ?></div>
		</li>
		<li>
			<label><?php _e('Payment Total', 'ignitiondeck'); ?></label>
			<div><?php echo $level_price; ?></div>
		</li>
		<?php if (number_format($order_data->prod_price, 2) != number_format($level_price, 2)) { ?>
		<li>
			<label><?php _e('Custom Amount', 'ignitiondeck'); ?></label>
			<div><?php echo $order_data->prod_price; ?></div>
		</li>
		<?php } ?>
		<li>
			<label><?php _e('Level Name', 'ignitiondeck'); ?></label>
			<div><?php echo stripslashes(html_entity_decode($level_desc)); ?></div>
		</li>
		<li>
			<label><?php _e('Order Status', 'ignitiondeck'); ?></label>
			<div><?php echo $order_data->status; ?></div>
		</li>
	</ul>
</div>