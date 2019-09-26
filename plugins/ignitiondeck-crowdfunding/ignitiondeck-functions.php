<?php
function is_id_project() {
	global $post;
	if (isset($post)) {
		$post_content = $post->post_content;
		if ($post->post_type == 'ignition_product') {
			$post_id = $post->ID;
			$project_id = get_post_meta($post_id, 'ign_project_id', true);
		}
		else if (strpos($post_content, 'project_')) {
			$pos = strpos($post_content, 'product=');
			$project_id = absint(substr($post_content, $pos + 9, 1));
		}
	}
	return (isset($project_id) ? $project_id : null);
}

/*
/*  get currency symbol *
*/
function setCurrencyCode($cvalue){ 
	switch($cvalue){		
		case 'USD':
			$currencyCode = '$';
			break;
		case 'AUD':
			$currencyCode = '$';
			break;
		case 'CAD':
			$currencyCode = '$';
			break;
		case 'CZK':
			$currencyCode = 'Kč';
			break;
		case 'DKK':
			$currencyCode = 'Kr';
			break;
		case 'EUR':
			$currencyCode = '&euro;';
			break;
		case 'HKD':
			$currencyCode = '$';
			break;
		case 'HUF':
			$currencyCode = 'Ft';
			break;
		case 'ILS':
			$currencyCode = '₪';
			break;
		case 'JPY':
			$currencyCode = '&yen;';
			break;
		case 'MXN':
			$currencyCode = '$';
			break;
		case 'MYR':
			$currencyCode = 'RM';
			break;
		case 'NOK':
			$currencyCode = 'kr';
			break;
		case 'NZD':
			$currencyCode = '$';
			break;
		case 'PHP':
			$currencyCode = '₱';
			break;
		case 'PLN':
			$currencyCode = 'zł';
			break;
		case 'GBP':
			$currencyCode = '&pound;';
			break;
		case 'SGD':
			$currencyCode = '$';
			break;
		case 'SEK':
			$currencyCode = 'kr';
			break;
		case 'CHF':
			$currencyCode = 'CHF';
			break;
		case 'TWD':
			$currencyCode = 'NT$';
			break;
		case 'THB':
			$currencyCode = '&#3647;';
			break;
		case 'TRY':
			$currencyCode = '&#8356;';
			break;
		case 'BRL':
			$currencyCode = 'R$';
			break;
		default :
			$currencyCode = '$';
	}
	return $currencyCode;
}

/**
 * SetOrderStatus
 * @param string $status
 * @param int $paymentId
 */
function setOrderStatus($status, $pay_info_id){
    global $wpdb;
    $sql_update="update ".$wpdb->prefix . "ign_pay_info set status='$status' where id='".$pay_info_id."'";
    $res = $wpdb->query( $sql_update );
    do_action('id_modify_order', $pay_info_id, 'update');
}

/**
 * getProductByOrderId
 * Get product by given order id
 * @global object $wpdb
 * @param <type> $id
 * @return <type> 
 */
function getProductInfoByOrderId($id){
    global $wpdb;
    $query="SELECT `product_id` FROM ".$wpdb->prefix . "ign_pay_info where id=$id";
    $productId = $wpdb->get_row( $query );

    $productId = $productId->product_id;

    $sql="SELECT * FROM ".$wpdb->prefix . "ign_products WHERE id='". $productId ."' limit 0,1";
    $res = $wpdb->query( $sql );
    $items = $wpdb->get_results($sql);
    $product = $items[0];
    return $product;
}

/**
 * GetOrderById
 * @global object $wpdb
 * @param <type> $id
 * @return <type>
 */
function getOrderById($id){
    global $wpdb;
    $query = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . "ign_pay_info where id = %d", $id);
    $order = $wpdb->get_row( $query );
    return $order;
}

/*
 *	getPostbyProductID()
 *	Desc: Return the post_id of the given $prod_id (product id)
 */
function getPostbyProductID($project_id) {
	$project = new ID_Project($project_id);
	$post_id = $project->get_project_postid();	
	return $post_id;
}

function get_embed_image($project_id) {
	global $wpdb;
	$project = new ID_Project($project_id);
	$post_id = $project->get_project_postid();	
	$url = ID_Project::get_project_thumbnail($post_id, 'id_embed_image');
	return $url;	
}

function idc_checkout_image($post_id) {
	global $wpdb;
	$image = ID_Project::get_project_thumbnail($post_id, 'id_checkout_image');
	if (empty($image)) {
		// $image = idcf_project_placeholder_image('checkout');
	}
	return $image;
}

function getPostDetailbyProductID($project_id) {
	$project = new ID_Project($project_id);
	$post_id = $project->get_project_postid();	
	$post = get_post($post_id);
	return $post;
}

function getTotalProductFund($project_id) {
	global $wpdb;
	$sql = "Select SUM(prod_price) AS raise from ".$wpdb->prefix . "ign_pay_info where product_id='".$project_id."'";
	$result = $wpdb->get_row($sql);
	if ($result->raise != NULL || $result->raise != 0)
		return $result->raise;
	else
		return 0;
}

