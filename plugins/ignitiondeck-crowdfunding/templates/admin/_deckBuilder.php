<div class="wrap">
	<div class="postbox-container" style="width:95%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="meta-box-sortables" style="min-height:0;">
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('Deck Builder', 'ignitiondeck'); ?></span></h3>
					<div class="inside">
						<p style="width: 50%"><?php _e('Deck Settings', 'ignitiondeck'); ?></p>
						<form method="POST" action="" id="idmsg-settings" name="idmsg-settings">
							<div class="form-select">
								<p>
									<label for="deck_select"><?php _e('Select deck to edit, or create new', 'ignitiondeck'); ?>.</label><br/>
									<select name="deck_select" id="deck_select">
										<option><?php _e('Create New', 'ignitiondeck'); ?></option>
									</select>
								</p>
							</div>
							<div class="form-input">
								<p>
									<label for="deck_title"><?php _e('Deck Title', 'ignitiondeck'); ?></label><br/>
									<input type="text" name="deck_title" id="deck_title" class="deck-attr-text" value="" />
								</p>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_title" id="project_title" class="deck-attr" value="1"/>
								<label for="project_title"><?php _e('Project Title', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_image" id="project_image" class="deck-attr" value="1"/>
								<label for="project_image"><?php _e('Project Thumbnail', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_bar" id="project_bar" class="deck-attr" value="1"/>
								<label for="project_bar"><?php _e('Percentage Bar', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_pledged" id="project_pledged" class="deck-attr" value="1"/>
								<label for="project_pledged"><?php _e('Funds Raised', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_goal" id="project_goal" class="deck-attr" value="1"/>
								<label for="project_goal"><?php _e('Project Goal', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_pledgers" id="project_pledgers" class="deck-attr" value="1"/>
								<label for="project_pledgers"><?php _e('Number of Pledges', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="days_left" id="days_left" class="deck-attr" value="1"/>
								<label for="days_left"><?php _e('Days Left', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_end" id="project_end" class="deck-attr" value="1"/>
								<label for="project_end"><?php _e('End Date', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_button" id="project_button" class="deck-attr" value="1"/>
								<label for="project_button"><?php _e('Donate Button', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_description" id="project_description" class="deck-attr" value="1"/>
								<label for="project_description"><?php _e('Project Description', 'ignitiondeck'); ?></label>
							</div>
							<div class="form-check">
								<input type="checkbox" name="project_levels" id="project_levels" class="deck-attr" value="1"/>
								<label for="project_levels"><?php _e('Levels', 'ignitiondeck'); ?></label>
							</div>
							<div class="submit">
								<input type="submit" name="deck_submit" id="submit" class="button button-primary"/>
								<input type="submit" name="deck_delete" id="deck_delete" class="button" value="Delete Deck" style="display: none;"/>
							</div>
						</form>
					</div>
				</div>
				<div class="postbox">
					<h3 class="hndle"><span><?php _e('General Settings', 'ignitiondeck'); ?></span></h3>
					<div class="inside">
						<form name="formSettings" action="" method="post">
							<ul>
								<li>
									<label for="theme_value" class="title"><?php _e('Deck Skins', 'ignitiondeck'); ?></label>
									<a href="javascript:toggleDiv('hTheme');" class="idMoreinfo">[?]</a>
									<div id="hTheme" class="idMoreinfofull">
									<div class="idSSwrap"><span><?php _e('IgnitionDeck Light', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-1.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('IgnitionDeck Dark', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-1d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('Corporate', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-2.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('Clean', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-3.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('Clean Dark', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-3d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('Skyscraper', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-4.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="idSSwrap"><span><?php _e('Skyscraper Dark', 'ignitiondeck'); ?></span><img src="<?php echo plugins_url('/images/help/ss-4d.jpg', dirname(dirname(__FILE__))); ?>"></div>
									<div class="clear"></div>
									</div>
									<div><select name="theme_value" id="theme_value">
										<option <?php echo (isset($data) && $data->theme_value == "style1" ? 'selected="selected"' : '')?> value="style1"><?php _e('IgnitionDeck Light', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style1-dark" ? 'selected="selected"' : '')?> value="style1-dark"><?php _e('IgnitionDeck Dark', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style2" ? 'selected="selected"' : '')?> value="style2"><?php _e('Clean', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style2-dark" ? 'selected="selected"' : '')?> value="style2-dark"><?php _e('Clean Dark', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style3" ? 'selected="selected"' : '')?> value="style3"><?php _e('Skyscraper', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style3-dark" ? 'selected="selected"' : '')?> value="style3-dark"><?php _e('Skyscraper Dark', 'ignitiondeck'); ?></option>
										<option <?php echo (isset($data) && $data->theme_value == "style4" ? 'selected="selected"' : '')?> value="style4"><?php _e('Corporate', 'ignitiondeck'); ?></option>
										<?php do_action('id_skin'); ?>
									</select></div>
									<br/>
									<label for="skin-instructions" class="title"><?php _e('Skins Instructions', 'ignitiondeck'); ?></label>
									<a href="javascript:toggleDiv('hSkin');" class="idMoreinfo">[?]</a>
									<div id="hSkin" class="idMoreinfofull">
										<p><?php _e('How to add Deck skins', 'ignitiondeck'); ?>:</p>
										<ol>
											<li><?php _e('Upload skin assets to the /skins directory via FTP', 'ignitiondeck'); ?>.</li>
											<li><?php _e('CSS file will be named ignitiondeck-skinname.css. Enter the &lsquo;skinname&rsquo; in the box and click &lsquo;Add Skin&rsquo;', 'ignitiondeck'); ?>.</li>
											<li><?php _e('To delete, select skin and click &lsquo;Delete Skin&rsquo;', 'ignitiondeck'); ?>.</li>
										</ol>
									</div>
									<br/>
									<div>
										<input type="submit" name="add-skin" id="add-skin" class="button" value="<?php _e('Add skin', 'ignitiondeck'); ?>"/>
										<input type="text" name="skin-name" id="skin-name"/>
									</div>
									<br/>
									<div>
										<input type="submit" name="delete-skin" id="delete-skin" class="button" value="<?php _e('Delete skin', 'ignitiondeck'); ?>"/>
										<select name="deleted-skin" id="deleted-skin">
											<option>-- <?php _e('Delete skin', 'ignitiondeck'); ?> --</option>
											<?php echo $deleted_skin_list; ?>
										</select>
									</div>	
								</li>
								
								<li>
									<div><input <?php echo (isset($data) && $data->id_widget_logo_on ? 'checked="checked"' : ''); ?> name="id_widget_logo_on" type="checkbox" id="id_widget_logo_on" class="main-setting" value="1" /> 
									<label for="id_widget_logo_on"><img src="<?php echo plugins_url('/images/ignitiondeck-menu.png', dirname(dirname(__FILE__))); ?>"><?php _e('IgnitionDeck Logo', 'ignitiondeck'); ?></label>
									<a href="javascript:toggleDiv('hLogo');" class="idMoreinfo">[?]</a>
									<div id="hLogo" class="idMoreinfofull">
									<img src="<?php echo plugins_url('/images/help/powered-by-id.jpg', dirname(dirname(__FILE__))); ?>"><?php _e('This allows you to activate and share via the Powered By IgnitionDeck logo that would appear at the bottom of the widget', 'ignitiondeck'); ?>.
									</div></div>
								</li>
								<li>
									<strong><?php _e('Affiliate Settings', 'ignitiondeck'); ?></strong>
									<div>
									<label for="id_widget_link"><?php _e('Affiliate Link', 'ignitiondeck'); ?></label>
									<a href="javascript:toggleDiv('hAffiliate');" class="idMoreinfo">[?]</a>
									<div id="hAffiliate" class="idMoreinfofull">
									<a href="https://ignitiondeck.com/id/affiliate-area/" alt="IgnitionDeck Affiliate" title="IgnitionDeck Affiliate Program" target="_blank"><?php _e('Click here', 'ignitiondeck'); ?></a> <?php _e('to sign up for our referral program, and paste your unique URL here. Set this to http://ignitiondeck.com for default setting.', 'ignitiondeck'); ?>
									</div><br>
									<input name="id_widget_link" type="text" id="id_widget_link" value="<?php echo $affiliate_link; ?>" /> 
									</div>
								</li>
								
								<li>
									<div>
									<?php if(isset($data) && count($data) > 0) {?>
										<input class="button-primary" type="submit" name="btnIgnSettings" id="btnAddOrder" value="<?php _e('Update', 'ignitiondeck'); ?>" />
									<?php } else { ?>
										<input class="button-primary" type="submit" name="btnIgnSettings" id="btnAddOrder" value="<?php _e('Add', 'ignitiondeck'); ?>" />
									<?php } ?>
									</div>
								</li>
							</ul>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>