<?php
if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	    exit();
global $wpdb;		

delete_option( 'isms_account_settings' );
delete_option('wc_isms_default');

delete_option('wc_isms_setting_payment_complete_to_admin');
delete_option('wc_isms_setting_payment_complete_to_customer');
delete_option('wc_isms_setting_order_processing_to_admin');
delete_option('wc_isms_setting_order_processing_to_customer');
delete_option('wc_isms_setting_payment_complete_send_customer');
delete_option('wc_isms_setting_order_processing_send_customer');
delete_option('wc_isms_setting_order_processing_send_admin');
delete_option('wc_isms_setting_payment_complete_send_admin');
delete_option('wc_isms_order_processing_enable');

$msg_sent = $wpdb->prefix. "woocommerce_isms_msg_sent" ;

$sql="DROP TABLE $msg_sent";
$wpdb->query($sql);


