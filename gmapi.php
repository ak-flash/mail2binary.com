<?php
date_default_timezone_set('UTC');

include_once('binary.php');

$api_secret = file_get_contents('token.ini');
$history_id = file_get_contents('historyId.ini');

// Name of message Label in gmailbox: to check only one inbox special folder.

$label_id = 'Label_000000000000000000';

function get_history($new_history_id) {

global $api_secret, $history_id;

if($history_id == '') $history_id = $new_history_id;

$headers = stream_context_create(array(
	'http' => array(
		'method' => 'GET',
		'header' => "authorization: Bearer ".$api_secret." Content-Length: 0"
	),
));
 
$response = file_get_contents('https://www.googleapis.com/gmail/v1/users/me/history?labelId='.$label_id.'&historyTypes=messageAdded&startHistoryId='.$history_id, false, $headers);

$result = json_decode($response,true);

if ($result['error']) {
	file_put_contents('refresh_token.ini', '');
}

return $result;

}


function get_message($message_id) {

global $api_secret, $history_id;

$headers = stream_context_create(array(
	'http' => array(
		'method' => 'GET',
		'header' => "authorization: Bearer ".$api_secret." Content-Length: 0"
	),
));
 
$response = file_get_contents("https://www.googleapis.com/gmail/v1/users/me/messages/".$message_id."?format=metadata", false, $headers);

$result = json_decode($response,true);

return $result;

}


$data = file_get_contents("php://input");


file_put_contents('webhook_log.txt',$data.PHP_EOL, FILE_APPEND | LOCK_EX);

$requestBody = json_decode($data, true);
	//$messageId = $requestBody['message']['messageId'];
	//$dataId = $requestBody['message']['data'];

$data_decoded = base64_decode($requestBody['message']['data']);
$data_array = json_decode($data_decoded, true);

$historyId = $data_array['historyId'];


$datas = get_history($historyId);

$new_historyId = $datas['historyId'];

file_put_contents('historyId.ini', $new_historyId);



if(isset($datas['history'])) {
	
	foreach ($datas['history'] as $value) {
		
		$message_full = get_message($value["messages"][0]["id"]);
		
		$subject = $message_full["payload"]["headers"][16]['value'];
	
		$time_signal = $message_full["payload"]["headers"][19]['value'];
		$timestamp_signal = strtotime($time_signal);

		$time_now = time();
		$difference = $time_now - $timestamp_signal;
	
		if (strpos($subject, 'BUY3')) $contract_type = 'PUT';
	    if (strpos($subject, 'SELL3')) $contract_type = 'CALL';
		
		// open order only if time between now and signal time differens less then 10 seconds
		    if (isset($contract_type)&&$difference < 10) {
		    	
		        $send = send_buy($contract_type);
		        file_put_contents('buy_logs.txt', date('d-m-Y H:i:s', strtotime($time_now)).' - '.$subject.' - '.date('d-m-Y H:i:s', $send).PHP_EOL, FILE_APPEND | LOCK_EX);
		        
		        if($send != "") {
		        	echo $time_now.' - success';
		        }
		        
			} else file_put_contents('buy_logs.txt', date('d-m-Y H:i:s', $time_now).' - '.$subject.' - '.date('d-m-Y H:i:s', $timestamp_signal).' - very late '.$difference.' s'.PHP_EOL, FILE_APPEND | LOCK_EX);
	
	}

}

?>