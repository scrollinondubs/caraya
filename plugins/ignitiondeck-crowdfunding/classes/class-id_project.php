<?php
class ID_Project {
	var $id;

	function __construct($id=null) {
		$this->id = $id;
		add_action('save_post_ignition_product', array($this, 'flush_project_cache'));
		add_action('save_post_ignition_product', array($this, 'flush_project_raised'));
		add_action('idf_cache_object', array($this, 'store_project_cache'));
		add_action('id_update_product_defaults', array($this, 'flush_product_defaults'));
		add_action('id_modify_order', array($this, 'flush_project_raised'), 10, 3);
		add_action('id_modify_order', array($this, 'flush_project_orders'), 10, 3);
	}

	function the_project() {
		$res = idf_get_object('id_project-the_project-'.$this->id);
		if (empty($res)) {
			global $wpdb;
			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'ign_products WHERE id = %d', $this->id);
			$res = $wpdb->get_row($sql);
			do_action('idf_cache_object', 'id_project-the_project-'.$this->id, $res, 60 * 60 * 12);
		}
		return $res;
	}

	function update_project($args) {
		global $wpdb;
		$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'ign_products SET product_name = %s, ign_product_title = %s, ign_product_limit = %d, product_details = %s, product_price = %s, goal = %s WHERE id = %d', $args['product_name'], $args['ign_product_title'], $args['ign_product_limit'], $args['product_details'], $args['product_price'], $args['goal'], $this->id);
		$res = $wpdb->query($sql);
		do_action('id_update_project', $this->id, $args);
	}

	function get_project_settings() {
		$res = idf_get_object('id_project-get_project_settings-'.$this->id);
		if (!isset($res)) {
			global $wpdb;
			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'ign_product_settings WHERE product_id = %d', $this->id);
			$res = $wpdb->get_row($sql);
			do_action('idf_cache_object', 'id_project-get_project_settings-'.$this->id, $res, 60 * 60 * 12);
		}
		return $res;
	}

	function currency_code() {
		$project_settings = self::get_project_settings();
		if (empty($project_settings)) {
			$project_settings = self::get_project_defaults();
		}
		if (!empty($project_settings)) {
			$currencyCodeValue = $project_settings->currency_code;
		}
		else {
			$currencyCodeValue = 'USD';
		}
		$cCode = setCurrencyCode($currencyCodeValue);
		return $cCode;
	}

	function get_project_postid() {
		$post_id = idf_get_object('id_project-get_project_postid-'.$this->id);
		if (empty($post_id)) {
			global $wpdb;	
			$sql = $wpdb->prepare('SELECT post_id FROM '.$wpdb->prefix.'postmeta WHERE meta_key = "ign_project_id" AND meta_value = %d ORDER BY meta_id DESC LIMIT 1', $this->id);
			$res = $wpdb->get_row($sql);
			if (!empty($res)) {
				$post_id = $res->post_id;
				do_action('idf_cache_object', 'id_project-get_project_postid-'.$this->id, $post_id, 60 * 60 * 12);
			}
		}
		return $post_id;
	}

	function short_description() {
		$post_id = self::get_project_postid();
		$long_desc = get_post_meta($post_id, 'ign_project_description', true);
		return $long_desc;
	}

	function the_goal() {
		$goal = 0;
		$project = self::the_project();
		if (!empty($project)) {
			$goal = $project->goal;
		}
		return $goal;
	}

	function level_count() {
		$post_id = self::get_project_postid();
		$level_count = get_post_meta($post_id, 'ign_product_level_count', true);
		return $level_count;
	}

	function get_level_price($level_id) {
		$post_id = self::get_project_postid();
		if ($level_id == 1) {
			$price = get_post_meta($post_id, 'ign_product_price', true);
		}
		else if ($level_id > 1) {
			$price = get_post_meta($post_id, 'ign_product_level_'.$level_id.'_price', true);
		}
		return $price;
	}

	function get_project_orders() {
		$order_count = idf_get_object('id_project-get_project_orders-'.$this->id);
		if (!is_numeric($order_count)) {
			$order_count = 0;
			global $wpdb;
			$sql = $wpdb->prepare('SELECT COUNT(*) AS count FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id = %d', $this->id);
			$res = $wpdb->get_row($sql);
			if (!empty($res)) {
				$order_count = $res->count;
			}
			idf_cache_object('id_project-get_project_orders-'.$this->id, $order_count);
		}
		return $order_count;
	}

	function flush_project_orders($order_id, $action = null, $the_order = null) {
		if (empty($the_order)) {
			$order = New ID_Order($order_id);
			$the_order = $order->get_order();
		}
		if (!empty($the_order->product_id)) {
			idf_flush_object('id_project-get_project_orders-'.$the_order->product_id);
		}
	}

	function get_project_raised($noformat = false) {
		$raised = idf_get_object('id_project-get_project_raised-'.$this->id);
		if (!is_numeric($raised)) {
			global $wpdb;
			$sql = 'Select SUM(prod_price) AS raise from '.$wpdb->prefix.'ign_pay_info where product_id = "'.$this->id.'"';
			$res = $wpdb->get_row($sql);
			$raised = (!empty($res->raise) ? $res->raise : '0');
			$raised = apply_filters('id_funds_raised', $raised, $this->get_project_postid());
			idf_cache_object('id_project-get_project_raised-'.$this->id, $raised);
		}
		return ($noformat ? $raised : apply_filters('id_display_currency', apply_filters('id_number_format', $raised), $this->id));
	}

	function flush_project_raised($order_id, $action = null, $the_order = null) {
		if (empty($the_order)) {
			$order = New ID_Order($order_id);
			$the_order = $order->get_order();
		}
		if (!empty($the_order->product_id)) {
			idf_flush_object('id_project-get_project_raised-'.$the_order->product_id);
		}
	}

	function percent() {
		$project = self::the_project();
		$project_goal = self::the_goal();
		if (empty($project_goal)) {
			return 0;
		}
		//$project_orders = self::get_project_orders();
		$project_raised = self::get_project_raised(true);
		if (empty($project_raised)) {
			return 0;
		}
		$raw_percent = $project_raised/$project_goal*100;
		$percent = id_price_format($raw_percent);
		return $percent;
	}

	function successful() {
		$post_id = self::get_project_postid();
		$success = get_post_meta($post_id, 'ign_project_success', true);
		return $success;
	}

	function end_date($post_id = null) {
		// get options and set defaults
		$date_format = get_option('date_format', 'm/d/Y');

		if (empty($post_id)) {
			$post_id = self::get_project_postid();
		}
		$end_date = get_post_meta($post_id, 'ign_fund_end', true);
		$end_date = date($date_format, strtotime($end_date));
		return $end_date;
	}

	function days_left($end_date = null) {

		if (empty($end_date)) {
			$end_date = self::end_date();
		}

		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
		}
		date_default_timezone_set($tz);

		$end_date .= ' 23:59:59';
		$end_date = DateTime::createFromFormat(idf_date_format().' H:i:s', $end_date);
		$days_left = ( date_timestamp_get($end_date) - time() )/60/60/24;
		//echo $days_left;
		if ($days_left < 1) {
			if ($days_left > 0) {
				// convert to hours
				$days_left = floor(number_format($days_left, 2) * 24);
			}
			else {
				$days_left = 0;
			}
		}
		else {
			$days_left = number_format($days_left);
		}
		if (empty($days_left) || $days_left == '' || $days_left < 0) {
			$days_left = 0;
		}

		return $days_left;
	}

	function end_month() {
		$end_date = self::end_date();
		if (!empty($end_date)) {
			$tz = get_option('timezone_string');
			if (empty($tz)) {
				$tz = 'UTC';
			}
			date_default_timezone_set($tz);
			$month = date('F', strtotime($end_date));
		}
		else {
			$month = date('F', time('now'));
		}
		return $month;
	}

	function end_day() {
		$end_date = self::end_date();
		if (!empty($end_date)) {
			$tz = get_option('timezone_string');
			if (empty($tz)) {
				$tz = 'UTC';
			}
			date_default_timezone_set($tz);
			$day = date('d', strtotime($end_date));
		}
		else {
			$day = date('d', time('now'));
		}
		return $day;
	}

	function end_year() {
		$end_date = self::end_date();
		if (!empty($end_date)) {
			$tz = get_option('timezone_string');
			if (empty($tz)) {
				$tz = 'UTC';
			}
			date_default_timezone_set($tz);
			$year = date('Y', strtotime($end_date));
		}
		else {
			$year = date('Y', time('now'));
		}
		return $year;
	}

	function project_closed($post_id = null) {
		if (empty($post_id)) {
			$post_id = self::get_project_postid();
		}
		return get_post_meta($post_id, 'ign_project_closed', true);
	}

	function clear_project_settings() {
		global $wpdb;
		$sql = "DELETE FROM ".$wpdb->prefix."ign_product_settings WHERE product_id = '".$this->id."'";
		$res = $wpdb->query($sql);
	}

	function get_lvl1_name() {
		$ign_product_title = idf_get_object('id_project-get_level1_name-'.$this->id);
		if (empty($ign_product_title)) {
			global $wpdb;
			$sql = $wpdb->prepare('SELECT ign_product_title FROM '.$wpdb->prefix.'ign_products WHERE id = %d', $this->id);
			$res = $wpdb->get_row($sql);
			if (!empty($res)) {
				$ign_product_title = $res->ign_product_title;
				do_action('idf_cache_object', 'id_project-get_level1_name-'.$this->id, $ign_product_title);
			}
		}
		return $ign_product_title;
	}

	function get_fancy_description($level_id) {
		$the_project = $this->the_project();
		$post_id = $this->get_project_postid();
		$project_title = get_the_title($post_id);
		if ($level_id > 1) {
			$post_id = $this->get_project_postid();
			$level_title = get_post_meta($post_id, 'ign_product_level_'.$level_id.'_title', true);
		}
		else if ($level_id == 1) {
			$level_title = $the_project->ign_product_title;
		}
		return $project_title.': '.$level_title;
	}

	function get_level_data($post_id, $no_levels) {
		$this->post_id = $post_id;
		$level_data = array();
		for ($i=2; $i <= $no_levels; $i++) {
			$meta_title = html_entity_decode(get_post_meta( $this->post_id, "ign_product_level_".($i)."_title", true ));
			$meta_limit = get_post_meta( $this->post_id, "ign_product_level_".($i)."_limit", true );
			$meta_order = get_post_meta($this->post_id, 'ign_product_level_'.$i.'_order', true);
			$meta_price = get_post_meta( $this->post_id, "ign_product_level_".($i)."_price", true );
			$meta_desc = html_entity_decode(get_post_meta( $this->post_id, "ign_product_level_".($i)."_desc", true ));
			$meta_short_desc = html_entity_decode(get_post_meta( $this->post_id, "ign_product_level_".($i)."_short_desc", true ));
			$meta_count = getCurrentLevelOrders($this->id, $this->post_id, $i);
			$level_invalid = getLevelLimitReached($this->id, $this->post_id, $i);
			$level_data[$i] = new stdClass;
			$level_data[$i]->id = $i;
			$level_data[$i]->meta_title = apply_filters( 'id_level_'.$i.'_title', $meta_title, $post_id, $i );
			$level_data[$i]->meta_limit = $meta_limit;
			$level_data[$i]->meta_order = $meta_order;
			$level_data[$i]->meta_price = apply_filters( 'id_level_'.$i.'_price', $meta_price, $post_id, $i );
			$level_data[$i]->meta_desc = $meta_desc;
			$level_data[$i]->meta_short_desc = $meta_short_desc;
			$level_data[$i]->meta_count = $meta_count;
			$level_data[$i]->level_invalid = $level_invalid;
		}
		return $level_data;
	}

	function get_end_type() {
		$post_id = $this->get_project_postid();
		return get_post_meta($post_id, 'ign_end_type', true);
	}

	function store_project_cache($transient) {
		if (strpos($transient, 'id_project-') !== false) {
			$project_cache = get_option('id_project_cache');
			if (empty($project_cache)) {
				$project_cache = array($transient);
			}
			else {
				if (!in_array($transient, $project_cache)) {
					array_push($project_cache, $transient);
				}
			}
			update_option('id_project_cache', $project_cache);
		}
	}

	function get_project_cache() {
		return get_option('id_project_cache');
	}

	function flush_project_cache($post_id) {
		$cache_store = $this->get_project_cache();
		$flush_array = array(
			'get_all_projects',
		);
		if (!empty($cache_store)) {
			foreach ($cache_store as $k=>$v) {
				if (in_array($v, $flush_array) || strpos($v, '-'.$this->id) !== FALSE) {
					idf_flush_object($v);
				}
			}
		}
	}

	function flush_product_defaults() {
		idf_flush_object('id_project-get_project_defaults');
	}

	public static function get_project_thumbnail($post_id, $size = 'full') {
		$size = apply_filters('id_project_thumbnail_size', $size);
		$project_id = get_post_meta($post_id, 'ign_project_id', true);
		$thumbs = idf_get_object('id_project-get_project_thumbnail-'.$project_id);
		if (empty($thumbs[$size])) {
			$thumb_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size);
			if (!empty($thumb_src)) {
				if (empty($thumbs)) {
					$thumbs = array(
						$size => $thumb_src,
					);
				}
				else {
					$thumbs[$size] = $thumb_src;
				}
				do_action('idf_cache_object', 'id_project-get_project_thumbnail-'.$project_id, $thumbs);
			}
		}
		if (empty($thumbs[$size])) {
			$url = get_post_meta($post_id, 'ign_product_image1', true);
		}
		else {
			$url = $thumbs[$size][0];
		}
		return $url;
	}

	public static function insert_project($args) {
		global $wpdb;
		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
		}
		date_default_timezone_set($tz);
		$date = date('Y-m-d H:i:s');
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'ign_products (
			product_name,
			ign_product_title,
			ign_product_limit,
			product_details,
			product_price,
			goal,
			created_at) VALUES (%s, %s, %d, %s, %s, %s, %s)',
		$args['product_name'],
		$args['ign_product_title'],
		$args['ign_product_limit'],
		$args['product_details'],
		$args['product_price'],
		$args['goal'],
		$date);
		$insert_id = null;
		try {
			$res = $wpdb->query($sql);
			$insert_id = $wpdb->insert_id;
		}
		catch(error $e) {
			// some error
		}
		return $insert_id;
	}

	public static function get_all_projects() {
		$res = idf_get_object('id_project-get_all_projects');
		if (empty($res)) {
			global $wpdb;
			$sql = 'SELECT * FROM '.$wpdb->prefix.'ign_products';
			$res = $wpdb->get_results($sql);
			do_action('idf_cache_object', 'id_project-get_all_projects', $res);
		}
		return $res;
	}

	public static function get_project_posts() {
		$args = array('post_type' => 'ignition_product', 'posts_per_page' => -1);
		$posts = get_posts($args);
		return $posts;
	}

	public static function get_project_defaults() {
		$settings = idf_get_object('id_project-get_project_defaults');
		if (empty($settings)) {
			global $wpdb;
			$sql = 'SELECT * FROM '.$wpdb->prefix.'ign_prod_default_settings WHERE id = 1';
			$settings = $wpdb->get_row($sql);
			do_action('idf_cache_object', 'id_project-get_project_defaults', $settings, 60 * 60 * 12);
		}
		return $settings;
	}

	public static function get_id_settings() {
		global $wpdb;
		$sql = 'SELECT * FROM '.$wpdb->prefix.'ign_settings WHERE id = 1';
		$res = $wpdb->get_row($sql);
		return $res;
	}

	public static function get_days_left($project_end) {
		return self::days_left($days_left);
	}

	public static function set_raised_meta($project_id = null) {
		if (empty($project_id)) {
			$projects = self::get_all_projects();
			foreach ($projects as $a_project) {
				$project = new ID_Project($a_project->id);
				$post_id = $project->get_project_postid();
				$raised = floatval($project->get_project_raised(true));
				update_post_meta($post_id, 'ign_fund_raised', $raised);
			}
		}
		else {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			$raised = floatval($project->get_project_raised(true));
			update_post_meta($post_id, 'ign_fund_raised', $raised);
		}
	}

	public static function set_percent_meta($project_id = null) {
		if (empty($project_id)) {
			$projects = self::get_all_projects();
			foreach ($projects as $a_project) {
				$project = new ID_Project($a_project->id);
				$post_id = $project->get_project_postid();
				$percent = floatval($project->percent());
				if ($percent >= 100) {
					$successful = get_post_meta($post_id, 'ign_project_success', true);
					if (empty($successful) || !$successful) {
						do_action('idcf_project_success', $post_id, $a_project->id);
					}
					if (apply_filters('idcf_do_project_success', true, $post_id, $a_project->id, $successful)) {
						update_post_meta($post_id, 'ign_project_success', 1);
					}
				}
				else {
					do_action('idcf_pre_remove_success', $post_id);
					do_action('idcf_remove_success', $post_id);
				}
				update_post_meta($post_id, 'ign_percent_raised', $percent);
			}
		}
		else {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			$percent = floatval($project->percent());
			if ($percent >= 100) {
				$successful = get_post_meta($post_id, 'ign_project_success', true);
				if (empty($successful) || !$successful) {
					do_action('idcf_project_success', $post_id, $project_id);
				}
				if (apply_filters('idcf_do_project_success', true, $post_id, $project_id, $successful)) {
					update_post_meta($post_id, 'ign_project_success', 1);
				}
			}
			else {
				do_action('idcf_pre_remove_success', $post_id);
				do_action('idcf_remove_success', $post_id);
			}
			update_post_meta($post_id, 'ign_percent_raised', $percent);
		}
	}

	public static function set_days_meta($project_id = null) {
		if (empty($project_id)) {
			$projects = self::get_all_projects();
			foreach ($projects as $a_project) {
				$project = new ID_Project($a_project->id);
				$post_id = $project->get_project_postid();
				$days_left = $project->days_left();
				update_post_meta($post_id, 'ign_days_left', $days_left);
			}
		}
		else {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			$days_left = $project->days_left();
			update_post_meta($post_id, 'ign_days_left', $days_left);
		}
	}

	public static function set_closed_meta($project_id = null) {
		if (empty($project_id)) {
			$projects = self::get_all_projects();
			foreach ($projects as $a_project) {
				$project = new ID_Project($a_project->id);
				$post_id = $project->get_project_postid();
				$days_left = $project->days_left();
				$end_type = $project->get_end_type();
				if ($days_left <= 0 && $end_type == 'closed') {
					update_post_meta($post_id, 'ign_project_closed', true);
					do_action('idcf_project_closed', $post_id, $a_project->id);
				} else {
					delete_post_meta($post_id, 'ign_project_closed', false);
				}
			}
		}
		else {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			$days_left = $project->days_left();
			$end_type = $project->get_end_type();
			if ($days_left <= 0 && $end_type == 'closed') {
				update_post_meta($post_id, 'ign_project_closed', true);
				do_action('idcf_project_closed', $post_id, $project_id);
			} else {
				delete_post_meta($post_id, 'ign_project_closed');
			}
		}
	}

	public static function set_failed_meta($project_id = null) {
		if (empty($project_id)) {
			$projects = self::get_all_projects();
			foreach ($projects as $a_project) {
				$project = new ID_Project($a_project->id);
				$post_id = $project->get_project_postid();
				$successful = get_post_meta($post_id, 'ign_project_success', true);
				$closed = get_post_meta($post_id, 'ign_project_closed', true);
				if ($closed && empty($successful)) {
					update_post_meta($post_id, 'ign_project_failed', 1);
					do_action('idcf_project_failed', $post_id, $a_project->id);
				} else {
					delete_post_meta($post_id, 'idcf_project_failed', false);
				}
			}
		}
		else {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			$successful = get_post_meta($post_id, 'ign_project_success', true);
			$closed = get_post_meta($post_id, 'ign_project_closed', true);
			if ($closed && empty($successful)) {
				update_post_meta($post_id, 'ign_project_failed', 1);
				do_action('idcf_project_failed', $post_id, $a_project->id);
			} else {
				delete_post_meta($post_id, 'idcf_project_failed', false);
			}
		}
	}

	public static function level_sort($a, $b) {
		return $a->meta_order == $b->meta_order ? 0 : ($a->meta_order > $b->meta_order) ? 1 : -1;
	}

	public static function get_project_images($post_id, $project_id) {
		$project_image1 = self::get_project_thumbnail($post_id);
		$project_image2 = get_post_meta($post_id, 'ign_product_image2', true);
		$project_image3 = get_post_meta($post_id, 'ign_product_image3', true);
		$project_image4 = get_post_meta($post_id, 'ign_product_image4', true);
		$images = array($project_image1, $project_image2, $project_image3, $project_image4);
		return $images;
	}

	public static function delete_project_posts() {
		global $wpdb;
		$post_query = 'SELECT * FROM '.$wpdb->prefix.'posts WHERE post_type = "ignition_product"';
		$post_res = $wpdb->get_results($post_query);
		if (!empty($post_res)) {
			foreach ($post_res as $res) {
				wp_delete_post($res->ID, true);
			}
		}
	}

	public static function count_user_projects($user_id) {
		$count = idf_get_object('id_project-count_user_projects-'.$user_id);
		if (!is_numeric($count)) {
			$args = array(
				'author' => $user_id,
				'post_type' => 'ignition_product',
				'post_status' => array('draft','pending','publish')
			);
			$posts = get_posts($args);
			$count = count($posts);
			idf_cache_object('idf_project-count_user_projects-'.$user_id, $count);
		}
		return $count;
	}

	function get_project_raised_by_dates($start_date, $end_date) {
		global $wpdb;
		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
		}
		date_default_timezone_set($tz);
		$sql = $wpdb->prepare('SELECT SUM(prod_price) AS raise FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id = %d
				AND (created_at >= %s AND created_at <= %s) ',
				$this->id, $start_date." 00:00:00", $end_date." 23:59:59");
		$res = $wpdb->get_row($sql);
		if (!empty($res->raise)) {
			return str_replace(',', '', $res->raise);
		}
		else {
			return '0';
		}
	}

	function get_project_orders_by_dates($start_date, $end_date) {
		global $wpdb;
		$tz = get_option('timezone_string');
		if (empty($tz)) {
			$tz = 'UTC';
		}
		date_default_timezone_set($tz);
		$sql = $wpdb->prepare('SELECT COUNT(*) AS count FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id = %d' .
				' AND (created_at >= %s AND created_at <= %s)',
				$this->id, $start_date." 00:00:00", $end_date." 23:59:59");
		$res = $wpdb->get_row($sql);
		if (!empty($res)) {
			return $res->count;
		}
		else {
			return 0;
		}
	}

	function get_project_meta() {
		$post_id = $this->get_project_postid();
		return get_post_meta($post_id);
	}
}

$project = new ID_Project();
?>