function getProjectGoal($project_id) {
	global $wpdb;

	$goal_query = $wpdb->prepare('SELECT goal FROM '.$wpdb->prefix.'ign_products WHERE id=%d', $project_id);
	$goal_return = $wpdb->get_row($goal_query);
	return $goal_return->goal;
}

function getPercentRaised($project_id) {
	global $wpdb;
	$total = getTotalProductFund($project_id);
	$goal = getProjectGoal($project_id);
	$percent = 0;
	if ($total > 0) {
		$percent = number_format($total/$goal*100, 2, '.', '');
	}
	return $percent;
}

/*
 *	getLevelLimitReached()
 *	Desc: To calculate the goal reached so for for a certain level
 */
function getLevelLimitReached($project_id, $post_id, $level) {
	$count = ID_Order::project_level_order_count($project_id, $level);
	if ($level == 1) {
		$product_details = getProductDetails($project_id);
		if (empty($product_details)) {
			return false;
		}
		$limit = $product_details->ign_product_limit;
	}
	else {
		// getting the level limit set, from the wp_postmeta
		$limit = get_post_meta( $post_id, "ign_product_level_".$level."_limit", true );
	}
	// Setting the level limit to non-formatted number
	$limit = floatval(preg_replace('/[^\d.]/', '', $limit));
	
	if (empty($limit)) {
		return false;
	}
	
	if ($count < $limit) {
		return false;
	}
	return true;
}

function getCurrentLevelTotal($product_id, $post_id, $level) {
	global $wpdb;
	$sql = "SELECT SUM(prod_price) AS LevelPurchaseTotal FROM ".$wpdb->prefix."ign_pay_info WHERE product_id = '".$product_id."' AND product_level = '".$level."'";
	//echo $sql."<br />";
	$level_purchase_so_far = $wpdb->get_row($sql)->LevelPurchaseTotal;
	
	if ($level_purchase_so_far == "") {
		//If there are no purchases $level_purchase_so_far will be empty, so putting condition
		$level_purchase_so_far = 0;
	}	
	return $level_purchase_so_far;
}

/*
 *	getUsersOrders()
 *	Desc: To get the number of orders placed for the $level
 */
function getCurrentLevelOrders($product_id, $post_id, $level) {
	// #devnote surely we can cache or use a cached object here
	$orders = ID_Order::get_orders_by_project($product_id);
	if (empty($orders)) {
		return 0;
	}
	$level_orders = 0;
	foreach ($orders as $order) {
		if ($order->product_level == $level) {
			$level_orders++;
		}
	}
	//echo $level_orders;
	/*
	global $wpdb;
	$sql = "SELECT COUNT(*) AS TotalOrders FROM ".$wpdb->prefix."ign_pay_info WHERE product_id = '".$product_id."' AND product_level = '".$level."'";
	//echo $sql."<br />";
	$level_purchase_so_far = $wpdb->get_row($sql)->TotalOrders;
	
	if ($level_purchase_so_far == "") {
		//If there are no purchases $level_purchase_so_far will be empty, so putting condition
		$level_purchase_so_far = 0;
	}*/
	return $level_orders;
}

function getProductDetails($project_id) {
	$project = new ID_Project($project_id);
	return $project->the_project();
}

/*
 *	Desc: Based on the latest structure of the Project URL stored, we are using this funtion to get the Project URL
 *	Function: getProjectURLfromType()
 */
