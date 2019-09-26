<div class="wrap ignOrders">	
<a href="admin.php?page=order_details"> &lt; <?php _e('Return to Orders List', 'ignitiondeck'); ?> List</a>
		<h3><?php _e('Order Details', 'ignitiondeck'); ?></h3>
		<ul>
			<form action="" method="post" name="formEditOrder" id="formEditOrder" data-orderid="<?php echo $order_data->id; ?>" data-order-price="<?php echo $order_data->prod_price; ?>">
				<li class="first">
					<label><?php _e('First Name', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="first_name" name="first_name" value="<?php echo stripslashes(html_entity_decode($order_data->first_name)); ?>" />
					</div>
				</li>
				<li class="second">
					<label><?php _e('Last Name', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="last_name" name="last_name" value="<?php echo stripslashes(html_entity_decode($order_data->last_name)); ?>" />
					</div>
				</li>
				<li>
					<label><?php _e('Email Address', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="email" name="email" value="<?php echo $order_data->email; ?>" />
					</div>
				</li>
				<li>
					<label><?php _e('Streeth Address', 'ignitiondeck'); ?></label>
					<div>
						<textarea name="address" cols="40" rows="4" class="namebox" id="address"><?php echo stripslashes(html_entity_decode($order_data->address)); ?></textarea>
					</div>
				</li>
				<li class="first">
					<label><?php _e('City', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="city" name="city" value="<?php echo stripslashes(html_entity_decode($order_data->city)); ?>" />
					</div>
				</li>
				<li class="second">
					<label><?php _e('State or Territory', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="state" name="state" value="<?php echo stripslashes(html_entity_decode($order_data->state)); ?>" />
					</div>
				</li>
				<li class="first">
					<label><?php _e('Postal Code', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="zip" name="zip" value="<?php echo stripslashes(html_entity_decode($order_data->zip)); ?>" />
					</div>
				</li>
				<li class="second">
					<label><?php _e('Country', 'ignitiondeck'); ?></label>
					<div>
						<input type="text" class="textbox" id="country" name="country" value="<?php echo stripslashes(html_entity_decode($order_data->country)); ?>" />
					</div>
				</li>
				<li>
					<label><?php _e('Project', 'ignitiondeck'); ?></label>
					<div>
						<select class="select" id="product_id" name="product_id">
								<?php
							foreach ($products as $product) {
								$project = new ID_Project($product->id);
								$post_id = $project->get_project_postid();
								echo '<option '.($product->id == absint($order_data->product_id) ? 'selected' : '').' value="'.absint($product->id).'">'.stripslashes(html_entity_decode(get_the_title($post_id))).'</option>';
							}
							?>
						</select>
					</div>
				</li>
				<li id="level-select">
					<label><?php _e('Level', 'ignitiondeck'); ?></label>
					<div><select name="product_level" id="product_level">
							<option value="0"><?php _e('Select Project', 'ignitiondeck'); ?></option>
						</select>
					</div>
				</li>
				<li id="manual-select">
					<label><?php _e('Custom Amount', 'ignitiondeck'); ?></label>
					<div><input type="text" class="textbox" name="manual-input" id="manual-input" data-firstedit="yes" /></div>
				</li>
				<li>
					<label><?php _e('Order Status', 'ignitiondeck'); ?></label>
					<div>
						<select name="status" id="status">
							<option <?php echo (($order_data->status == "P") ? 'selected="selected"' : ''); ?> value="P"><?php _e('Pending', 'ignitiondeck'); ?></option>
							<option <?php echo (($order_data->status == "C") ? 'selected="selected"' : ''); ?> value="C"><?php _e('Complete', 'ignitiondeck'); ?></option>
							<option <?php echo (($order_data->status == "W") ? 'selected="selected"' : ''); ?> value="W"><?php _e('Waiting', 'ignitiondeck'); ?></option>
						</select>
					</div>
				</li>
				<li class="first">
					<div>
						<input class="button button-primary" type="submit" name="btnUpdateOrder" id="btnUpdateOrder" onclick="prodpricefn();" value="<?php _e('Update Order', 'ignitiondeck'); ?>" />
					</div>
				</li>
				<input type="hidden" name="prod_price" id="prod_price" />
			</form>
		</ul>
	</div>