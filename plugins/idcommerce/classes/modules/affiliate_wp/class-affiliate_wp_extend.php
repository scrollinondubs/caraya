<?php

class ID_Affiliate_WP_Extend extends Affiliate_WP_Base {
	
	function init() {
		$this->context = 'ignitiondeck';
		self::set_filters();
	}

	function set_filters() {
		add_action('init', array($this, 'add_pending_referral'));
		add_action('idc_before_order_delete', array($this, 'revoke_referral'));
		// #devnote add additional hooks once order status flags are developed
	}

	function add_pending_referral() {
		// #devnote could we find a way to use payment_success hook despite IPN not registering cookie?

		if (empty($_GET['paykey'])) {
			return; // not a success page
		}

		if (!$this->was_referred()) {
			$this->log( 'Is not a refferal. Referral not created.' );
			return; // not a referral
		}

		$paykey = sanitize_text_field($_GET['paykey']);
		$meta = self::paykey_to_order($paykey);
		if (empty($meta)) {
			$this->log( 'Could not retrieve order meta. Referral not created.' );
			return; // cannot retrieve order meta
		}

		$order = new ID_Member_Order($meta->order_id);
		$the_order = $order->get_order();
		if (empty($the_order)) {
			$this->log( 'Could not retrieve order data. Referral not created.' );
			return; // no order data, cannot apply referral
		}

		$user = get_user_by('id', $the_order->user_id);

		if (!empty($user->user_email) && $this->is_affiliate_email($user->user_email)) {
			$this->log( 'Referral not created because affiliate\'s own account was used.' );
			return; // self-referral, no credit applied
		}

		$level = ID_Member_Level::get_level($the_order->level_id);
		if (empty($level)) {
			$this->log( 'Could not retrieve product data. Referral not created.' );
			return; // cannot retrieve level data
		}

		$referral_total = $this->calculate_referral_amount($the_order->price, $the_order->transaction_id);

		$this->insert_pending_referral($referral_total, $the_order->transaction_id, $level->level_name);

		return;
	}

	function revoke_referral($order_id) {
		// #devnote should we delete instead of revoke?
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (empty($the_order)) {
			$this->log( 'Could not retrieve order data. Referral for order: #'.$order_id.' could not be revoked.' );
			return; // no order data, cannot revoke referral
		}
		$this->reject_referral($the_order->transaction_id);
		return;
	}

	function paykey_to_order($paykey) {
		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_order_meta WHERE meta_key = %s AND meta_value = %s', 'paykey', $paykey);
		$res = $wpdb->get_row($sql);
		return $res;
	}

}
?>