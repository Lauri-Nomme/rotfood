#!/usr/bin/php -q
<?php
require(__DIR__ . '/config.php');
require(__DIR__ . '/parse.php');
$email = file_get_contents('php://stdin');

function debug($msg) {
	file_put_contents('/tmp/rotfood.txt', date(DATE_ATOM) . ' ' . $msg . "\n", FILE_APPEND);
}

debug("-------------------------");
debug($email);

$payload = compose($email, '#rotfoodtest');

debug(json_encode($payload));

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL,"https://slack.com/api/chat.postMessage");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
	'Authorization: Bearer ' . $token,
	'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);
debug($server_output);
?>
