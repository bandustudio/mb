<?php 

session_start();

date_default_timezone_set("EST");

require __DIR__ . "/../vendor/autoload.php";

function subpic($id,$url){
    $parts = explode('/',$url);
    $name = $parts2[count($parts2)-1];
    $parts[count($parts)-1] = $id.$parts[count($parts)-1];
    return implode('/',$parts);
}

function words($str,$cut=10){
    $words=str_word_count($str,true);
    $a=array_slice($words,$cut);
    $s=join(' ',$a);
    return $s;    
}

$dotenv = new Dotenv\Dotenv(__DIR__ . '/..');
$dotenv->load();

$app = new \Slim\App([
    "settings" => [
        "displayErrorDetails" => true
    ]
]);

require __DIR__ . "/config/dependencies.php";
require __DIR__ . "/config/handlers.php";
require __DIR__ . "/config/middleware.php";
require __DIR__ . "/routes/routes.php";

$app->run();