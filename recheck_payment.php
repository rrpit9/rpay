<?php 
global $order_id;
global $pid;
global $txn_number;
global $key_id;
global $key_secret;

$pid =$_REQUEST['pid'];
$cust_id = str_replace('77A','',$pid);
$order_id = $_REQUEST['order_id'];
$txn_number =$_REQUEST['txn_number'];
$mtype = $_REQUEST['mtype'];
$chargeable_rate =trim($_REQUEST['chargeable_rate']);
$currency = $_REQUEST['currency'];


$key_id ='rzp_live_8DnU7blbZYLO0Y';
$key_secret ='JZ2pF5809AM01iuBDqusOXVo';
$merchant_name ='Winds E Pvt. Ltd';

/*==log== */
createLogFile($order_id,'Process for recheck-'.$key_id,json_encode($_REQUEST));

//echo $key_id.' => '.$key_secret.' => '.$merchant_name.'<br>';
$action = $_REQUEST['action'];

if($action=='recheck_txn'){
	
	if($txn_number!=''){
		
		$URL = "https://api.razorpay.com/v1/payments/".$txn_number;
		$headers = array(
		   "Accept: application/json",
		   "Authorization: Basic ". base64_encode("$key_id:$key_secret")
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($ch);
		curl_close ($ch);
		
		createLogFile($order_id,'Recheck Response',$response);
		
		$responseArr =json_decode($response,true);
		$pay_amount =trim($responseArr['amount']);
		$pay_currecy =$responseArr['currency'];
		
		$chargeable_rate =trim($chargeable_rate*100);
		
		capturePayment();
		
		if( $pay_amount < $chargeable_rate ){
			$data =array('ErrorCode'=>7702,'ErrorMessage'=>'Technical Issue, Mismatched Price');
			$response =json_encode($data);
			createLogFile($order_id,'Price Not Matched',$response);
		}
	}
	else{
		$data =array('ErrorCode'=>7701,'ErrorMessage'=>'Order Id or Txn Not Matched');
		$response = json_encode($data);
		createLogFile($order_id,json_encode($_REQUEST),$response);
	}
	echo $response;
	
	die;
}
function capturePayment()
{
	global $order_id;
	global $pid;
	global $txn_number;
	global $key_id;
	global $key_secret;
	global $chargeable_rate;
	global $currency;
	
	$data = array(
    'amount' => $chargeable_rate,
    'currency' => $currency,
);

	$success = true;
	$error = '';
	try {
		$ch = get_curl_handle($txn_number, $data,$key_id,$key_secret);
		$result = curl_exec($ch);
		$data = json_decode($result);
	    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		
		createLogFile($order_id,'Captured Response',$result);
		
		
	 if ($result === false) {
			$success = false;
			$error = 'Curl error: ' . curl_error($ch);
		} else {
			$response_array = json_decode($result, true);
			//Check success response
			if ($http_status === 200 and isset($response_array['error']) === false) {
				
			} else {
				$success = false;
				if (!empty($response_array['error']['code'])) {
					$error = $response_array['error']['code'] . ':' . $response_array['error']['description'];
				} else {
					$error = 'Invalid Response <br/>' . $result;
				}
			}
		}
		//close connection
		curl_close($ch);
	} catch (Exception $e) {
		$success = false;
		$error = 'Request to Razorpay Failed';
	}
}

function get_curl_handle($payment_id, $data,$RAZOR_KEY_ID,$RAZOR_KEY_SECRET) {
    $url = 'https://api.razorpay.com/v1/payments/' . $payment_id . '/capture';
    $key_id = $RAZOR_KEY_ID;
    $key_secret = $RAZOR_KEY_SECRET;
    $params = http_build_query($data);
    //cURL Request
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $key_id . ':' . $key_secret);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    return $ch;
}

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
?>