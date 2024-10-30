<div id="manual-send-form">
    <h4>Send to <?php echo $data->get_billing_phone(); ?></h4>
    <input type="hidden" id="dst" name="dst" value="<?php echo $data->get_billing_phone(); ?>">
    <input type="hidden" id="order_id" name="order_id" value="<?php  echo $data->get_id(); ?>">
    <div><textarea id="msg" name="msg" rows="5"></textarea></div>
    <div><button type="submit" class="button button-primary" id="send-sms" name="send-sms">Send SMS</button></div>
    <div class="isms-response-holder isms-hidden"></div>
    <div id="text-replacement-holder">
        <h3>SMS text replacement</h3>
        <p>SITE_NAME: Your site name</p>
        <p>ORDER_TOTAL: Total order price</p>
        <p>ORDER_CURRENCY: Order currency</p>
        <p>ORDER_ID: Order ID</p>
    </div>
</div>