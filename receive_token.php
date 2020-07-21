<?php
date_default_timezone_set('UTC');
$url = 'https://accounts.google.com/o/oauth2/token';
$self_url = 'https://' . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"];  
$script_path = '/www/wwwroot/........./mail2binary/';

// Change to your to gmail credentials
$client_id = ".......................q2fhdr.apps.googleusercontent.com";
$client_secret = "VM7s...............";

$SCOPE = "https://www.googleapis.com/auth/gmail.readonly";

$refresh_token = file_get_contents($script_path.'refresh_token.ini');
$code = file_get_contents($script_path.'gmail_code.ini');

if (!empty($_GET["code"])) {
	$code = $_GET["code"];
	file_put_contents($script_path.'gmail_code.ini', $code);
	echo $code;
	header("Location: ".$self_url);
	die();
} else {

if ($refresh_token) {
	
	$params = array(
        "client_id" => $client_id,
        "client_secret" => $client_secret,
        "refresh_token" => $refresh_token,
        "redirect_uri" => $self_url,
        "grant_type" => "refresh_token"
    );

    $ch = curl_init();
    curl_setopt($ch, constant("CURLOPT_" . 'URL'), $url);
    curl_setopt($ch, constant("CURLOPT_" . 'POST'), true);
    curl_setopt($ch, constant("CURLOPT_" . 'POSTFIELDS'), $params);
    curl_setopt($ch, constant("CURLOPT_" . 'RETURNTRANSFER'), true);
    $output = curl_exec($ch);

    $secret_token = json_decode($output, true);
    
	
	if ($secret_token['access_token']) {
		echo date('H:i:s d-m-Y').' - token received [<b>success</b>]';
		file_put_contents($script_path.'token.ini', $secret_token['access_token']);
	}
	
    curl_close($ch);
	
	
} else {

echo '<table align="center" style="margin-top:150px;"><tr><td><h2>Token expired</h2></td></tr>
<tr><td><a href="'.$self_url.'?refresh">Receive token Gmail</a></td></tr></table>';


if (isset($_GET["refresh"])) {	
	
	$params = array(
        "client_id" => $client_id,
        "code" => $code,
        "access_type" => "offline",
        "client_secret" => $client_secret,
        "redirect_uri" => $self_url,
        "grant_type" => "authorization_code"
    );

    $ch = curl_init();
    curl_setopt($ch, constant("CURLOPT_" . 'URL'), $url);
    curl_setopt($ch, constant("CURLOPT_" . 'POST'), true);
    curl_setopt($ch, constant("CURLOPT_" . 'POSTFIELDS'), $params);
    curl_setopt($ch, constant("CURLOPT_" . 'RETURNTRANSFER'), true);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
   
	
   
    $result = json_decode($output, true);
    
    if ($result['error']) {
    	file_put_contents($script_path.'gmail_code.ini', "");
    	header("Location: https://accounts.google.com/o/oauth2/auth?scope=".$SCOPE."&access_type=offline&response_type=code&redirect_uri=https://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"]."?refresh&client_id=".$client_id);
		die();
	} else {
		echo '<br>'.$output;
		file_put_contents($script_path.'token.ini', $result['access_token']);
		file_put_contents($script_path.'refresh_token.ini', $result['refresh_token']);
	}
    
    
    curl_close($ch);
}    

}

}
