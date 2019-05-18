<div class="wrap">
	<form action="" method="post" name="formAddOrder" id="formAddOrder">
		<table width="100%" border="0" cellspacing="1" cellpadding="1">
			<thead>
				<tr>
					<th colspan="2" style="text-align: left"><?php _e('Order Details', 'ignitiondeck'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width="21%"><?php _e('First Name', 'ignitiondeck'); ?></td>
					<td width="79%"><input type="text" name="first_name" id="first_name" /></td>
				</tr>
				<tr>
					<td><?php _e('Last Name', 'ignitiondeck'); ?></td>
					<td><input type="text" name="last_name" id="last_name" /></td>
				</tr>
				<tr>
					<td><?php _e('Email Address', 'ignitiondeck'); ?></td>
					<td><input type="text" name="email" id="email" /></td>
				</tr>
				<tr>
					<td><?php _e('Address', 'ignitiondeck'); ?></td>
					<td><textarea name="address" cols="40" rows="4" id="address"></textarea></td>
				</tr>
				<tr>
					<td><?php _e('Country', 'ignitiondeck'); ?></td>
					<td><input type="text" name="country" id="country" /></td>
				</tr>
				<tr>
					<td><?php _e('State', 'ignitiondeck'); ?></td>
					<td><input type="text" name="state" id="state" /></td>
				</tr>
				<tr>
					<td><?php _e('City', 'ignitiondeck'); ?></td>
					<td><input type="text" name="city" id="city" /></td>
				</tr>
				<tr>
					<td><?php _e('Postal Code', 'ignitiondeck'); ?></td>
					<td><input type="text" name="zip" id="zip" /></td>
				</tr>
				<tr>
					<td><?php _e('Order Status', 'ignitiondeck'); ?></td>
					<td><select name="status" id="status">
							<option value="P" selected="selected"><?php _e('Pending', 'ignitiondeck'); ?></option>
							<option value="C"><?php _e('Complete', 'ignitiondeck'); ?></option>
						</select></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</tbody>
			<thead>
				<tr>
					<th colspan="2" style="text-align: left"><strong><?php _e('Project', 'ignitiondeck'); ?></strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php _e('Project Name', 'ignitiondeck'); ?></td>
					<td><select name="product_id" id="product_id">
						<?php
						foreach ($products as $product) {
							$project = new ID_project($product->id);
							$post_id = $project->get_project_postid();
							?>
							<option value="<?php echo $product->id; ?>"><?php echo get_the_title($post_id); ?></option>
							<?php
						}
						?>
						</select></td>
				</tr>
				<tr id="level-select">
					<td><?php _e('Project Level', 'ignitiondeck'); ?></td>
					<td><select name="product_level" id="product_level">
							<option value="0"><?php _e('Select Level', 'ignitiondeck'); ?></option>
						</select></td>
				</tr>
				<tr id="manual-select">
					<td><?php echo _e('Enter Custom Value', 'ignitiondeck'); ?></td>
					<td><input type="text" class="textbox" name="manual-input" id="manual-input" /></td>
				</tr>
				<tr>
					
					<td><input type="hidden" name="prod_price" id="prod_price" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input class="button button-primary submitbtn" type="submit" name="btnAddOrder" id="btnAddOrder" onclick="prodpricefn();" value="<?php _e('Add Order', 'ignitiondeck'); ?>" /></td>
				</tr>
			</tbody>
		</table>
	</form>
</div>