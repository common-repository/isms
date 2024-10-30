<?php 
/* @package iSMS SMS API Integration*/
/**
 * Plugin Name:       iSMS Wordpress E-Commerce SMS Notification
 * Plugin URI:        https://www.isms.com.my
 * Description:		  iSMS WordPress SMS integration with E-Commerce Online Store .
 * Version:           1.4
 * Requires at least: 5.7.2
 * Requires PHP:      7.0
 * Author:            Mobiweb
 * Author URI:        https://www.mobiweb.com.my
 * License:           GPLv2 or later
 * Text Domain:       isms
 */



defined('ABSPATH') or die( 'Access Forbidden!' );

global $wpdb;
define('ISMS_MSG_SENT_TABLE',$wpdb->prefix. "woocommerce_isms_msg_sent" );

require_once(dirname(__FILE__) . '/includes/Plugin.php');
require_once(dirname(__FILE__) . '/includes/iSMSProcess.php');

class wc_isms extends wc_isms\includes\Plugin {

    private $isms = null;
    
    public function __construct() {
        $this->name = plugin_basename(__FILE__);
        $this->pre = strtolower(__CLASS__);
        $this->version = '1.0.0.0';

         $this->actions = array(
            'plugins_loaded'        =>  false
        );
         //register the plugin and init assets
        $this->register_plugin($this->name, __FILE__, true);
    }

     public function plugins_loaded() {
        require_once(dirname(__FILE__) . '/includes/iSMS.php');
        $this->isms = new \wc_isms\includes\iSMS();
         
          
    }
}
function fm_activate() {
        global $wpdb;
         $sms_sent_db = $wpdb->query('CREATE TABLE `'.ISMS_MSG_SENT_TABLE.'` (
                      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                      `orderid` varchar(255) NOT NULL,
                      `msg_type` varchar(255) NOT NULL,
                      `to` varchar(255) DEFAULT NULL,
                      `message` varchar(255) DEFAULT NULL,
                      `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1'
                  );
}
register_activation_hook(__FILE__,'fm_activate');


$GLOBALS['iSMS'] = new wc_isms();

?>