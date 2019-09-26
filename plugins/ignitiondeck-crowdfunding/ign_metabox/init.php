<?php
/**
 * Adding scripts and styles
 */

function idcf_metabox_scripts() {
	global $post;
	if (!empty($post)) {
		if ( $post->post_type == 'ignition_product' ) {
			wp_register_script( 'idcf-metabox', plugins_url('/idcf_metabox-min.js', __FILE__), array('jquery','media-upload'));
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'idcf-metabox' );
			wp_register_style('idcf-jquery-ui', plugins_url('/idcf_jquery_ui-min.css', __FILE__));
			wp_enqueue_style( 'jquery-custom-ui' );
			wp_enqueue_style( 'idcf-jquery-ui' );
  		}
	}
}

add_action('admin_enqueue_scripts', 'idcf_metabox_scripts');

//$meta_boxes = array();
$meta_boxes = apply_filters('ign_cmb_meta_boxes', array());
if (is_array($meta_boxes)) {
	foreach ( $meta_boxes as $meta_box ) {
		$my_box = new ign_cmb_Meta_Box($meta_box);
	}
}


/*
 * Script url to load local resources.
 */

//define( 'CMB_META_BOX_URL', trailingslashit( str_replace( WP_CONTENT_DIR, WP_CONTENT_URL, dirname(__FILE__) ) ) );
define( 'IDCF_CMB_META_BOX_URL', plugins_url('/ign_metabox/', dirname(__FILE__)));

/**
 * Create meta boxes
 */

class ign_cmb_Meta_Box {
	protected $_meta_box;

	function __construct( $meta_box ) {
		if ( !is_admin() ) return;

		$this->_meta_box = $meta_box;

		$upload = false;
		foreach ( $meta_box['fields'] as $field ) {
			if ( $field['type'] == 'file' || $field['type'] == 'file_list' || $field['type'] == 'wysiwyg') {
				$upload = true;
				break;
			}
		}
		
		$current_page = substr(strrchr($_SERVER['PHP_SELF'], '/'), 1, -4);
		
		if ( $upload && ( $current_page == 'page' || $current_page == 'page-new' || $current_page == 'post' || $current_page == 'post-new' ) ) {
			add_action('admin_head', array(&$this, 'add_post_enctype'));
		}

		add_action( 'admin_menu', array($this, 'add'));
		add_action( 'save_post', array($this, 'save'), 3, 2 );
	}

