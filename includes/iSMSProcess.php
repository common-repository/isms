<?php
namespace wc_isms\includes;

defined('ABSPATH') or die( 'Access Forbidden!' );

class iSMSProcess {

    private $endpoint;
    private $options;
    private $username;
    private $password;
    private $prefix;

    function __construct() {

        $this->options = get_option( 'isms_account_settings' );
        $this->endpoint = 'https://www.isms.com.my/RESTAPI.php';
        $this->username = $this->options['username'];
        $this->password = $this->options['password'];


    }

    function send_admin_notification() {
        $data = array (
            'sendid' => $this->options['sendid'],
            'dstno' => $this->options['phone'],
            'msg' => 'WooCommerce Notification after successful checkout test',
            'type' => '1',
            'agreedterm' =>  'YES',
            'method' => 'isms_send_all_id'
        );
        $payload = json_encode($data);

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic '. base64_encode("$this->username:$this->password"),
            'Content-Length: ' . strlen($payload)
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        return $result;

        curl_close($ch);
    }

    function send_notification($params) {
        if($params['type'] == 'all'){
            $data = array (
                'sendid' => $this->options['sendid'],
                'recipient' => $params['to'],
                'agreedterm' =>  'YES',
                'method' => 'isms_send_all_id'
            );
        }else {
            $data = array (
                'sendid' => $this->options['sendid'],
                'dstno' => $params['dstno'],
                'msg' => $params['msg'],
                'type' => '1',
                'agreedterm' =>  'YES',
                'method' => 'isms_send_all_id'
            );
        }

        $payload = json_encode($data);

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic '. base64_encode("$this->username:$this->password"),
            'Content-Length: ' . strlen($payload)
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        return $result;

        curl_close($ch);
    }

    function get_data($method) {
        $data = array (
            'method' => $method
        );
        $payload = json_encode($data);
        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Basic '. base64_encode("$this->username:$this->password"),
            'Content-Length: ' . strlen($payload)
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        echo json_decode($result);

        curl_close($ch);
    }

    function save_message ($data){
        global $wpdb;
        if ( $wpdb->insert( ISMS_MSG_SENT_TABLE,$data)){
            return true;
        }
    }

    function get_db_data($table) {
        global $wpdb;
        return $wpdb->get_results('SELECT * FROM '.$table.'', OBJECT );
    }

    function get_sent_message($order_id,$msg_type,$mobile){
        global $wpdb;
		
      return $wpdb->get_var('SELECT COUNT(*) FROM `'.ISMS_MSG_SENT_TABLE.'` WHERE orderid = '.$order_id.' AND msg_type = "'.$msg_type.'" AND `to` = '.$mobile.' AND timestamp > NOW() - INTERVAL 1 HOUR ');	
    }
}



?>