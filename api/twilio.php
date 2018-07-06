<?php
// Get the PHP helper library from https://twilio.com/docs/libraries/php
require_once './vendor/autoload.php'; // Loads the library

use Twilio\Rest\Client;

// Your Account Sid and Auth Token from twilio.com/user/account
$sid = "AC25783c9dd77da185006426f8e80f4a5c";
$token = "18880dea640c5bcdcdba0ffc3adc0e18";

$client = new Client($sid, $token);

$country = $client->pricing->messaging->countries("AR")->fetch();

echo "<pre>";
print_r($country);

foreach ($country->inboundSmsPrices as $p) {
    echo "{$p["number_type"]} {$p["current_price"]}\n";
}

foreach ($country->outboundSmsPrices as $p) {
    echo "{$p["number_type"]} {$p["current_price"]}\n";
}