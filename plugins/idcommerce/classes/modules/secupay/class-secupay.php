<?php
class ID_Secupay {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'id_secupay-hooks.php';
		if (self::is_active()) {
			require dirname(__FILE__) . '/' . 'id_secupay-tests.php';
		}
	}

	private static function set_filters() {
		add_action('wp_enqueue_scripts', 'id_secupay_scripts', 11);
		add_action('id_set_module_status_before', array('ID_Secupay', 'module_status_actions'), 10, 2);
		add_action('idc_other_gateways_after', 'id_secupay_admin_toggle');
		add_action('init', 'id_secupay_query_handler');
		if (self::is_active()) {
			add_action('admin_menu', 'id_secupay_admin', 12);
			add_filter('idc_localization_strings', 'id_secupay_localization_strings', 11);
			add_action('idc_after_credit_card_selectors', 'id_secupay_checkout_selector');
			add_filter('id_secupay_demo_mode', 'id_secupay_demo_check');
			//add_filter('id_secupay_host', array('ID_Secupay', 'id_secupay_host_check'));
			add_action('doing_idc_checkout', 'id_secupay_checkout_actions');
			add_filter('idc_checkout_descriptions', 'id_secupay_checkout_descriptions', 10, 7);
			add_filter('id_secupay_payment_action', 'id_secupay_payment_action', 10, 2);
			add_action('init', array('ID_Secupay', 'id_secupay_ajax_handler'));
			add_action('id_secupay_request_data', 'id_secupay_shipping_info', 10, 2);
			add_action('init', 'id_secupay_webhook_handler');
			add_filter('idc_order_currency', 'id_secupay_order_currency', 10, 3);
			add_action('idc_before_preauth_processing', 'id_secupay_process_preauth');
			global $crowdfunding;
			if ($crowdfunding) {
				add_filter('memberdeck_payment_success', 'id_secupay_idcf_order', 3, 5);
				add_filter('memberdeck_preauth_success', 'id_secupay_idcf_order', 3, 5);
			}
		}
	}

	public static function is_active() {
		$settings = maybe_unserialize(get_option('memberdeck_gateways'));
		return isset($settings['enable_secupay']) ? $settings['enable_secupay'] : 0;
	}

	public static function module_status_actions($module, $status) {
		if ($module == 'secupay') {
			if (!$status) {
				$settings = maybe_unserialize(get_option('memberdeck_gateways'));
				$settings['enable_secupay'] = 0;
				update_option('memberdeck_gateways', $settings);
			}
		}
	}

	public static function id_secupay_host_check() {
		$gateways = get_option('memberdeck_gateways');
		$gateways = maybe_unserialize($gateways);
		if (isset($gateways['test']) && $gateways['test']) {
			return 'api-dist.secupay-ag.de';
		}
		return 'api.secupay.ag';
	}

	public static function id_secupay_ajax_handler() {
		add_action('wp_ajax_id_secupay_submit', 'id_secupay_submit');
		add_action('wp_ajax_nopriv_id_secupay_submit', 'id_secupay_submit');
	}

	public static function id_secupay_request_handler($request_data, $endpoint = 'init', $format = 'application/json', $log = true) {
		$return_data = array(
			'status' => 'error',
			'data' => __('Error processing request', 'memberdeck').':'.__LINE__
		);
		try {
			$sp_api = new secupay_api($request_data, $endpoint, $format, $log);
			$api_return = $sp_api->request();
			if (!empty($api_return->status)) {
				switch ($api_return->status) {
					case 'ok':
						// success, return default data
						$return_data = $api_return;
						break;

					default:
						// error, lets bundle up the message for ease of use
						$status = $api_return->status;
						$message = $api_return->errors[0]->code.':'.$api_return->errors[0]->message.':'.(isset($api_return->errors[0]->field) ? $api_return->errors[0]->field : '').__LINE__;
						$return_data = array(
							'status' => $api_return->status,
							'data' => $message.':'.__LINE__
						);
						break;
				}
			}
		}
		catch(Exception $e) {
			$return_data = array(
				'status' => 'exception',
				'data' => $e.':'.__LINE__
			);
		}
		return $return_data;
	}
	
}
new ID_Secupay();
?>