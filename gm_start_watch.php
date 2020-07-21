<?php

$topicName = 'projects/...you project name from GCP .../topics/gmail';


$api_secret = file_get_contents('token.ini');

$headers = stream_context_create(array(
	'http' => array(
		'method' => 'POST',
		'header' => "authorization: Bearer ".$api_secret." Content-Length: 0 ",
		'content' => 'topicName='.$topicName,
	),
));
 
echo file_get_contents('https://www.googleapis.com/gmail/v1/users/me/watch', false, $headers);

?>