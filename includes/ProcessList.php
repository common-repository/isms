<?php
namespace wc_isms\includes;
$isms = new iSMS();
?>
<div id="isms">
    <h1>
        iSMS Notifications
    </h1><div class="isms-divider"></div>
    <style>
        button.button-primary.woocommerce-save-button { display: none; }
    </style>
    <table class="table table-bordered">
        <tr>
            <th>Process</th>
            <th>Recipient(s)</th>
            <th>Enabled</th>
            <th></th>
        </tr>

        <tr>
            <td>Order Processing</td>
            <?php echo $isms->proc_list_td('order_processing','order-processing'); ?>
        </tr>
        <tr>
            <td>Order Completed</td>
            <?php echo $isms->proc_list_td('order_completed','order-completed'); ?>
        </tr>
        <tr>
            <td>Payment Complete</td>
            <?php echo $isms->proc_list_td('payment_complete','payment-complete'); ?>
        </tr>
        <tr>
            <td>Order Failed</td>
            <?php echo $isms->proc_list_td('order_failed','order-failed'); ?>
        </tr>
        <tr>
            <td>Order Cancelled</td>
            <?php echo $isms->proc_list_td('order_cancelled','order-cancelled'); ?>
        </tr>
        <tr>
            <td>Order Refunded</td>
            <?php echo $isms->proc_list_td('order_refunded','order-refunded'); ?>
        </tr>
        <tr>
            <td>Order Pending</td>
            <?php echo $isms->proc_list_td('order_pending','order-pending'); ?>
        </tr>

    </table>
</div>