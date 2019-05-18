<?php

function fee_mods_metabox() {
	$metabox = new IDC_Fee_Mods_Metaboxes();
}

function fee_mods_fee($app_fee, $level_data) {
	if (!empty($level_data->id)) {
		$assignments = get_assignments_by_level($level_data->id);
		if (!empty($assignments)) {
			$project_id = $assignments[0]->project_id;
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			if ($post_id > 0) {
				$custom_fee = get_post_meta($post_id, 'application_fee', true);
				if (!empty($custom_fee)) {
					$app_fee = $custom_fee;
				}
			}
		}
	}
	return $app_fee;
}

?>