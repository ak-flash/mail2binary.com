<?php
require __DIR__ . '/vendor/autoload.php';

// Settings:	CHANGE THIS!!!
$is_real=0;
//Real
$token_real = '1E...'; #read/trade
//Demo
$token_demo = 'T5...'; #read/trade

$app_id = 0000;

// Order properties
$amount = '3'; # stake in USD
$symbol = 'EURJPY';
$duration = 800; // expiration in seconds
$duration_unit = 's';



if($is_real==1) $token=$token_real; else $token=$token_demo;


$client = new WebSocket\Client("wss://ws.binaryws.com/websockets/v3?app_id=".$app_id);

function send_buy($contract_type)
{
    global $token, $client, $amount, $duration, $duration_unit, $symbol;

    try {
        $message = '';

        $client->send('{"authorize":"' . $token . '"}');
        $message = $client->receive();


        $client->send('{"buy": "1","price": '.$amount.',"parameters": {"amount": '.$amount.',"basis": "stake","contract_type": "'.$contract_type.'","currency": "USD","duration": '.$duration.',"duration_unit": "'.$duration_unit.'","symbol": "frx'.$symbol.'"}}');
        $message = $client->receive();

        $result=json_decode($message, true);

        if(isset($result['buy']['contract_id'])) {
           
            return $result['buy']['purchase_time'];

        }

        //var_dump($result);
        //$result['buy']['purchase_time']
        //$result['buy']['transaction_id']

    } catch (\WebSocket\ConnectionException $e) {
        file_put_contents('errors.txt', 'Order - '.date('d-m-Y H:i:s').' - '.$e.PHP_EOL, FILE_APPEND | LOCK_EX);
    } catch (\WebSocket\BadOpcodeException $e) {
        file_put_contents('errors.txt', 'Order - '.date('d-m-Y H:i:s').' - '.$e.PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    $client->close();

}

function balance($type)
{
    global $token_real, $token_demo, $client;

    if ($type=='real') $token = $token_real;
    if ($type=='demo') $token = $token_demo;

    try {
		$message = '';
		
        $client -> send('{"authorize":"' . $token . '"}');
        $message = $client->receive();

        $result = json_decode($message, true);
        #file_put_contents('logs.txt',$time.' - '.$subject);

        if(isset($result['authorize']['balance'])) {
            if($result['authorize']['is_virtual'] == '1') $type = 'Demo'; else $type = '<b>Real</b>';
            
            $balance = array ( 
            	'type' => $type,
            	'balance' => $result['authorize']['balance'],
            	'unit' => 'USD',
            );
            
            return json_encode($balance, true);
        }

    } catch (\WebSocket\ConnectionException $e) {
        file_put_contents('errors.txt', 'Balance - '.date('d-m-Y H:i:s').' - '.$e.PHP_EOL, FILE_APPEND | LOCK_EX);
    } catch (\WebSocket\BadOpcodeException $e) {
        file_put_contents('errors.txt', 'Balance - '.date('d-m-Y H:i:s').' - '.$e.PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    $client->close();

}

// Direct GET request
if (isset($_GET['subject'])&&isset($_GET['time'])) {

    $subject = $_GET['subject'];
    $time = $_GET['time'];


    if (strpos($subject, 'BUY3')) $contract_type = 'PUT';
    if (strpos($subject, 'SELL3')) $contract_type = 'CALL';

    if (isset($contract_type)) {
        $send=send_buy($contract_type);
        file_put_contents('logs.txt',$time.' - '.$subject.' - '.date('d-m-Y H:i:s', $send).PHP_EOL, FILE_APPEND | LOCK_EX);
    }


}

// Show BALANCE using GET request
if (isset($_GET['balance'])) {
    header('Content-type:application/json'); 
    echo balance(@$_GET['balance']);
}
?>