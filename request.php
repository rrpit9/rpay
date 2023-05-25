<?php
ob_start();
echo '<div class="adiFullp animated-background-lodear"><div class="adi_wrapper"><p class="adi_please_wait">Please Wait!</p><p class="adi_please_wait1"></p><div class="spinnerDots"><div class="dots1"></div><div class="dots2"></div><div class="dots3"></div></div><p></p><p class="adi_please_wait2">Your booking is in progress.</p><p class="adi_please_wait3">Please do not press refresh or back button.</p></div></div><style>
body{margin:0px;font-family: sans-serif}.animated-background-lodear{    display: flex;justify-content: center;text-align: center;height:100%;align-items: center;}.adi_wrapper{} .adi_please_wait2{font-size:15px;margin:0;font-weight:700;color:#000;line-height: 1.6;}.adi_please_wait{font-size:26px;font-weight:700;color:#000;padding: 0px;margin: 0px 0px 10px;text-shadow:-15px 5px 20px #ced0d3;line-height: 1.6;}.spinnerDots{width:70px;margin:0 auto} .spinnerDots>div{background-color:rgb(185 183 183);border-radius:100%;display:inline-block;width:14px;height:14px;-webkit-animation:1.4s ease-in-out infinite both rk-bouncedelay;animation:1.4s ease-in-out infinite both rk-bouncedelay}.adi_please_wait3{font-size:17px;margin:0;color:#f44336;font-weight:700}.spinnerDots .dots1{-webkit-animation-delay:-.32s;animation-delay:-.32s}.spinnerDots .dots2{-webkit-animation-delay:- .16s;animation-delay:- .16s}@-webkit-keyframes rk-bouncedelay{0%,100%,80%{-webkit-transform:scale(0)}40%{-webkit-transform:scale(1)}}@keyframes rk-bouncedelay{0%,100%,80%{-webkit-transform:scale(0);transform:scale(0)}40%{-webkit-transform:scale(1);transform:scale(1)}}</style>';
ob_flush();

$key_id ='rzp_live_8DnU7blbZYLO0Y';
$key_secret ='JZ2pF5809AM01iuBDqusOXVo';
$merchant_name ='Winds E Pvt. Ltd';
$rzp_registered_url='https://windsapp.com/rpay';



$pid =$_REQUEST['pid'];
$moduleName =$_REQUEST['moduleName'];
$mtype =$_REQUEST['mtype'];

$chargeableAmount =adh_decrypt($_REQUEST['orderAmount']);
$order_id =$_REQUEST['order_id'];
$orderCurrency =$_REQUEST['orderCurrency'];
$customerName =$_REQUEST['customerName'];
$email_id =$_REQUEST['customerEmail'];
$homePhone =$_REQUEST['customerPhone'];
$address =$_REQUEST['customerAddress'];
$countryCode =$_REQUEST['customerCcod'];
$client_website =$_REQUEST['client_website'];


$redirect =$rzp_registered_url.'/Razorpay/payment_response.php?action=manage_order_txn&pid='.$pid.'&order_id='.$order_id.'&mtype='.$mtype;

$module_redirect ='https://www.abengines.com/wp-content/plugins/adivaha/apps/modules/'.$moduleName.'/payment-notification.php?pid='.$pid.'&order_id='.$order_id;


/*==log== */
createLogFile($order_id,'Payment Request Data-Key:'.$key_id,json_encode($_REQUEST));

function createLogFile($order_id,$request,$response){
	$log_filename = "/var/www/whitelabelB2B/Razorpay/LogFile";
	  
    if (!file_exists($log_filename)) {  
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/Payment-RQ-RS-'.$order_id.'.txt';
	
	$log_data ="===========Request========="."\n";
	$log_data.=$request."\n";
	$log_data.="===========Response========="."\n";
	$log_data.=$response."\n";
	
    file_put_contents($log_file_data, $log_data . "\n", FILE_APPEND);
}

function adh_decrypt($string){
$secret_key = 'notonbaba'; 
$secret_iv  = 'notonbaba_iv';
$output     = FALSE;
$encrypt_method = "AES-256-CBC";
$key = hash('sha256', $secret_key);
$iv = substr(hash('sha256', $secret_iv), 0, 16);
$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
return $output;	
}

?>

<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script src="//checkout.razorpay.com/v1/checkout.js"></script>
<script>
	var options = {
		"key": "<?php echo $key_id; ?>",
		"amount": "<?php echo $chargeableAmount; ?>",
		"currency": "<?php echo $orderCurrency; ?>",
		"name": "<?php echo $merchant_name; ?>",
		"description": "Payment",
		"image": "",
		"handler": function(response) {
			if (response.razorpay_payment_id != '') {
				var txn_number = response.razorpay_payment_id;
				$.ajax({
					url: '<?php echo $redirect; ?>&txn_number=' + txn_number,
					type: "POST",
					success: function(response) {
						var pchecksum = response;
						var module_redirect = "<?php echo $module_redirect; ?>";
						var redirectUrl = module_redirect + '&txn_number=' + txn_number + '&pchecksum=' + pchecksum;
                        //console.log("redirectUrl:"+redirectUrl);
						window.location.href = redirectUrl;
					}
				});

			} else {
				alert('Payment failed');
			}
		},
		"prefill": {
			"name": "<?php echo $customerName; ?>",
			"email": "<?php echo $email_id; ?>",
			"contact": "<?php echo $homePhone; ?>"
		},
		"notes": {
			"address": "<?php echo $address . ' ' . $countryCode; ?>"
		},
		"theme": {
			"color": ""
		},
		"modal": {
			escape: false,
			ondismiss: function() {

				window.top.location.href = "<?php echo $client_website; ?>";
			}
		}
	};
	var rzp1 = new Razorpay(options);
	rzp1.open();
</script>

<script>
	setInterval(function() {
		resize();
	}, 5);

	function resize() {
		var height = 800;
		window.parent.postMessage(["setHeight", height, "Razorpay"], "*");
	}
</script>