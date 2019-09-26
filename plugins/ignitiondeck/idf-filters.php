<?php
//add_filter('id_modules', 'idf_modules_require');

function idf_modules_require($modules) {
	if (!idf_registered()) {
		return null;
	}
	return $modules;
}
?>