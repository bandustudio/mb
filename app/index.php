<?php 

date_default_timezone_set("EST");

require __DIR__ . "/../vendor/autoload.php";

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