<?php
header("X-Frame-Options: allow-from https://www.abengines.com/");
$pid =$_REQUEST['pid'];
$order_id = $_REQUEST['order_id'];
$mtype = $_REQUEST['mtype'];
$txn_number = $_REQUEST['txn_number'];

$URL ='https://www.abengines.com/wp-content/plugins/adivaha/adh-integrations/common-payment-validation-v1.php?action=manage_order_txn&pid='.$pid.'&order_id='.$order_id.'&mtype='.$mtype.'&txn_number='.$txn_number;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$contents = curl_exec($ch);
echo $contents;
?>