function getProjectURLfromType($project_id, $page="") {
	global $wpdb;
	$slug = apply_filters('idcf_archive_slug', __('projects', 'ignitiondeck'));
	$project_id = urlencode($project_id);
	if ($project_id > 0) {
		$project = new ID_Project($project_id);
		$post_id = $project->get_project_postid();
		if ($post_id > 0) {
			$product_url = get_permalink($post_id);
		}
		$page = urlencode($page);
		$post = getPostDetailbyProductID($project_id);
		if (!empty($post)) {
			$meta_url = get_post_meta($post->ID, 'id_project_URL', true);
			if (get_option('permalink_structure') == "") {
				if (get_post_meta($post->ID, 'ign_option_project_url', true) == "current_page") {		// If Project URL is the normal Project Page
					if ($page == "")
						$product_url = home_url()."/?ignition_product=".$post->post_name;
					else if ($page == "purchaseform")
						$product_url = home_url()."/?ignition_product=".$post->post_name."&purchaseform=1&prodid=".$project_id;
					else if ($page == "preapprovalkey")
						$product_url = home_url()."/?ignition_product=".$post->post_name."&generatepreapproval=1";
					else
						$product_url = home_url()."/?ignition_product=".$post->post_name."&paypa_passed=yes";
						
				}
				else if (get_post_meta($post->ID, 'ign_option_project_url', true) == "page_or_post") {		// If Project URL is another post or Project page
					$post_name = html_entity_decode(get_post_meta($post->ID, 'ign_post_name', true));
					$sql_project_post = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."posts WHERE post_name = %s AND post_type != 'ignition_product' LIMIT 1", $post_name);
					$project_post = $wpdb->get_row($sql_project_post);
					if (!empty($project_post)) {
						if ($page == "")
							$product_url = $project_post->guid;
						else if ($page == "purchaseform")
							$product_url = $project_post->guid."&purchaseform=1&prodid=".$project_id;
						else if ($page == "preapprovalkey")
							$product_url = $project_post->guid."&generatepreapproval=1";
						else
							$product_url = $project_post->guid."&paypa_passed=yes";
					}
				}
				else if (get_post_meta($post->ID, 'ign_option_project_url', true) == "external_url" && !empty($meta_url)) {		//If some external URL is set as Project page
					if ($page == "")
						$product_url = $meta_url;
					else if ($page == "purchaseform")
						$product_url = $meta_url."&purchaseform=1&prodid=".$project_id;
					else if ($page == "preapprovalkey")
						$product_url = $meta_url."&generatepreapproval=1";
					else
						$product_url = $meta_url."&paypa_passed=yes";
				}
			} 
			else {
				if (get_post_meta($post->ID, 'ign_option_project_url', true) == "current_page") {		// If Project URL is the normal Project Page
					if ($page == "")
						$product_url = home_url()."/".$slug."/".$post->post_name;
					else if ($page == "purchaseform")
						$product_url = home_url()."/".$slug."/".$post->post_name."/?purchaseform=1&prodid=".$project_id;
					else if ($page == "preapprovalkey")
						$product_url = home_url()."/".$slug."/".$post->post_name."/?generatepreapproval=1";
					else
						$product_url = home_url()."/".$slug."/".$post->post_name."/?paypa_passed=yes";
						
				}
				else if (get_post_meta($post->ID, 'ign_option_project_url', true) == "page_or_post") {		// If Project URL is another post or Project page
					$post_name = html_entity_decode(get_post_meta($post->ID, 'ign_post_name', true));
					$sql_project_post = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."posts WHERE post_name = %s AND post_type != 'ignition_product' LIMIT 1", $post_name);
					$project_post = $wpdb->get_row($sql_project_post);
					if (!empty($project_post)) {
						if ($page == "")
							//$product_url = $project_post->guid;
							$product_url = get_permalink($project_post->ID);
						else if ($page == "purchaseform")
							$product_url = get_permalink($project_post->ID)."?purchaseform=1&prodid=".$project_id;
						else if ($page == "preapprovalkey")
							$product_url = get_permalink($project_post->ID)."?generatepreapproval=1";
						else
							$product_url = get_permalink($project_post->ID)."?paypa_passed=yes";
					}
				}
				else if (get_post_meta($post->ID, 'ign_option_project_url', true) == "external_url" && !empty($meta_url)) {		//If some external URL is set as Project page
					if ($page == "")
						$product_url = $meta_url;
					else if ($page == "purchaseform")
						$product_url = $meta_url."?purchaseform=1&prodid=".$project_id;
					else if ($page == "preapprovalkey")
						$product_url = $meta_url."?generatepreapproval=1";
					else
						$product_url = $meta_url."?paypa_passed=yes";
				}
			}
		}
	}
	return $product_url;
}

function getPurchaseURLfromType($project_id, $page="") {
	$slug = apply_filters('idcf_archive_slug', __('projects', 'ignitiondeck'));
	global $wpdb;
	// Set default purchase url in the event we don't have one set
	$purchase_default = get_option('id_purchase_default');
	if (!empty($purchase_default)) {
		if (!empty($purchase_default['option'])) {
			$option = $purchase_default['option'];
			if ($option == 'page_or_post') {
				if (!empty($purchase_default['value'])) {
					$purchase_url = get_permalink($purchase_default['value']);
				}
			}
			else {
				if (isset($purchase_default['value'])) {
					$purchase_url = $purchase_default['value'];
				}
			}
		}
	}
	$project_id = absint($project_id);
	$page = urlencode($page);
	if ($project_id > 0) {
		$post = getPostDetailbyProductID($project_id);
		if (isset($post->ID)) {
			$post_page = get_post_meta($post->ID, 'ign_option_purchase_url', true);
			if (!empty($post_page)) {
				$permalink_structure = get_option('permalink_structure');
				if ($post_page !== 'default') {
					$meta_url = get_post_meta($post->ID, 'purchase_project_URL', true);
					if ($permalink_structure == "") {
						// we no longer set defaults here since they are set above
						if ($post_page == "current_page") {		// If Project URL is the normal Project Page
							if ($page == "purchaseform") {
								$purchase_url = home_url()."/?ignition_product=".$post->post_name."&purchaseform=1&prodid=".$project_id;
							}
						} 
						else if ($post_page == "page_or_post") {		// If Project URL is another post or Project page
							$post_name = html_entity_decode(get_post_meta($post->ID, 'ign_purchase_post_name', true));
							$sql_purchase_post = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."posts WHERE post_name = %s AND post_type != 'ignition_product' LIMIT 1", $post_name);
							$purchase_post = $wpdb->get_row($sql_purchase_post);
							if (!empty($purchase_post)) {
								if ($page == "purchaseform") {
									$purchase_url = $purchase_post->guid."&purchaseform=1&prodid=".$project_id;
								}
							}	
						} 
						else if ($post_page == "external_url") {		//If some external URL is set as Project page
							if ($page == "purchaseform" && !empty($meta_url)) {
								$purchase_url = $meta_url."&purchaseform=1&prodid=".$project_id;
							}	
						}
					} 
					else {
						if ($post_page == "current_page") {		// If Project URL is the normal Project Page
							if ($page == "purchaseform") {
								$purchase_url = home_url()."/".$slug."/".$post->post_name."/?purchaseform=1&prodid=".$project_id;
							}	
						} 
						else if ($post_page == "page_or_post") {		// If Project URL is another post or Project page

							$post_name = html_entity_decode(get_post_meta($post->ID, 'ign_purchase_post_name', true));

							$sql_purchase_post = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."posts WHERE post_name = %s AND post_type != 'ignition_product' LIMIT 1", $post_name);
							$purchase_post = $wpdb->get_row($sql_purchase_post);
							if (!empty($purchase_post)) {
								if ($page == "purchaseform") {
									$purchase_url = get_permalink($purchase_post->ID)."?purchaseform=1&prodid=".$project_id;
								}
							}
						} 
						else if ($post_page == "external_url") {		//If some external URL is set as Project page
							if ($page == "purchaseform" && !empty($meta_url)) {
								$purchase_url = $meta_url."?purchaseform=1&prodid=".$project_id;
							}	
						}
					}
				}
				else {
					if (empty($purchase_url)) {
						$purchase_url = get_permalink($post->ID);
					}
					if ($permalink_structure == "") {
						$purchase_url = $purchase_url.'&purchaseform=1&prodid='.$project_id;
					}
					else {
						$purchase_url = $purchase_url.'?purchaseform=1&prodid='.$project_id;
					}
				}
			}
		}
	}
	return $purchase_url;
}