	function add_post_enctype() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("#post").attr("enctype", "multipart/form-data");
			jQuery("#post").attr("encoding", "multipart/form-data");
		});
		</script>';
	}

	/// Add metaboxes
	function add() {
		$this->_meta_box['context'] = empty($this->_meta_box['context']) ? 'normal' : $this->_meta_box['context'];
		$this->_meta_box['priority'] = empty($this->_meta_box['priority']) ? 'high' : $this->_meta_box['priority'];
		foreach ($this->_meta_box['pages'] as $page) {
			add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
		}
	}

	// Show fields
	function show() {
		global $post;

		// Use nonce for verification
		echo '<input type="hidden" name="id_meta_box_nonce" value="', wp_create_nonce('id_create_edit_project'), '" />';
		echo '<div class="form-table cmb_metabox ignitiondeck">';

		foreach ( $this->_meta_box['fields'] as $field ) {
			// Set up blank values for empty ones
			if ( !isset($field['desc']) ) $field['desc'] = '';
			if ( !isset($field['std']) ) $field['std'] = '';
			if ( !isset($field['id']) ) $field['id'] = '';
			if ( !isset($field['name']) ) $field['name'] = '';
			if ( !isset($field['show_help']) ) $field['show_help'] = false;
			$meta = get_post_meta( $post->ID, $field['id'], 'multicheck' != $field['type'] /* If multicheck this can be multiple values */ );
			
			if ( $field['type'] == "level1wraptop" ) {
				echo '<div level="1" class="projectmeta-levelbox" style="padding: 7px;"><h2>'.__('Level', 'ignitiondeck').' 1 </h2>'; 
			}
			if ( $field['type'] == "level1wrapbottom" ) {
				echo '<div class="clear"></div></div>'; 
			}
			
			echo '<div class="ign_project_meta ', $field['class'], '">';
	
				if ( $field['type'] == "title" ) {
					echo '<div class="idProjectsFields>';
				} 
				else if ($field['type'] == 'checkbox') {
					echo '<div id="', $field['id'], 'Help" class="idMoreinfofull">', $field['desc'], '</div>';
					echo '<div class="idcf_metabox_wrapper">';
					echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
					echo ' ';
					echo '<label for="', $field['id'], '" class="idcf_metabox_label">', $field['name'], '</label> <a href="javascript:toggleDiv(\'', $field['id'], 'Help\');" class="idMoreinfo">[?]</a>';
				}
				else {
					if( $field['show_help'] == true ) {
							echo '<label for="', $field['id'], '" class="idcf_metabox_label">', $field['name'], '</label> <a href="javascript:toggleDiv(\'', $field['id'], 'Help\');" class="idMoreinfo">[?]</a>
							<div id="', $field['id'], 'Help" class="idMoreinfofull">', $field['desc'], '</div>
							';
					} 

					else {			
						echo '<label for="', $field['id'], '" class="idcf_metabox_label">', $field['name'], '</label>';
					}			
					echo '<div class="idcf_metabox_wrapper">';
				}		
				switch ( $field['type'] ) {
					case 'text':
						echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', isset($meta) ? $meta : $field['std'], '" />',
							' ';
						break;
					case 'text_small':
						echo '<input class="cmb_text_small" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', isset($meta) ? $meta : $field['std'], '" /> ';
						break;
					case 'text_medium':
						echo '<input class="cmb_text_medium" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', isset($meta) ? $meta : $field['std'], '" />';
						break;
					case 'text_date':
						echo '<input class="cmb_text_small cmb_datepicker" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', isset($meta) ? $meta : $field['std'], '" /> ';
						break;
					case 'text_money':
						echo '<input class="cmb_text_money" type="text" name="', $field['id'], '" id="', $field['id'], '" value="'. (!empty($meta) ? number_format($meta, 2, '.', ',') : $field['std']). '" /> ';
						break;
					case 'textarea':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" style="width:97%">', isset($meta) ? $meta : $field['std'], '</textarea>',
							'';
						break;
					case 'textarea_code':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="10" style="width:97%">', isset($meta) ? $meta : $field['std'], '</textarea>',
							' ';
						break;					
					case 'textarea_small':
						echo '<textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', isset($meta) ? $meta : $field['std'], '</textarea>',
							' ';
						break;
					case 'textarea_medium':
						echo '<textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="7" style="width:97%">', isset($meta) ? $meta : $field['std'], '</textarea>',
							' ';
						break;					
					case 'select':
						echo '<select name="', $field['id'], '" id="', $field['id'], '">';
						foreach ($field['options'] as $option) {
							echo '<option value="', $option['value'], '"', $meta == $option['value'] ? ' selected="selected"' : '', '>', $option['name'], '</option>';
						}
						echo '</select>';
						echo ' ';
						break;
					case 'radio_inline':
						echo '<div class="cmb_radio_inline">';
						foreach ($field['options'] as $option) {
							echo '<span class="cmb_radio_inline_option"><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'], '</span> ';
						}
						echo '</div>';
						echo ' ';
						break;
					case 'radio':
						foreach ($field['options'] as $option) {
							echo '<p><input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'].'</p>';
						}
						echo ' ';
						break;
					/*case 'checkbox':
						echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
						echo ' ';
						break;*/
					case 'multicheck':
						echo '<ul>';
						foreach ( $field['options'] as $value => $name ) {
							// Append `[]` to the name to get multiple values
							// Use in_array() to check whether the current option should be checked
							echo '<li><input type="checkbox" name="', $field['id'], '[]" id="', $field['id'], '" value="', $value, '"', in_array( $value, $meta ) ? ' checked="checked"' : '', ' /><label>', $name, '</label></li>';
						}
						echo '</ul>';
						echo ' ';					
						break;		
					case 'title':
						echo '<h5 class="cmb_metabox_title">', $field['name'], '</h5>';
						echo ' ';
						break;
					case 'wysiwyg':
						echo '<div id="poststuff" class="meta_mce">';
						echo '<div class="customEditor"><textarea class="mce-html" name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="7" style="width:97%">', $meta ? $meta : '', '</textarea></div>';
	                    echo '</div>';
				        echo ' ';
					break;
	/*
					case 'wysiwyg':
						echo '<textarea name="', $field['id'], '" id="', $field['id'], '" class="theEditor" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>';
						echo ' ';	
						break;
	*/
					case 'file_list':
						echo '<input id="upload_file" type="text" size="36" name="', $field['id'], '" value="" />';
						echo '<input class="upload_button button" type="button" value="Upload File" />';
						echo ' ';
						$args = array(
							'post_type' => 'attachment',
							'numberposts' => null,
							'post_status' => null,
							'post_parent' => $post->ID
						);
						$attachments = get_posts($args);
						if ($attachments) {
							echo '<ul class="attach_list">';
							foreach ($attachments as $attachment) {
								echo '<li>'.wp_get_attachment_link($attachment->ID, 'thumbnail', 0, 0, 'Download');
								echo '<span>';
								echo apply_filters('the_title', '&nbsp;'.$attachment->post_title);
								echo '</span></li>';
							}
							echo '</ul>';
						}
						break;
					case 'file':
						echo '<div class="ign_file_upload">';
						echo '<input id="'.$field['id'].'" type="text" size="45" class="', $field['id'], '" name="', $field['id'], '" value="', $meta, '" />';
						echo '<input class="upload_button button add_media" type="button" value="Upload File" data-input="'.$field['id'].'"/>';
						echo '</div>';
						echo '<div class="file_actions ign_file_upload_image">';	
							if ( $meta != '' ) { 
								$check_image = preg_match( '/(^.*\.jpg|jpeg|png|gif|ico*)/i', $meta );
								if ( $check_image ) {
									echo '<div class="img_status submitbox" id="delete-action">';
									echo '<div class="ign_image_crop"><a href="', $meta, '" target="_blank"><img class="ign_image_tiny" src="', $meta, '"></a></div>';
									echo '<a href="#" class="submitdelete deletion remove_file_button" rel="', $field['id'], '">Remove Image</a></div>';
									echo '<div class="clear"></div>';
								} else {
									$parts = explode( "/", $meta );
									for( $i = 0; $i < sizeof( $parts ); ++$i ) {
										$title = $parts[$i];
									} 
									echo 'File: <strong>', $title, '</strong>&nbsp;&nbsp;&nbsp; (<a href="', $meta, '" target="_blank" rel="external">Download</a> / <div class="submitbox" id="delete-action"><a href="?post_id='.$_GET["post"].'&meta_key='.$field['id'].'" class="submitdelete deletion" rel="', $field['id'], '">Remove</a></div>)';
								}	
							}
						echo '</div>'; 
						break;
					case 'product_levels':
						$meta_no_levels = get_post_meta( $post->ID, $name="ign_product_level_count", true );
						$levels_html = '';
						//echo $meta_no_levels;

						if ($meta_no_levels > 0 || $meta_no_levels != "") {
							$levels_html .= 
							'<div levels="'.$meta_no_levels.'">'.
								'<input id="levels" name="level-count" type="hidden" value="'.$meta_no_levels.'"/>';
							for ($i=2 ; $i <= $meta_no_levels ; $i++) {
								$meta_title = stripslashes(get_post_meta( $post->ID, $name="ign_product_level_".($i)."_title", true ));
								$meta_limit = get_post_meta( $post->ID, $name="ign_product_level_".($i)."_limit", true );
								$meta_order = get_post_meta( $post->ID, $name="ign_product_level_".($i)."_order", true );
								if (empty($meta_order)) {
									$meta_order = 0;
								}
								$meta_price = get_post_meta( $post->ID, $name="ign_product_level_".($i)."_price", true );
								$meta_short_desc = stripslashes(get_post_meta( $post->ID, $name="ign_product_level_".($i)."_short_desc", true ));
								$meta_desc = stripslashes(get_post_meta( $post->ID, $name="ign_product_level_".($i)."_desc", true ));
								$levels_html .= 
								'<div'.(($i == 0) ? '' : ' level="'.($i).'"').' class="projectmeta-levelbox">'.
									'<h2>'.__('Level', 'ignitiondeck').' '.($i).' </h2>'.
									'<div class="ign_projectmeta_reward_title">'.
										'<div>'.
											'<label class="idcf_metabox_label">'.__('Level Title', 'ignitiondeck').' </label>'.
										'</div>'.
										'<input class="cmb_text" type="text" name="levels['.$i.'][title]" id="ign_level_'.$i.'" cols="60" value="'.$meta_title.'" />'.
									'</div>'.
										'<div class="ign_projectmeta_reward_left">'.
											'<div class="ign_projectmeta_reward_price">'.
												'<label class="cmb_metabox_description">'.__('Level Price', 'ignitiondeck').' </label>'.
												'<input class="cmb_text_money" type="text" name="levels['.$i.'][price]" id="ign_level_'.$i.'" value="'.$meta_price.'" />'.
											'</div>'.
											'<div class="ign_projectmeta_reward_limit">'.
												'<label class="idcf_metabox_label">'.__('Level Limit', 'ignitiondeck').' </label>'.
												'<input class="cmb_text_small" type="text" name="levels['.$i.'][limit]" id="ign_level_'.$i.'_limit" value="'.$meta_limit.'" />'.
											'</div>'.
											'<div class="ign_projectmeta_reward_limit">'.
												'<label class="idcf_metabox_label">'.__('Level Order', 'ignitiondeck').' </label>'.
												'<input class="cmb_text_small" type="text" name="levels['.$i.'][order]" id="ign_level_'.$i.'_order" value="'.$meta_order.'" />'.
											'</div>'.
										'</div>'.
										'<div class="ign_projectmeta_reward_desc">'.
											'<label class="idcf_metabox_label">'.__('Level Short Description', 'ignitiondeck').' </label>'.
											'<textarea name="levels['.$i.'][short_description]" id="ign_level'.$i.'short_desc" cols="60" rows="4" style="width:97%">'.$meta_short_desc.'</textarea>'.
										'</div>'.
										'<div class="ign_projectmeta_reward_desc">'.
											'<label class="idcf_metabox_label"l>'.__('Level Long Descriptions', 'ignitiondeck').' </label>'.
											'<textarea name="levels['.$i.'][description]" class="tinymce" id="ign_level'.$i.'desc" cols="60" rows="4" style="width:97%">'.$meta_desc.'</textarea>'.
										'</div>'.
									'<div>'.
								'</div>';
							}
							$levels_html .= '</div>';
						} 
						else {
							$levels_html .= '<div levels="1">';
							$levels_html .= '<input id="levels" name="level-count" type="hidden" value="1"/>';
							$levels_html .= '</div>';
						}
						echo apply_filters('id_product_levels_html_admin', $levels_html, $meta_no_levels, $post->ID);
						break;
					case 'add_levels':
						echo '	<div class="submitbox"> <span addlevel="1" class="add-delete-level" id="delete-action"><button class="button-primary">'.__('Add Level', 'ignitiondeck').'</button></span> &nbsp;&nbsp; <span deletelevel="1" class="add-delete-level"> <button class="delete button">'.__('Delete Last Level', 'ignitiondeck').'</button></span> </div>';
						break;
					
					case 'short_code':
						echo '	<div class="shortcode-container">
									<div class="id-projectpage-short-codes"></div>
								</div>';
						break;
					case 'headline1':
						echo '<h1 class="ign_projectmeta_title">'.__('Project Reward Levels', 'ignitiondeck').'</h1>'; 
						break;
					case 'headline2':
						echo '<h1 class="ign_projectmeta_title">'.__('Additional Project Information', 'ignitiondeck').'</h1>'; 
						break;
					case 'headline2':
						echo '<h1 class="ign_projectmeta_title">'.__('Additional Project Information', 'ignitiondeck').'</h1>'; 
						break;
				}
				echo '</div>';
			echo '</div>';
		}
		echo '</div>';
	}

	// Save data from metabox
	function save($post_id, $post)  {
		
		if (empty($_REQUEST['id_meta_box_nonce']) || !wp_verify_nonce($_REQUEST['id_meta_box_nonce'], 'id_create_edit_project')) {
			return $post_id;
		}

		// check autosave
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		if (isset($_POST['wp-preview']) && $_POST['wp-preview'] == 'dopreview') {
			return $post_id;
		}

		// check permissions
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}
		//===================================================================
		//	Saving the post meta field in the product table as well
		//===================================================================
		global $wpdb;
		if (!empty($_POST['ign_product_price'])) {
			$product_price = round(str_replace(",", "", esc_attr($_POST['ign_product_price'])), 2);		//replacing commas with empty
		}
		else {
			$product_price = '';
		}
		
		if ($post->post_type == 'ignition_product') {
			$project_id = get_post_meta($post_id, 'ign_project_id', true);
			if (!empty($project_id)) {
				$product_goal = round(str_replace(",", "", esc_attr($_POST['ign_fund_goal'])), 2);		//replacing commas with empty
				update_post_meta($post_id, 'ign_project_id', $project_id);
				$sql_update = $wpdb->prepare("UPDATE ".$wpdb->prefix."ign_products SET product_name = %s,
					ign_product_title = %s,
					ign_product_limit = %s,
					product_details = %s,
					product_price = %s,
					product_url = %s,
					goal = %s WHERE id = %d", esc_attr($_POST['post_title']), esc_attr($_POST['ign_product_title']), esc_attr($_POST['ign_product_limit']), esc_attr($_POST['ign_product_details']), $product_price, esc_attr($_POST['id_project_URL']).'/', $product_goal, $project_id);
				$wpdb->query( $sql_update );
				do_action('id_update_project', $post_id, $project_id);
				update_option('id_preview_data', serialize($_POST));
				update_option('id_products_notice', 'off');
			} 
			else {
				$product_goal = round(str_replace(",", "", $_POST['ign_fund_goal']), 2);		//replacing commas with empty
				$sql_product = $wpdb->prepare("INSERT INTO ".$wpdb->prefix ."ign_products (
								product_image, 
								product_name, 
								product_url, 
								ign_product_title, 
								ign_product_limit, 
								product_details, 
								product_price, 
								goal, 
								created_at) VALUES (
									'product image',
									%s,
									%s,
									%s,
									%s,
									%s,
									%s,
									%s,
									'".date('Y-m-d H:i:s')."'
								)", esc_attr($_POST['post_title']),
								esc_attr($_POST['id_project_URL']).'/' ,
								esc_attr($_POST['ign_product_title']) , 
								esc_attr($_POST['ign_product_limit']), 
								esc_attr($_POST['ign_product_details']),
								$product_price,
								$product_goal);

				$res = $wpdb->query( $sql_product );
				$product_id = $wpdb->insert_id;
				update_post_meta($post_id, 'ign_project_id', $product_id);
				do_action('id_create_project', $post_id, $product_id);
				update_option('id_products_notice', 'off');
			}
		}		
		//===================================================================

		foreach ( $this->_meta_box['fields'] as $field ) {
			if ( !isset($field['desc']) ) $field['desc'] = '';
			if ( !isset($field['std']) ) $field['std'] = '';
			if ( !isset($field['id']) ) $field['id'] = '';
			if ( !isset($field['name']) ) $field['name'] = '';
			if ( !isset($field['show_help']) ) $field['show_help'] = false;

			$name = $field['id'];
			$old = get_post_meta( $post_id, $name, 'multicheck' != $field['type'] /* If multicheck this can be multiple values */ );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;
			
			if ( $field['type'] == 'wysiwyg' || $field['type'] == 'textarea_medium') {
				$new = wpautop($new);
			}

			if ( ($field['type'] == 'textarea') || ($field['type'] == 'textarea_small') ) {
				$new = htmlspecialchars($new);
			}
			
			if ( ($field['id'] == "ign_product_price") || ($field['id'] == "ign_fund_goal") ) {
				$new = str_replace(",", "", $new);
			}

			if ($field['id'] == "ign_product_name") {
				$new = htmlspecialchars($new);
			}

			if ( 'multicheck' == $field['type'] ) {
				// Do the saving in two steps: first get everything we don't have yet
				// Then get everything we should not have anymore
				if ( empty( $new ) ) {
					$new = array();
				}
				$aNewToAdd = array_diff( $new, $old );
				$aOldToDelete = array_diff( $old, $new );
				foreach ( $aNewToAdd as $newToAdd ) {
					add_post_meta( $post_id, $name, $newToAdd, false );
				}
				foreach ( $aOldToDelete as $oldToDelete ) {
					delete_post_meta( $post_id, $name, $oldToDelete );
				}
			} 
			else if (isset($new) && $new !== $old) {
				update_post_meta($post_id, $name, $new);
			} 
			else if ('' == $new && $old && $field['type'] != 'file') {
				delete_post_meta($post_id, $name, $old);
			}
		}
		
		//===================================================================
		//	Saving the product levels
		//===================================================================
		if (isset($_POST['level-count'])) {
			update_post_meta($post_id, "ign_product_level_count", $_POST['level-count']);
		}
		$j = 2;
		//find a better way to declare this without using +1
		if (isset($_POST['levels'])) {
			$custom_order = false;
			if ($_POST['levels'] > 1 ) {
				foreach ( $_POST['levels'] as $level ) {
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_title", esc_attr($meta_value=$level['title']));
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_limit", $meta_value=$level['limit']);
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_order", $meta_value=$level['order']);
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_price", $meta_value=str_replace(",", "", $level['price']));
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_short_desc", esc_html($meta_value=$level['short_description']));
					update_post_meta($post_id, $meta_key="ign_product_level_".$j."_desc", esc_html($meta_value=$level['description']));
					if ($level['order'] > 0) {
						$custom_order = true;
					}
					$j++;
				}
			}
			update_post_meta($post_id, 'custom_level_order', $custom_order);
		}
			
		//===================================================================
	}
}
?>