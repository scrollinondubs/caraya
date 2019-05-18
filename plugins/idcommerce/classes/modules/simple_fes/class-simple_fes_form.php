<?php
class ID_Simple_Fes_Form {

	var $form;
	var $vars;

	function __construct($form = null, $vars = null) {

	}

	public static function simple_fes_create_form($form = null, $vars = null) {
		$form = apply_filters('id_fes_form_init', $form, $vars);
		$form[] = ID_FES::id_fes_project_name_field($vars);
		$form[] = ID_FES::id_fes_fund_types_single($vars);
		if (empty($vars['status']) || strtoupper($vars['status']) !== 'PUBLISH') {
			// draft or pending review
			$form[] = ID_FES::id_fes_goal_field($vars);
			$form[] = ID_FES::id_fes_cat_form($vars);
		}
		// quick hack to close opening fes_section div
		$form[] = array(
			'after' => '</div>'
		);
		$form[] = ID_FES::id_fes_short_description_field($vars);
		$form[] = ID_FES::id_fes_video_field($vars);
		$form[] = ID_FES::id_fes_project_long_description_field($vars);
		// quick hack to close middle fes_section div
		$form[] = array(
			'after' => '</div>'
		);
		$form[] = ID_FES::id_fes_featured_image_field($vars);
		$form[] = ID_FES::id_fes_featured_image_check_field($vars);
		$form[] = array(
			'label' => '',
			'value' => '1',
			'name' => 'project_levels',
			'id' => 'project_levels',
			'type' => 'hidden',
			'wclass' => '',
			'class' => '',
		);
		$form[] = array(
			'after' => '<div class="border-bottom"></div></div>'
		);
		$submit_button = array(
			'value' => (isset($vars['status']) && strtoupper($vars['status']) == 'PUBLISH' ? __('Update', 'memberdeck') : __('Update Submission', 'memberdeck')),
			'name' => 'project_fesubmit',
			'type' => 'submit',
			'class' => 'project_fesubmit',
			'wclass' => 'form-row'
		);
		if (empty($vars['status']) || strtoupper($vars['status']) == 'DRAFT') {
			$form[] = array(
				'value' => (empty($vars['status']) ? __('Save Draft', 'ignitiondeck') : __('Update Draft', 'ignitiondeck')),
				'name' => 'project_fesave',
				'class' => 'project_fesave',
				'type' => 'submit',
				'wclass' => 'form-row half left'
			);
			$submit_button['value'] = __('Submit for Review', 'memberdeck');
			$submit_button['wclass'] = 'form-row half';
		}
        $form[] = $submit_button;
        if (isset($vars['post_id']) && $vars['post_id'] > 0) {
		$form[] = array(
			'value' => $vars['post_id'],
			'name' => 'project_post_id',
			'type' => 'hidden');
		}
		return $form;
	}

	public static function simple_fes_form($form = '', $vars = null) {
		return self::simple_fes_create_form($form, $vars);
	}
}
?>