function getThemeFileName() {
	$name = 'ignitiondeck-style';
	$settings = ID_Project::get_id_settings();
	if (!empty($settings->theme_value) && $settings->theme_value !== 'style1') {
		$name = 'ignitiondeck-'.$settings->theme_value;
	}
	return $name;
}

function getSettings() {
	$settings = get_transient('idcf-getSettings');
	if (empty($settings)) {
		global $wpdb;
		$sql_settings = "SELECT * FROM ".$wpdb->prefix."ign_settings WHERE id = '1'";
		$settings = $wpdb->get_row( $sql_settings );
		do_action('idf_cache_object', 'idcf-getSettings', $settings, 60 * 60 * 12);
	}
	return $settings;
}

function getProductSettings($product_id) {
	global $wpdb;
	$sql_settings = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."ign_product_settings WHERE product_id = %d", $product_id);
	$settings = $wpdb->get_row( $sql_settings );
	
	return $settings;
}

function getProductbyPostID($post_id) {
	$project_id = get_post_meta($post_id, 'ign_project_id', true);
	$project = new ID_Project($project_id);
	$the_project = $project->the_project();
	return $the_project;
}

function getProductNumberFromPostID($postid) {
	global $wpdb;
	//$product_details = getProductbyPostID($postid);

	$product_number = get_post_meta($postid, 'ign_project_id', true);
	return $product_number;
}

// This returns itself
function getProductNumberFromProductID($product_id) {
	global $wpdb;
	
	$sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."ign_products WHERE id = %d", $product_id);
	$result = $wpdb->get_row($sql);
	
	$product_number = $result->id;
	return $product_number;
}

function postToURL($url, $data) {
	$fields = '';
	foreach($data as $key => $value) { 
	  	$fields .= $key . '=' . $value . '&'; 
	}
	rtrim($fields, '&');
	
	$post = curl_init();
	
	curl_setopt($post, CURLOPT_URL, $url);
	curl_setopt($post, CURLOPT_POST, count($data));
	curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
	
	$result = curl_exec($post);
	
	curl_close($post);
}

function getProductDefaultSettings() {
	return ID_Project::get_project_defaults();
}

function action_id_payment_success($pay_info_id) {
	// This function handles all that happens after a successful order
	// 1. Lets set percent meta in case we need to fire the project success hook
	if (empty($pay_info_id)) {
		return;
	}
	$new_order = new ID_Order($pay_info_id);
	$order = $new_order->get_order();
	if (empty($order)) {
		return;
	}
	$project_id = $order->product_id;
	$percent = ID_Project::set_percent_meta($project_id);
}

if (is_id_licensed()) {
	add_action('id_payment_success', 'action_id_payment_success', 5, 1);
}

/*
 *   Function to print all the short codes
 */
