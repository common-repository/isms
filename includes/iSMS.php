<?php
namespace wc_isms\includes;
defined('ABSPATH') or die( 'Access Forbidden!' );
class iSMS {

    private $admin_options;
    public $isms_process;
    private $order_action;

    function __construct() {

        add_action('admin_menu', array($this,'hook_to_menu') );
        add_action('admin_init', array( $this, 'isms_init' ) );
        add_action("admin_enqueue_scripts", array($this,"scripts_and_style"));
        add_action('wp_enqueue_scripts', array($this,"isms_public_scripts_and_style"));

        if (! class_exists( 'wp_isms_authenticator' )) {
            add_action( 'woocommerce_register_form_start', array($this, 'isms_mobile_register_field'));
            add_action( 'woocommerce_register_post', array($this,'isms_validate_mobile_register_field'), 10, 3 );
            add_action( 'woocommerce_created_customer', array($this,'isms_save_mobile_register_field') );
        }
        
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::isms_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_isms_setting', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_isms_setting', __CLASS__ . '::update_settings' );
        add_action( 'add_meta_boxes', array( $this, 'add_isms_metabox' ) );

        //WooCommerce Hook
        add_action('woocommerce_thankyou', array($this, 'wc_order_processing_thank_you'));
        //add_action('woocommerce_order_status_processing', array($this, 'wc_order_processing'));
        add_action('woocommerce_payment_complete', array($this, 'wc_payment_complete'));
        add_action('woocommerce_order_status_failed',  array($this, 'wc_order_failed'));
        add_action('woocommerce_order_status_cancelled', array($this, 'wc_order_cancelled'));
        add_action('woocommerce_order_status_refunded', array($this, 'wc_order_refunded'));
        add_action('woocommerce_order_status_on-hold', array($this, 'wc_order_onhold'));
        add_action('woocommerce_order_status_pending', array($this, 'wc_order_pending'));


        //WooCommerce Hook

        $this->admin_options = get_option( 'isms_account_settings' );
        $this->isms_process = new \wc_isms\includes\iSMSProcess();
       

        add_action( 'wp_ajax_send_manual_sms', array($this, 'send_manual_sms') );
        add_action( 'wp_ajax_nopriv_send_manual_sms', array($this, 'send_manual_sms') );

        add_action( 'wp_ajax_get_sms_sent', array($this, 'get_sms_sent') );
        add_action( 'wp_ajax_nopriv_get_sms_sent', array($this, 'get_sms_sent') );

