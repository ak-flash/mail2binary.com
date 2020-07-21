<?php

function send_webhook()
{
	// change it
	$url = 'https://....../mail2binary/gmapi.php';
    
	//change to your test Webhook data. Use file: webhook_log.txt
	$data = '{"message":{"data":".....Z21haWwuY29tIiwiaGlzdG9yeUlkIjoxNzM0ODI1fQ==","messageId":"1364354737559262","message_id":"1364354737559262","publishTime":"2020-07-21T16:54:09.197Z","publish_time":"2020-07-21T16:54:09.197Z"},"subscription":"projects/......./subscriptions/signal"}';
	
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return curl_exec($curl);
}

send_webhook();

?>