function getAllShortCodes() {
	echo '<div class="id-metabox-short-codes">
			<div class="shortcode-content">';
			$content = 
				'<div><strong>'.__('Project Content Template', 'ignitiondeck').':</strong><br>&#91;project_page_content product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Full Project Template', 'ignitiondeck').':</strong><br>&#91;project_page_complete product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Templates Separately', 'ignitiondeck').':</strong><br>&#91;project_page_content_left product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&#91;project_page_widget product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&#91;project_mini_widget product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Project Grid', 'ignitiondeck').':</strong><br>&#91;project_grid "&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project Name', 'ignitiondeck').':</strong><br>&#91;project_name product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project Thumbnail', 'ignitiondeck').':</strong><br>&#91;project_image product="<span data-product=&quot;&quot;></span>" image="1"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project Long Description', 'ignitiondeck').':</strong><br>&#91;project_long_desc product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project Video', 'ignitiondeck').':</strong><br>&#91;project_video product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Percentage Bar', 'ignitiondeck').':</strong><br>&#91;project_percentage_bar product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Short Description', 'ignitiondeck').':</strong><br>&#91;project_short_desc product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Goal Amount', 'ignitiondeck').':</strong><br>&#91;project_goal product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Number of Pledges', 'ignitiondeck').':</strong><br>&#91;project_users product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Fundraising Total', 'ignitiondeck').':</strong><br>&#91;project_pledged product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Days Left', 'ignitiondeck').':</strong><br>&#91;project_daystogo product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display End Date', 'ignitiondeck').':</strong><br>&#91;project_end product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project FAQ', 'ignitiondeck').':</strong><br>&#91;project_faq product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>'.__('Display Project Updates', 'ignitiondeck').':</strong><br>&#91;project_updates product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>';
				echo apply_filters('id_shortcode_list', $content);
		echo '</div>
		  </div>';
}

function getShortCodesPostPage() {
	global $wpdb;
	
	$products = ID_Project::get_all_projects();
	echo '<div class="id-metabox-short-codes">
			<div>
				<div>Select Project: <select name="project_id_shortcodes" id="project_id_shortcodes">
					 <option> --- </option>';
	foreach ($products as $product) {
		$project = new ID_Project($product->id);
		$post_id = $project->get_project_postid();
		echo '<option value="'.$product->id.'">'.get_the_title($post_id).'</option>';
	}
	echo '</select>
				</div>
			</div>
			<div class="shortcode-content">';
			$content = 
				'<div><strong>For Full Width Project Template:</strong><br>&#91;project_page_content product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>For Combination Project Template &amp; Project Widget:</strong><br>&#91;project_page_complete product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>To Use Project Template &amp; Widget Separately:</strong><br>&#91;project_page_content_left product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&#91;project_page_widget product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&#91;project_mini_widget product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>Project Grid:</strong><br>&#91;project_grid product="<span data-product=&quot;&quot;></span>"]</div>
				<div>&nbsp;</div>
				<div><strong>For Pledge Form:</strong><br>&#91;project_purchase_form product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<!--<div><strong>For Thank You Page:</strong><br>&#91;project_thank_you product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>-->
				<div><strong>For Project Name:</strong><br>&#91;project_name product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Project Image:</strong><br>&#91;project_image product="<span data-product=&quot;&quot;></span>" image="&#60;image number&#62;"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Project Long Description:</strong><br>&#91;project_long_desc product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Project Video:</strong><br>&#91;project_video product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Project Percentage Bar:</strong><br>&#91;project_percentage_bar product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Short Description:</strong><br>&#91;project_short_desc product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Goal Amount of the Project:</strong><br>&#91;project_goal product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<!--<div><strong>For Project Levels:</strong><br>&#91;project_price_levels product="<span data-product=&quot;&quot;></span>"&#93;</div>-->
				<div>&nbsp;</div>
				<div><strong>For Amount of Project Users:</strong><br>&#91;project_users product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Amount of Project Supporters:</strong><br>&#91;project_pledged product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Project Days Left:</strong><br>&#91;project_daystogo product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Project End:</strong><br>&#91;project_end product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Project FAQ:</strong><br>&#91;project_faq product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>
				<div><strong>For Displaying Project Updates:</strong><br>&#91;project_updates product="<span data-product=&quot;&quot;></span>"&#93;</div>
				<div>&nbsp;</div>';
				echo apply_filters('id_shortcode_list', $content);
		echo '</div>
		  </div>';
}

/**
 * Get the placeholder image of the size required
 */
function idcf_project_placeholder_image($size = null) {
	$image = '';
	if (empty($size)) {
		$image = plugins_url('images/project-image-placeholder.jpg', __FILE__);
	} else {
		switch ($size) {
			case 'thumb':
				$image = plugins_url('images/project-image-placeholder-370x208.jpg', __FILE__);
				break;
			case 'checkout':
				$image = plugins_url('images/project-image-placeholder-500x282.jpg', __FILE__);
				break;
			
			default:
				# code...
				break;
		}
	}
	return $image;
}

function id_remove_success($post_id) {
	if (apply_filters('idcf_do_remove_success', true, $post_id)) {
		delete_post_meta($post_id, 'ign_project_success');
	}
}

add_action('idcf_remove_success', 'id_remove_success', 10, 1);

function id_create_order($order) {
	$fname = $order['fname'];
	$lname = $order['lname'];
	$email = $order['email'];
	$address = $order['address'];
	$city = $order['city'];
	$state = $order['state'];
	$zip = $order['zip'];
	$country = $order['country'];
	$project_id = $order['project_id'];
	$txn_id = $order['txn_id'];
	$preapproval_key = $order['preapproval_key'];
	$level = $order['level'];
	$price = $order['price'];
	$status = $order['status'];
	$date = $order['date'];

	$new_order = new ID_Order(null,
		$fname,
		$lname,
		$email,
		$address,
		$city,
		$state,
		$zip,
		$country,
		$project_id,
		$txn_id,
		$preapproval_key,
		$level,
		$price,
		$status,
		$date);
	$order_id = $new_order->insert_order;
	return $order_id;
}

