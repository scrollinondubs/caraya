<?php
add_action('init', 'install_ign_metaboxes', 11);

function install_ign_metaboxes() {
	if (is_admin()) {
		global $pagenow;
		if ($pagenow == 'post.php' || $pagenow =='post-new.php') {
			add_filter('ign_cmb_meta_boxes', 'ign_meta_boxes');
			require_once('ign_metabox/init.php');
			// Include & setup custom metabox and fields
		}
	}
}

function ign_meta_boxes(array $meta_boxes) {
	$prefix = 'ign_';
	$id_meta_boxes = array(
	    'id' => 'product_meta',
	    'title' => __('Project', 'ignitiondeck'),
	    'pages' => array('ignition_product'), // post type
		'context' => 'normal',
		'priority' => 'high',
		'class' => $prefix . 'projectmeta',
	    'fields' => array(
			array(
				'name' => __('Campaign End Options', 'ignitiondeck'),
				'desc' => __('Choose how to handle campaign end. Leave open to keep collecting payments, closed to remove pledge button.', 'ignitiondeck'),
				'id' => $prefix.'end_type',
				'class' => '',
				'show_help' => true,
				'options' => array(
					array(
						'name' => __('Never Expires', 'ignitiondeck'),
						'id' => 'open',
						'value' => 'open'
					),
					array(
						'name' => __('Expires on End Date', 'ignitiondeck'),
						'id' => 'closed',
						'value' => 'closed'
					)
				),
				'type' => 'radio_inline'
			),
			array(
		        'name' => __('Funding Goal', 'ignitiondeck'),
		        'desc' => __('Amount you are seeking to raise (required)', 'ignitiondeck'),
		        'id' => $prefix . 'fund_goal',
		        'class' => '',
		        'show_help' => true,
		        'type' => 'text_money'
		    ),
		    (!(function_exists('is_id_pro') || !is_id_pro())) ? array('type' => '') : array(
				'name' => __('Proposed Start Date', 'ignitiondeck'),
				'desc' => __('The date the project creator wishes to start funding', 'ignitiondeck'),
				'id' => $prefix . 'start_date',
				'class' => '',
				'show_help' => true,
				'type' => 'text_date'
			),
		    array(
		        'name' => __('Fundraising End Date', 'ignitiondeck'),
		        'desc' => __('Date funding will end (recommended)', 'ignitiondeck'),
		        'id' => $prefix . 'fund_end',
		        'class' => '',
		        'show_help' => true,
		        'type' => 'text_date'
		    ),
			array(
		        'name' => __('Project Short Description', 'ignitiondeck'),
		        'desc' => __('Used in the grid, widget areas, and on the purchase form', 'ignitiondeck'),
		        'id' => $prefix . 'project_description',
		        'class' => '',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
			array(
		        'name' => __('Project Long Description', 'ignitiondeck'),
		        'desc' => __('Supports HTML. Used on project pages', 'ignitiondeck'),
		        'id' => $prefix . 'project_long_description',
		        'class' => $prefix . 'projectmeta_full tinymce',
		        'show_help' => true,
		        'type' => 'textarea_medium'
		    ),
		    array(
		        'name' => __('Video Embed Code', 'ignitiondeck'),
		        'desc' => __('Video embed code using iframe or embed format (YouTube, Vimeo, etc)', 'ignitiondeck'),
		        'id' => $prefix . 'product_video',
		        'class' => $prefix . 'projectmeta_full',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
		    array(
		        'type' => 'headline1',
		        'class' => $prefix . 'projectmeta_headline1'
		    ),
		    array(
		        'name' => __('Level Title', 'ignitiondeck'),
		        'id' => $prefix . 'product_title',
		        'class' => $prefix . 'projectmeta_reward_title',
		        'show_help' => false,
		        'type' => 'text'
		    ),
			array(
		        'name' => __('Level Price', 'ignitiondeck'),
		        'id' => $prefix . 'product_price',
		        'class' => $prefix . 'projectmeta_reward_price',
		        'type' => 'text_money'
		    ),
		    array(
		        'name' => __('Level Short Description', 'ignitiondeck'),
		        'desc' => __('Used in widgets sidebars, and in some cases, on the purchase form', 'ignitiondeck'),
		        'id' => $prefix . 'product_short_description',
		        'class' => $prefix . 'projectmeta_reward_desc',
		        'show_help' => true,
		        'type' => 'textarea_small'
		    ),
		    array(
		        'name' => __('Level Long Description', 'ignitiondeck'),
		        'desc' => __('For use on the project page and in level shortcodes/widgets', 'ignitiondeck'),
		        'id' => $prefix . 'product_details',
		        'class' => $prefix . 'projectmeta_reward_desc tinymce',
		        'show_help' => true,
		        'type' => 'textarea_medium'
		    ),
		    array(
		        'name' => __('Level Limit', 'ignitiondeck'),
		        'desc' => __('Restrict the number of buyers that can back this level', 'ignitiondeck'),
		        'id' => $prefix . 'product_limit',
		        'class' => $prefix . 'projectmeta_reward_limit',
		        'show_help' => true,
		        'type' => 'text_small'
		    ),
		    array(
		    	'name' => __('Level Order', 'ignitiondeck'),
		    	'desc' => __('Enter a number of 0 (first) or higher if you wish to customize the placement of this level', 'ignitiondeck'),
		    	'id' => $prefix.'projectmeta_level_order',
		    	'class' => $prefix . 'projectmeta_reward_limit',
		    	'show_help' => true,
		    	'type' => 'text_small'
		    ),
			array(
	            'name' => '',
				'std' => '',
	            'id' => $prefix . 'level',
	            'class' => $prefix . 'projectmeta_full new_levels',
	            'show_help' => false,
	            'type' => 'product_levels'
	        ),	
	        array(
	            'name' => '',
	            'id' => $prefix . 'addlevels',
	            'class' => $prefix . 'projectmeta_full new_level',
	            'type' => 'add_levels',
	        ),
	        array(
	            'type' => 'headline2',
	            'class' => $prefix . 'projectmeta_headline2'
	        ),
			array(
	            'name' => __('Project FAQs', 'ignitiondeck'),
	           'desc' => __('List Project FAQs here', 'ignitiondeck'),
	            'id' => $prefix . 'faqs',
	            'class' => $prefix . 'projectmeta_full tinymce',
	            'show_help' => true,
	            'type' => 'textarea_medium'
	        ),
			array(
	            'name' => __('Project Updates', 'ignitiondeck'),
	            'desc' => __('List Project Updates here', 'ignitiondeck'),
	            'id' => $prefix . 'updates',
	            'class' => $prefix . 'projectmeta_full tinymce',
	            'show_help' => true,
	            'type' => 'textarea_medium'
	        ),
	    )
	);
	$fields = apply_filters('id_postmeta_box_fields', $id_meta_boxes['fields']);
	$id_meta_boxes['fields'] = $fields;
	$meta_boxes[] = $id_meta_boxes;
	return apply_filters('id_postmeta_boxes', $meta_boxes);
}
?>