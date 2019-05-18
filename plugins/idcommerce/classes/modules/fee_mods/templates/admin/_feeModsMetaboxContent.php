<?php
$fields = array(
	array(
		'before' => '<p>'.__('Optionally enter acustom application fee % or cents value for this project (leave empty for default)', 'ignitiondeck').'.</p>',
		'label' => __('Application fee', 'ignitiondeck'),
		'value' => (isset($application_fee) ? $application_fee : ''),
		'name' => 'application_fee',
		'type' => 'number'
	)
);
$form = new ID_Form($fields);
echo apply_filters('idc_fee_mods_form', $form->build_form());
?>