//AJAX for getting the product number from product id
function get_product_number_callback() {
	global $wpdb;
	
	//GET product number by product id
	$prod_no = getProductNumberFromProductID($_POST['product_id']);
	
	echo $prod_no;
	exit;
			
}
add_action('wp_ajax_get_product_number', 'get_product_number_callback');
add_action('wp_ajax_nopriv_get_product_number', 'get_product_number_callback');

// AJAX call for deleting product image coming as an argument
function remove_product_image_callback() {
	global $wpdb;
	$post_id = absint($_POST['post_id']);
	$image = esc_attr($_POST['image']);
	$del = delete_post_meta($post_id, $image);
	exit;
}
add_action('wp_ajax_remove_product_image', 'remove_product_image_callback');
add_action('wp_ajax_nopriv_remove_product_image', 'remove_product_image_callback');

// AJAX call for deleting product image coming as an argument
// probably unused
function get_pages_links_callback() {
	global $wpdb, $post;
	
	$sql = "SELECT * FROM ".$wpdb->prefix."posts WHERE post_title LIKE '%".$_POST['page_title']."%' AND (post_type = 'ignition_product' OR post_type = 'post' OR post_type = 'page') AND post_status = 'publish'";
	$results = $wpdb->get_results( $sql );
	foreach( $results as $single_post ) {
		//setup_postdata($post);
		echo '<div class="post-link-container"><a class="post-link-filler" href="'.$single_post->guid.'">'.$single_post->post_title.'</a></div>';
	}
	exit;
}
add_action('wp_ajax_get_pages_links', 'get_pages_links_callback');
add_action('wp_ajax_nopriv_get_pages_links', 'get_pages_links_callback');

class ID_Pagination{
/*
Script Name: *Digg Style Paginator Class
Script URI: http://www.mis-algoritmos.com/2007/05/27/digg-style-pagination-class/
Description: Class in PHP that allows to use a pagination like a digg or sabrosus style.
Script Version: 0.4
Author: Victor De la Rocha
Author URI: http://www.mis-algoritmos.com
*/
	/*Default values*/
	var $total_pages = -1;//items
	var $limit = null;
	var $target = ""; 
	var $page = 1;
	var $adjacents = 2;
	var $showCounter = false;
	var $className = "ID_Pagination";
	var $parameterName = "page";
	var $urlF = false;//urlFriendly

	/*Buttons next and previous*/
	var $nextT = "Next";
	var $nextI = "&#187;"; //&#9658;
	var $prevT = "Previous";
	var $prevI = "&#171;"; //&#9668;

	/*****/
	var $calculate = false;
	
	#Total items
	function items($value){$this->total_pages = (int) $value;}
	
	#how many items to show per page
	function limit($value){$this->limit = (int) $value;}
	
	#Page to sent the page value
	function target($value){$this->target = $value;}
	
	#Current page
	function currentPage($value){$this->page = (int) $value;}
	
	#How many adjacent pages should be shown on each side of the current page?
	function adjacents($value){$this->adjacents = (int) $value;}
	
	#show counter?
	function showCounter($value=""){$this->showCounter=($value===true)?true:false;}

	#to change the class name of the pagination div
	function changeClass($value=""){$this->className=$value;}

	function nextLabel($value){$this->nextT = $value;}
	function nextIcon($value){$this->nextI = $value;}
	function prevLabel($value){$this->prevT = $value;}
	function prevIcon($value){$this->prevI = $value;}

	#to change the class name of the pagination div
	function parameterName($value=""){$this->parameterName=$value;}

	#to change urlFriendly
	function urlFriendly($value="%"){
			if(preg_match('^ *$',$value)){
					$this->urlF=false;
					return false;
				}
			$this->urlF=$value;
		}
	
	var $pagination;

	function __construct() {}
	function show(){
			if(!$this->calculate)
				if($this->calculate())
					echo "<div class=\"$this->className\">$this->pagination</div>\n";
		}
	function getOutput(){
			if(!$this->calculate)
				if($this->calculate())
					return "<div class=\"$this->className\">$this->pagination</div>\n";
		}
	function get_pagenum_link($id){
			if(strpos($this->target,'?')===false)
					if($this->urlF)
							return str_replace($this->urlF,$id,$this->target);
						else
							return "$this->target?$this->parameterName=$id";
				else
					return "$this->target&$this->parameterName=$id";
		}
	
