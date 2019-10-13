<?php
class IDC_Modules extends ID_Modules {

	function set_moddir() {
		$this->moddir = dirname(__FILE__) . '/' . 'modules/';
		$this->custom_moddir = dirname(__FILE__) . '/' . 'custom-modules/';
	}

	function show_modules($modules) {
		// show modules in the IDF modules menu
		global $idf_current_version;
		$idc_modules = $this->get_modules();
		foreach ($idc_modules as $module) {
			$thisfile = (is_dir($this->moddir . $module) ? $this->moddir . $module : $this->custom_moddir . $module);
			$basename = basename(is_dir($this->moddir . $module) ? $this->moddir : $this->custom_moddir);
			if (is_dir($thisfile) && !in_array($module, $this->exdir)) {
				if (!file_exists($thisfile . '/' . 'module_info.json')) {
					continue;
				}
				$info = json_decode(file_get_contents($thisfile . '/' . 'module_info.json'), true);
				$new_module = (object) array(
					'title' => $info['title'],
					'short_desc' => $info['short_desc'],
					'link' => apply_filters('id_module_link', menu_page_url('idf-extensions', false) . '&id_module='.$module),
					'doclink' => $info['doclink'],
					'thumbnail' => plugins_url($basename . '/' . $module . '/thumbnail.png', __FILE__),
					'basename' => $module,
					'type' => $info['type'],
					'requires' => $info['requires'],
					'priority' => (isset($info['priority']) ? $info['priority'] : ''),
					'category' => (isset($info['category']) ? $info['category'] : ''),
					'tags' => (isset($info['tags']) ? $info['tags'] : ''),
					'status' => (isset($info['status']) ? $info['status'] : '')
				);
				if ($info['status'] == 'test') {
					// allow devs to activate
					if (defined('ID_DEV_MODE') && 'ID_DEV_MODE' == true) {
						$info['status'] = 'live';
						$new_module->short_desc .= ' '.__('(DEV_MODE)', 'memberdeck');
					}
				}
				if ($info['status'] == 'live') {
					if ($idf_current_version > '1.2.36') {
						$modules[$module] = $new_module;
					}
					else {
						switch ($new_module->requires) {
							case 'idc':
								if (is_idc_licensed()) {
									$modules[$module] = $new_module;
								}
								break;
							case 'ide':
								$pro = get_option('is_id_pro', false);
								if ($pro) {
									$modules[$module] = $new_module;
								}
								break;
							default:
								$modules[$module] = $new_module;
								break;
						}
					}
				}
			}
		}
		return $modules;
	}

	function get_modules() {
		$modules = array();
		$subfiles = scandir($this->moddir);
		foreach ($subfiles as $file) {
			$thisfile = $this->moddir . $file;
			if (is_dir($thisfile) && !in_array($file, $this->exdir) && substr($file, 0, 1) !== '.') {
				$modules[] = $file;
			}
		}
		return apply_filters('id_modules', $modules);
	}

	public function load_module($module) {
		// Loading the class file of the module
		if (file_exists($this->moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION)) {
			require_once $this->moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION;
		}
		else if (file_exists($this->custom_moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION)) {
			require_once $this->custom_moddir . $module . '/' . 'class-' . $module . self::$PHP_EXTENSION;
		}
	}
}
new IDC_Modules();