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

$container = $app->getContainer();

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->offsetSet('params', $_REQUEST?:false);
    $view->offsetSet('app_title', getenv('APP_TITLE'));
    $view->offsetSet('app_url', getenv('APP_URL'));
    $view->offsetSet('api_url', getenv('API_URL'));
    $view->offsetSet('static_url', getenv('STATIC_URL'));
    $view->offsetSet('app_phone', getenv('APP_PHONE'));
    $view->offsetSet('app_whatsapp', getenv('APP_WHATSAPP'));
    $view->offsetSet('app_facebook', getenv('APP_FACEBOOK'));
    $view->offsetSet('rev_parse', substr(exec('git rev-parse HEAD'),0,7));
    $view->offsetSet('localhost', ($_SERVER['REMOTE_ADDR'] == "127.0.0.11"));
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->get('[{path:.*}]', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
});

$app->run();