	function calculate(){
			$this->pagination = "";
			$this->calculate == true;
			$error = false;
			if($this->urlF and $this->urlF != '%' and strpos($this->target,$this->urlF)===false){
					//Es necesario especificar el comodin para sustituir
					echo "Especificaste un wildcard para sustituir, pero no existe en el target<br />";
					$error = true;
				}elseif($this->urlF and $this->urlF == '%' and strpos($this->target,$this->urlF)===false){
					echo "Es necesario especificar en el target el comodin % para sustituir el número de página<br />";
					$error = true;
				}

			if($this->total_pages < 0){
					echo "It is necessary to specify the <strong>number of pages</strong> (\$class->items(1000))<br />";
					$error = true;
				}
			if($this->limit == null){
					echo "It is necessary to specify the <strong>limit of items</strong> to show per page (\$class->limit(10))<br />";
					$error = true;
				}
			if($error)return false;
			
			$n = trim($this->nextT.' '.$this->nextI);
			$p = trim($this->prevI.' '.$this->prevT);
			
			/* Setup vars for query. */
			if($this->page) 
				$start = ($this->page - 1) * $this->limit;             //first item to display on this page
			else
				$start = 0;                                //if no page var is given, set start to 0
		
			/* Setup page vars for display. */
			$prev = $this->page - 1;                            //previous page is page - 1
			$next = $this->page + 1;                            //next page is page + 1
			$lastpage = ceil($this->total_pages/$this->limit);        //lastpage is = total pages / items per page, rounded up.
			$lpm1 = $lastpage - 1;                        //last page minus 1
			
			/* 
				Now we apply our rules and draw the pagination object. 
				We're actually saving the code to a variable in case we want to draw it more than once.
			*/
			
			if($lastpage > 1){
					if($this->page){
							//anterior button
							if($this->page > 1)
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($prev)."\" class=\"prev\">$p</a>";
								else
									$this->pagination .= "<span class=\"disabled\">$p</span>";
						}
					//pages	
					if ($lastpage < 7 + ($this->adjacents * 2)){//not enough pages to bother breaking it up
							for ($counter = 1; $counter <= $lastpage; $counter++){
									if ($counter == $this->page)
											$this->pagination .= "<span class=\"current\">$counter</span>";
										else
											$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
								}
						}
					elseif($lastpage > 5 + ($this->adjacents * 2)){//enough pages to hide some
							//close to beginning; only hide later pages
							if($this->page < 1 + ($this->adjacents * 2)){
									for ($counter = 1; $counter < 4 + ($this->adjacents * 2); $counter++){
											if ($counter == $this->page)
													$this->pagination .= "<span class=\"current\">$counter</span>";
												else
													$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
										}
									$this->pagination .= "...";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($lpm1)."\">$lpm1</a>";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($lastpage)."\">$lastpage</a>";
								}
							//in middle; hide some front and some back
							elseif($lastpage - ($this->adjacents * 2) > $this->page && $this->page > ($this->adjacents * 2)){
									$this->pagination .= "<a href=\"".$this->get_pagenum_link(1)."\">1</a>";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link(2)."\">2</a>";
									$this->pagination .= "...";
									for ($counter = $this->page - $this->adjacents; $counter <= $this->page + $this->adjacents; $counter++)
										if ($counter == $this->page)
												$this->pagination .= "<span class=\"current\">$counter</span>";
											else
												$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
									$this->pagination .= "...";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($lpm1)."\">$lpm1</a>";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($lastpage)."\">$lastpage</a>";
								}
							//close to end; only hide early pages
							else{
									$this->pagination .= "<a href=\"".$this->get_pagenum_link(1)."\">1</a>";
									$this->pagination .= "<a href=\"".$this->get_pagenum_link(2)."\">2</a>";
									$this->pagination .= "...";
									for ($counter = $lastpage - (2 + ($this->adjacents * 2)); $counter <= $lastpage; $counter++)
										if ($counter == $this->page)
												$this->pagination .= "<span class=\"current\">$counter</span>";
											else
												$this->pagination .= "<a href=\"".$this->get_pagenum_link($counter)."\">$counter</a>";
								}
						}
					if($this->page){
							//siguiente button
							if ($this->page < $counter - 1)
									$this->pagination .= "<a href=\"".$this->get_pagenum_link($next)."\" class=\"next\">$n</a>";
								else
									$this->pagination .= "<span class=\"disabled\">$n</span>";
								if($this->showCounter)$this->pagination .= "<div class=\"pagination_data\">($this->total_pages Pages)</div>";
						}
				}

			return true;
		}
}
function get_id_skins() {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->prefix.'ign_settings WHERE id="1"';
	$res = $wpdb->get_row($sql);
	//$skins = $sql->theme_choices;
	$skins = unserialize($res->theme_choices);
	$content = '';
	if ($skins) {
		foreach ($skins as $skin) {
			$content .= '<option '. ($res->theme_value == $skin ? 'selected="selected"' : '').' value="'.$skin.'">'.$skin.'</option>';
		}
	}
	echo $content;
}

add_filter('id_skin', 'get_id_skins', 10, 1);

function deleted_skin_list($skins) {
	$content = '';
	if ($skins) {
		foreach ($skins as $skin) {
			$content .= '<option name="'.$skin.'" id="'.$skin.'" value="'.$skin.'">'.$skin.'</option>';
		}
	}
	return $content;
}