        add_action( 'wp_ajax_resend_notification', array($this, 'resend_notification') );
        add_action( 'wp_ajax_nopriv_resend_notification', array($this, 'resend_notification') );


    }


    /*START WOOCOMMERCE TAB
      /**
       * Add a new settings tab to the WooCommerce settings tabs array.
       *
       * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
       * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
       */
    public static function isms_settings_tab( $settings_tabs ) {
        $settings_tabs['isms_setting'] = __( 'iSMS', 'woocommerce-isms-setting' );
        return $settings_tabs;
    }
    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }
    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public  function get_settings() {
        if(isset($_GET['section'])){
            $section = $_GET['section'];
        }

        switch ($section) {
            case 'payment-complete':
                $settings = self::add_wc_isms_settings('payment_complete','Payment Complete');
                break;
            case 'order-processing':
                $settings = self::add_wc_isms_settings('order_processing','Order Processing');
                break;
            case 'order-completed':
                $settings = self::add_wc_isms_settings('order_completed','Order Completed');
                break;
            case 'order-failed':
                $settings = self::add_wc_isms_settings('order_failed','Order Failed');
                break;
            case 'order-cancelled':
                $settings = self::add_wc_isms_settings('order_cancelled','Order Cancelled');
                break;
            case 'order-refunded':
                $settings = self::add_wc_isms_settings('order_refunded','Order Refunded');
                break;
            case 'order-onhold':
                $settings = self::add_wc_isms_settings('order_onhold','Order Onhold');
                break;
            case 'order-pending':
                $settings = self::add_wc_isms_settings('order_pending','Order Pending');
                break;
            default:
                $settings = array(
                    'section_for_order_processing_list' => array(
                        'name'     => __( '', 'woocommerce-isms-setting' ),
                        'type'     => 'title',
                        'desc'     => '',
                        'id'       => 'wc_isms_setting_section_for_process_listing'
                    ),);

                self::isms_template('ProcessList');
        }
        return apply_filters( 'wc_isms_settings', $settings );
    }

    /*END WOOCOMMERCE TAB*/

    function scripts_and_style(){
        wp_enqueue_style("isms-datatable", 'https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css');
        wp_enqueue_style("isms-fontawesome", 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

        wp_enqueue_style("isms-prefix", plugins_url('../assets/prefix/css/intlTelInput.css', __FILE__));
        wp_enqueue_style("isms-style", plugins_url('../assets/css/ismsstyle.css', __FILE__));

        wp_enqueue_script("isms-datatables",'https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js');
        wp_enqueue_script("isms-prefix-js", plugins_url('../assets/prefix/js/intlTelInput.js', __FILE__));

         wp_enqueue_script("isms-js", plugins_url('../assets/js/ismsjs.js', __FILE__));
        wp_localize_script( 'isms-js', 'ismsajaxurl', array( "ismsscript" => admin_url('admin-ajax.php') ) );
      	wp_localize_script('isms-js', 'ismsScript', array(
        'pluginsUrl' => plugin_dir_url( __FILE__ ),
     	));
    }
	
    function isms_public_scripts_and_style($hook){
        wp_enqueue_style("isms-prefix", plugins_url('../assets/prefix/css/intlTelInput.css', __FILE__));
		  wp_enqueue_style("isms-style", plugins_url('../assets/css/publicismsstyle.css', __FILE__));
        wp_enqueue_script("isms-prefix-js", plugins_url('../assets/prefix/js/intlTelInput.js', __FILE__));
        wp_enqueue_script("isms-js", plugins_url('../assets/js/publicismsjs.js', __FILE__));
        wp_localize_script( 'isms-js', 'ismsajaxurl', array( "ismsscript" => admin_url('admin-ajax.php') ) );
        wp_localize_script('isms-js', 'ismsScript', array(
            'pluginsUrl' => plugin_dir_url( __FILE__ ),
        ));
    }
	
    function hook_to_menu() {
        add_menu_page(
            'iSMS API Integration',
            'iSMS',
            'manage_options',
            'isms-setting',
            array( $this, 'create_admin_page' ),'',6
        );
        add_submenu_page(
            'isms-setting',
            'iSMS Sent Messages',
            'Sent Messages',
            'manage_options',
            'isms-messege-sent',
             array( $this, 'sent_message' ),''
        );

    }

    function sent_message() {
        $this->isms_template('sent_message','');
    }

    function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>iSMS Account Settings</h1>
            <div class="isms-divider"></div>
            <?php
			
            if($this->admin_options){ ?>
                <div>
                    <h3>Your credit balance: <?php echo $this->isms_process->get_data('isms_balance'); ?></h3>
                    <h4>valid until <?php echo $this->isms_process->get_data('isms_expiry_date'); ?> </h4>

                </div>
            <?php } ?>

            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'isms_admin_settings' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function isms_init() {
        if(add_option('wc_isms_default') != 'added') {
            add_option('wc_isms_payment_complete_admin','You have got a payment ORDER_TOTAL ORDER_CURRENCY for order ID ORDER_ID on SITE_NAME.');
            add_option('wc_isms_payment_complete_customer','You have made a payment of ORDER_TOTAL ORDER_CURRENCY for order ID ORDER_ID on SITE_NAME.');
            add_option('wc_isms_order_processing_admin','You have got an order of ORDER_TOTAL ORDER_CURRENCY is under processing on SITE_NAME. Order ID ORDER_ID.');
            add_option('wc_isms_order_processing_customer','You have just order on SITE_NAME of ORDER_TOTAL ORDER_CURRENCY. Your Order ID ORDER_ID.');
            add_option('wc_isms_payment_complete_send_customer','yes');
            add_option('wc_isms_order_processing_send_customer','yes');
            add_option('wc_isms_order_processing_send_admin','no');
            add_option('wc_isms_payment_complete_send_admin','no');
            add_option('wc_isms_order_processing_enable','yes');
            add_option('wc_isms_default','added');
        }

        register_setting(
            'isms_admin_settings', // Option group
            'isms_account_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '', // Title
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );

        add_settings_field(
            'sendid', // ID
            'Sender ID', // Title
            array( $this, 'sendid_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section
        );
        add_settings_field(
            'username', // ID
            'Username', // Title
            array( $this, 'username_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section
        );

        add_settings_field(
            'phone',
            'Admin Phone',
            array( $this, 'phone_callback' ),
            'my-setting-admin',
            'setting_section_id'
        );
        add_settings_field(
            'password',
            'Password',
            array( $this, 'password_callback' ),
            'my-setting-admin',
            'setting_section_id'
        );



    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['sendid'] ) )
            $new_input['sendid'] = sanitize_text_field( $input['sendid'] );

        if( isset( $input['username'] ) )
            $new_input['username'] = sanitize_text_field( $input['username'] );

        if( isset( $input['phone'] ) )
            $new_input['phone'] = sanitize_text_field( $input['phone'] );

        if( isset( $input['password'] ) )
            $new_input['password'] = sanitize_text_field( $input['password'] );

        return $new_input;

    }

    /**
     * Print the Section text
     */

    public function print_section_info() {
        print 'Enter your iSMS credentials';
    }

    public function sendid_callback() {
        printf(
            '<input type="text" style="width: 210px" id="sendid" autocomplete="off" name="isms_account_settings[sendid]" value="%s" />',
            isset( $this->admin_options['sendid'] ) ? esc_attr( $this->admin_options['sendid']) : ''
        );
    }

    public function username_callback() {
        printf(
            '<input type="text" style="width: 210px" id="username" autocomplete="off" name="isms_account_settings[username]" value="%s" />',
            isset( $this->admin_options['username'] ) ? esc_attr( $this->admin_options['username']) : ''
        );
    }


    public function phone_callback() {
        printf(
            '<input type="text" style="width: 210px" id="phone" autocomplete="off" name="isms_account_settings[phone]" value="%s" />',
            isset( $this->admin_options['phone'] ) ? esc_attr( $this->admin_options['phone']) : ''
        );
    }

    public function password_callback() {
        printf(
            '<input type="password" style="width: 210px" id="password" autocomplete="off" name="isms_account_settings[password]" value="%s" />',
            isset( $this->admin_options['password'] ) ? esc_attr( $this->admin_options['password']) : ''
        );
    }

    function add_wc_isms_settings($process,$label) {
        $settings = array(
            'section_for_'.$process => array(
                'name'     => __( 'Send SMS FOR '.$label, 'woocommerce-isms-setting' ),
                'type'     => 'title',
                'desc'     => '',
                'id'       => 'wc_isms_setting_section_for_'.$process
            ),
            $process.'_enable' => array(
                'name' => __( 'Enable/Disable', 'woocommerce-isms-setting' ),
                'type' => 'checkbox',
                'desc' => __( 'Enable Notification', 'woocommerce-isms-setting' ),
                'id'   => 'wc_isms_'.$process.'_enable'

            ),
            $process.'_sms_to_admin' => array(
                'name' => __( 'SMS To Admin For '.$label, 'woocommerce-isms-setting' ),
                'type' => 'textarea',
                'id'   => 'wc_isms_'.$process.'_admin'
            ),
            $process.'_sms_to_customer' => array(
                'name' => __( 'SMS To Customer For '.$label, 'woocommerce-isms-setting' ),
                'type' => 'textarea',
                'id'   => 'wc_isms_'.$process.'_customer'
            ),
            $process.'_send_admin' => array(
                'name' => __( '', 'woocommerce-isms-setting' ),
                'type' => 'checkbox',
                'desc' => __( 'Send SMS To Admin On '.$label, 'woocommerce-isms-setting' ),
                'id'   => 'wc_isms_'.$process.'_send_admin'
            ),
            $process.'_send_customer' => array(
                'name' => __( '', 'woocommerce-isms-setting' ),
                'type' => 'checkbox',
                'desc' => __( 'Send SMS To Customer On '.$label, 'woocommerce-isms-setting' ),
                'id'   => 'wc_isms_'.$process.'_send_customer'
            ),
            $process.'_section_end' => array(
                'type' => 'sectionend',
                'id' => 'wc_isms_setting_'.$process.'_section_end'
            ),
        );
        return $settings;
    }

    public function proc_list_td($process,$url){
        ob_start();
        $html = '';
        $html .= '<td>';
        if(get_option('wc_isms_'.$process.'_send_customer') == 'yes' && get_option('wc_isms_'.$process.'_send_admin') == 'no'){
            $html .= "Customer";
        }else if(get_option('wc_isms_'.$process.'_send_admin') == 'yes' && get_option('wc_isms_'.$process.'_send_customer') == 'no'){
            $html .= "Administrator";
        }else if(get_option('wc_isms_'.$process.'_send_admin') == 'yes' && get_option('wc_isms_'.$process.'_send_customer') == 'yes') {
            $html .="Administrator, Customer";
        }
        $html .= '</td><td>';
        if(get_option('wc_isms_'.$process.'_enable') == 'yes'){  $html .= '<i class="fa fa-check" aria-hidden="true"></i>'; }else{  $html .='<i class="fa fa-times" aria-hidden="true"></i>'; } ;
        $html .= '</td><td><a class="button alignright" href="'.get_site_url().'/wp-admin/admin.php?page=wc-settings&tab=isms_setting&section='.$url.'">Manage</a></td>';

        return $html;
        ob_clean();
    }

    public function isms_mobile_register_field() { ?>
        <link rel="stylesheet" id="register-prefix-css" href="<?php  echo plugins_url('../assets/prefix/css/intlTelInput.css', __FILE__); ?>" media="all"/>
        <script type="text/javascript" src="<?php echo plugins_url('../assets/prefix/js/intlTelInput.js', __FILE__); ?>" ></script>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                if($('body').hasClass('woocommerce-account')) {
                    $(document.body).on('keyup', '#reg-phone', function () {
                        $(this).val($(this).val().replace(/^0+/, ''));
                    });

                    var input = document.querySelector("#reg-phone");
                    window.intlTelInput(input, {
                        // allowDropdown: false,
                        //autoHideDialCode: false,
                        //autoPlaceholder: "off",
                        // dropdownContainer: document.body,
                        // excludeCountries: ["us"],
                        //formatOnDisplay: false,
                        // geoIpLookup: function(callback) {
                        //   $.get("http://ipinfo.io", function() {}, "jsonp").always(function(resp) {
                        //     var countryCode = (resp && resp.country) ? resp.country : "";
                        //     callback(countryCode);
                        //   });
                        // },
                        hiddenInput: "billing_phone",
                        // initialCountry: "auto",
                        // localizedCountries: { 'de': 'Deutschland' },
                        // nationalMode: false,
                        // onlyCountries: ['us', 'gb', 'ch', 'ca', 'do'],
                        placeholderNumberType: "MOBILE",
                        preferredCountries: ['my', 'jp'],
                        separateDialCode: true,
                        utilsScript: "<?php echo plugins_url('../assets/prefix/js/utils.js?1581331045115', __FILE__); ?>",

                    });
                }
            });
        </script>

       <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide ">
        <label for="text-phone"><?php _e( 'Phone', 'woocommerce' ); ?><span class="required">*</span></label>
        <input type="tel" class="input-text" name="billing_phone" id="reg-phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
    <?php }

    /**
     * register fields Validating.
     */

    function isms_validate_mobile_register_field( $username, $email, $validation_errors ) {
        if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
            $validation_errors->add( 'billing_phone_error', __( '<strong>Error</strong>: Phone is required!', 'woocommerce' ) );
        }
        return $validation_errors;
    }

    /**
     * Below code save extra fields.
     */
    function isms_save_mobile_register_field( $customer_id ) {
        if ( isset( $_POST['billing_phone'] ) ) {
            // Phone input filed which is used in WooCommerce
            update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
        }

    }




    /*START PROCESSES*/
    public function get_sms_sent(){
        $message = $this->isms_process->get_db_data(ISMS_MSG_SENT_TABLE);
        $lst = array();

        if($message) {
            foreach ($message as $msg) {
                $lst['data'][]  = array(
                    'order_id' => $msg->orderid,
                    'msg_type' => $msg->msg_type,
                    'phone_no' => $msg->to,
                    'message' => $msg->message,
                    'date' => date("l, F d, Y h:i:sa", strtotime($msg->timestamp))

                );
            }
            wp_send_json($lst);
        }else{
            $lst['data'][]  = array(
                'order_id' => "",
                'msg_type' => "",
                'phone_no' => "No data available",
                'message' => "",
                'date' => ""

            );
            wp_send_json($lst);
        }
    }

    public function add_isms_metabox() {
        add_meta_box( 'isms_meta_box','Send SMS',array( $this, 'print_isms_send_meta_box' ), 'shop_order', 'side' );
    }

    public function print_isms_send_meta_box( $post ) {
        $order = wc_get_order( $post->ID );
        $this->isms_template('ManualSend',$order);

    }

    public function send_manual_sms() {
        $order = new \WC_Order($_POST['order_id']);
        $msg = $this->format_message($order,$_POST['msg']);

        $params = array(
            'dstno' => str_replace("+", '', $_POST['dst']),
            'msg' => $msg
        );


        $result = $this->isms_process->send_notification($params);
        $json_result = json_decode($result);
        $response_code = explode("=",$json_result);

        if($response_code[0] == 2000) {
            $save = array(
                'orderid' => $_POST['order_id'],
                'msg_type' => "Manual Sent",
                'to' => str_replace("+", '', $_POST['dst']),
                'message' => $msg
            );
            $this->isms_process->save_message($save);
            wp_send_json(true);
        }else {
            wp_send_json(false);
        }
    }

    public function wc_order_processing_thank_you($order_id){
        if ($this->is_notification_enabled('order_processing')) {
            $this->process_notification($order_id, 'order_processing', 'Order Processing');
        }
    }

    public function wc_payment_complete($order_id) {
        if($this->is_notification_enabled('payment_complete')) {
            $this->process_notification($order_id,'payment_complete','Payment Complete');
        }
    }

    public function wc_order_status_update($order_id,$order_action,$process){
        if($order_action == 'send_order_details_admin') {
            switch ($process) {
				case 'wc-processing':
                     if ($this->is_notification_enabled('order_processing')) {
                        $this->process_notification($order_id, 'order_processing', 'Order Processing');
                    }
                    break;
                case 'wc-pending':
                    if ($this->is_notification_enabled('order_pending')) {
                        $this->process_notification($order_id, 'order_pending', 'Order Pending');

                    }
                    break;
                case 'wc-on-hold':
                    if($this->is_notification_enabled('order_onhold')) {
                        $this->process_notification($order_id,'order_onhold','Order Onhold');
                    }
                    break;
                case 'wc-completed':
                    if($this->is_notification_enabled('order_completed')) {
                        $this->process_notification($order_id,'order_completed','Order Completed');
                    }
                    break;
                case 'wc-cancelled':
                    if($this->is_notification_enabled('order_cancelled')) {
                        $this->process_notification($order_id,'order_cancelled','Order Cancelled');
                    }
                    break;
                case 'wc-refunded':
                    if($this->is_notification_enabled('order_refunded')) {
                        $this->process_notification($order_id,'order_refunded','Order Refunded');
                    }
                    break;
                case 'wc-failed':
                    if($this->is_notification_enabled('order_failed')) {
                        $this->process_notification($order_id,'order_failed','Order Failed');
                    }
                    break;
                default:
                    if ($this->is_notification_enabled('order_processing')) {
                        $this->process_notification($order_id, 'order_processing', 'Order Processing');
                    }
            }
        }

    }



    /*END PROCESSES*/

    private function is_notification_enabled($process) {
        $enabled = false;
        if(get_option('wc_isms_'.$process.'_enable') == 'yes') {
            $enabled = true;
        }
        return $enabled;
    }

    private function process_notification($order_id,$process,$msg_type) {
        $order = new \WC_Order($order_id);
        $params = $this->get_notification_params($order,$process);
		$checkorder = 0;
        if($msg_type == 'Order Processing'){
		
			if ($params['type'] == 'all') {
				$dstno = $params['to'][0]['dstno'];
			}else {
				$dstno = $params['dstno'];
			}
            $checkorder = $this->isms_process->get_sent_message($order_id,$msg_type,$dstno);
       	}
		
        if ($checkorder < 1) {
            $result = $this->isms_process->send_notification($params);
            $json_result = json_decode($result);
			
            if(is_array($json_result)){
                $response_code = explode("=", $json_result[0]);
            } else {
                $response_code = explode("=", $json_result);
            }

            if ($response_code[0] == 2000) {
                if ($params['type'] == 'all') {
                    foreach ($params['to'] as $recipient) {
                        $save = array(
                            'orderid' => $order_id,
                            'msg_type' => $msg_type,
                            'to' =>  str_replace("+", '', $recipient['dstno']),
                            'message' => $recipient['msg']
                        );
                        $this->isms_process->save_message($save);
                    }
                } else {
                    $save = array(
                        'orderid' => $order_id,
                        'msg_type' => $msg_type,
                        'to' => str_replace("+", '', $params['dstno']),
                        'message' => $params['msg']
                    );
                    $this->isms_process->save_message($save);
                }

                return $response_code[0];

            } else {
                return $result;
            }
        }
    }

    public function resend_notification() {
        $process = $_POST['original_process'];
        if($_POST['original_process'] != $_POST['new_process']) {
            $process = $_POST['new_process'];
        }
        $this->wc_order_status_update($_POST['order_id'],$_POST['order_action'],$process);

        wp_send_json(true);
    }

    private function get_notification_params($order,$process){
        $params = array();

        if(get_option('wc_isms_'.$process.'_send_admin') == 'yes' && get_option('wc_isms_'.$process.'_send_customer') == 'no') {
            $admin = array('dstno' => $this->admin_options['phone'], 'msg' => $this->format_message($order,get_option('wc_isms_'.$process.'_admin')),'type' => 'admin');
            $params = array_merge($params,$admin);

        }else if(get_option('wc_isms_'.$process.'_send_customer') == 'yes' && get_option('wc_isms_'.$process.'_send_admin') == 'no') {
            $cust = array('dstno' => $order->get_billing_phone(),'msg' =>$this->format_message($order,get_option('wc_isms_'.$process.'_customer')) , 'type' => 'customer');
            $params = array_merge($params,$cust);

        }else {
            $send_all = array('to' => array(
                array('dstno' => $this->admin_options['phone'], 'msg' => $this->format_message($order,get_option('wc_isms_'.$process.'_admin')),'type' => 1),
                array('dstno' => $order->get_billing_phone(), 'msg' => $this->format_message($order,get_option('wc_isms_'.$process.'_customer')),'type' => 1)
            ),
                'type' => 'all');
            $params = array_merge($params,$send_all);
        }
        return $params;
    }

    private function format_message($order,$message) {
        $vars = ["ORDER_TOTAL","ORDER_CURRENCY","ORDER_ID","SITE_NAME"];
        $wc_vars  = [$order->get_total(),$order->get_currency(), $order->get_id(), get_bloginfo( 'name' )];

        return str_replace($vars,$wc_vars,$message);
    }

    private function isms_template($file,$data= null) {
        include(dirname(__FILE__) . '/'.$file.'.php');
    }



}



?>