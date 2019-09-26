<div class="wrap">
	<?php echo (isset($message) ? $message : '');?>
	<div class="postbox-container" style="width:95%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Default Project Settings', 'ignitiondeck'); ?></span><a href="javascript:toggleDiv('hDefaultset');" class="idMoreinfo">[?]</a></h3>
					<div class="inside">
						<div id="hDefaultset" class="idMoreinfofull">
							<?php _e('This is where you set the defaults for whenever you create a new project on your website.  Whatever you set here, is what each project will default to, unless you set its custom settings below.', 'ignitiondeck'); ?><br><br>
							<?php if ($platform == 'legacy') { ?>
							<?php _e('Currency Code: this is the currency PayPal will collect funds in, as well as the currency that will be displayed publicly.', 'ignitiondeck'); ?><br><br>
							
							<?php _e('Address information: Ask for this if some of your reward levels involve shipping an item.  We highly suggest however, that you email all of your supporters once it’s actually time to ship, to get up to date shipping information from them at that time.', 'ignitiondeck'); ?>
							<?php } ?>
						</div>
						<div>
							<form name="formdefaultsettings" id="formdefaultsettings" action="" method="post">
								<table>
									<?php do_action('idcf_above_project_settings'); ?>
									<?php if ($platform == 'idc') { ?>
									<tr>
										<td><strong><?php _e('Default Purchase Page', 'ignitiondeck'); ?></strong></td>
									</tr>
									<tr>
										<td>
											<select name="ign_option_purchase_url" id="select_purchase_pageurls" onchange=storepurchaseurladdress();>
												<option value="page_or_post" <?php echo (!empty($purchase_default['option']) && $purchase_default['option'] == 'page_or_post' ? 'selected="selected"' : ''); ?>><?php _e('Page or Post', 'ignitiondeck'); ?></option>
												<option value="external_url" <?php echo (!empty($purchase_default['option']) && $purchase_default['option'] == 'external_url' ? 'selected="selected"' : ''); ?>><?php _e('External URL', 'ignitiondeck'); ?></option>
											</select>
										</td>
									</tr>
									<tr>
										<td id="purchase_url_cont" <?php echo (empty($purchase_default['option']) || $purchase_default['option'] !== 'external_url' ? 'style="display: none;"' : ''); ?>>
											<input class="purchase-url-container" name="id_purchase_URL" type="text" id="id_purchase_URL" value="<?php echo (isset($purchase_default['option']) && $purchase_default['option'] == 'external_url' && isset($purchase_default['value']) ? $purchase_default['value'] : ''); ?>">
										</td>
									</tr>
									<tr>
										<td id="purchase_posts" <?php echo (!empty($purchase_default['option']) && $purchase_default['option'] == 'external_url' ? 'style="display: none;"' : ''); ?>>
								            <select name="ign_purchase_post_name" id="">
								            	<option value="0"><?php _e('Select', 'ignitiondeck'); ?></option>
												<?php if ($list->have_posts()) {
													while ($list->have_posts()) {
														$list->the_post();
														$post_id = get_the_ID();
														echo '<option value="'.$post_id.'" '.(!empty($purchase_default['option']) && $purchase_default['option'] == 'page_or_post' && isset($purchase_default['value']) && $purchase_default['value'] == $post_id ? 'selected="selected"' : '').'>'.get_the_title().'</option>';
													}
												} ?>
								            </select>
								        </td>
									</tr>
									<?php } ?>
									<?php if (!is_id_pro()) { ?>
									<tr>
										<td>
											<input type="checkbox" name="auto_insert" id="auto_insert" value="1" <?php echo (isset($auto_insert) && $auto_insert ? 'checked="checked"' : ''); ?> /> <label for="auto_insert"><?php _e('Automatically insert project template', 'ignitiondeck'); ?></label>
										</td>
									</tr>
									<?php } ?>
									<?php do_action('idcf_below_project_settings'); ?>
									<tr>
										<td>&nbsp;</td>
									</tr>
									<tr>
										<td colspan="3">
											<input class="button-primary" type="submit" name="btnSubmitDefaultSettings" id="btnAddOrder" value="<?php echo $submit_default?>" />
										</td>
									</tr>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