function id_validate_price() {
	global $wpdb;
	if ($_POST) {
		if($_POST['Keys']) {
			$data = $_POST['Keys'][0];
		}
	}
	$level = $data['level'];
	$project = $data['project'];
	$post_id = $data['post_id'];
	if ($level) {
		if ($level == 1) {
			$sql = $wpdb->prepare('SELECT product_price FROM '.$wpdb->prefix.'ign_products WHERE id=%d', $project);
			$res = $wpdb->get_row($sql);
			$price = $res->product_price;
		}
		else {
			$price = get_post_meta($post_id, 'ign_product_level_'.$level.'_price', true);
		}
		echo $price;
		exit;
	}
	else {
		return false;
	}
}

if (is_id_licensed()) {
	add_action('wp_ajax_id_validate_price', 'id_validate_price');
	add_action('wp_ajax_nopriv_id_validate_price', 'id_validate_price');
}

function project_posts_list_ajax() {
	$projects = ID_Project::get_project_posts();
	print_r(json_encode($projects));
	exit;
}

add_action('wp_ajax_project_posts_list_ajax', 'project_posts_list_ajax');
add_action('wp_ajax_nopriv_project_posts_list_ajax', 'project_posts_list_ajax');

//AJAX for product levels
function get_product_levels_callback() {
	global $wpdb;
	if (isset($_POST['Project'])) {
		$project_id = absint($_POST['Project']);
		$project = new ID_Project($project_id);
		
		$product_settings = $project->get_project_settings();
		if (empty($product_settings)) {
			$product_settings = $project->get_project_defaults();
		}
		//GETTING the currency symbol
		if (isset($product_settings)) {
			$currencyCodeValue = $product_settings->currency_code;	
			$cCode = setCurrencyCode($currencyCodeValue);
		}
		else {
			$currencyCodeValue = 'USD';
			$cCode = '$';
		}
		
		$post_id = $project->get_project_postid();
		
		$level_count = get_post_meta($post_id, 'ign_product_level_count', true);
		$meta_price_1 = get_post_meta( $post_id, "ign_product_price", true );
		$options = "<option data-price='".id_price_format($meta_price_1)."' value=\"1\">".__('Level', 'ignitiondeck')." 1: ".apply_filters('id_display_currency', apply_filters('id_price_format', absint($meta_price_1), $post_id), absint($meta_price_1), $post_id)."</option>";
		if (isset($level_count) && $level_count > 1) {
			
			for ($i=2 ; $i <= $level_count ; $i++) {
				$meta_price = get_post_meta( $post_id, $name="ign_product_level_".($i)."_price", true );
				$options .= "<option data-price='".id_price_format($meta_price, $post_id)."' value=\"".($i)."\">".__('Level', 'ignitiondeck')." ".($i).": ".($meta_price > 0 ? apply_filters('id_display_currency', apply_filters('id_price_format', $meta_price, $post_id), $meta_price, $post_id) : __('No Price Set', 'ignitiondeck'))."</option>";
			}
		}
		echo $options;
	}
	exit;
			
}
add_action('wp_ajax_get_product_levels', 'get_product_levels_callback');
add_action('wp_ajax_nopriv_get_product_levels', 'get_product_levels_callback');

function get_order_level() {
	$order_id = absint($_POST['Order']);
	if (!empty($order_id)) {
		$order = new ID_Order($order_id);
		$get_order = $order->get_order();
		if (isset($get_order)) {
			echo $get_order->product_level;
		}
	}
	exit;
}

add_action('wp_ajax_get_order_level', 'get_order_level');
add_action('wp_ajax_nopriv_get_order_level', 'get_order_level');

//AJAX for getting the new product number automatically
function idcf_project_id() {
	global $wpdb;
	$project_id = null;
	if (isset($_POST['postID'])) {
		$post_id = $_POST['postID'];
		$project_id = get_post_meta($post_id, 'ign_project_id', true);
	}
	echo $project_id;
	exit;			
}
add_action('wp_ajax_idcf_project_id', 'idcf_project_id');
add_action('wp_ajax_nopriv_idcf_project_id', 'idcf_project_id');

function get_deck_list() {
	$list = Deck::get_deck_list();
	$decks = array();
	foreach ($list as $item) {
		$deck = array();
		$deck['id'] = $item->id;
		$deck['attrs'] = unserialize($item->attributes);
		$decks[] = $deck;
	}
	print_r(json_encode($decks));
	exit;
}

add_action('wp_ajax_get_deck_list', 'get_deck_list');
add_action('wp_ajax_nopriv_get_deck_list', 'get_deck_list');

function get_deck_attrs() {
	global $wpdb;
	if (isset($_POST['Deck'])) {
		$deck_id = absint($_POST['Deck']);
		if ($deck_id > 0) {
			$settings = Deck::get_deck_attrs($deck_id);
			$attrs = unserialize($settings->attributes);
			print_r(json_encode($attrs));
		}
	}
	exit;
}

add_action('wp_ajax_get_deck_attrs', 'get_deck_attrs');
add_action('wp_ajax_nopriv_get_deck_attrs', 'get_deck_attrs');

function id_hide_notice() {
	if (isset($_POST['Notice'])) {
		$notice = $_POST['Notice'];
		//echo $notice;
		update_option($notice, 'off');
	}
	exit;
}

add_action('wp_ajax_id_hide_notice', 'id_hide_notice');
add_action('wp_ajax_nopriv_id_hide_notice', 'id_hide_notice');
?>