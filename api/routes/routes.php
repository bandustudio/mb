<?php 

require __DIR__ . "/functions.php";

use Exception\NotFoundException;
use Exception\ForbiddenException;
use Exception\PreconditionFailedException;
use Exception\PreconditionRequiredException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use Tuupola\Base62;
use App\Email;

$app->get('/', function ($request, $response, $args) {
    print "<h1>MercedesBenz.com API</h1>";
});

$app->get("/m/{code}", function ($request, $response, $arguments) {

    $mapper = $this->spot->mapper("App\Email")->first([
        "code" => $request->getAttribute('code')
    ]);

    if( ! $mapper){
        throw new NotFoundException("No se encontró el email", 404);        
    }

    header('Content-Type: text/html; charset=utf-8');
    print $mapper->content;
    exit;
});

require __DIR__ . "/app.php";
require __DIR__ . "/auth.php";
require __DIR__ . "/clientes.php";
require __DIR__ . "/upload.php";
require __DIR__ . "/gestion.php";
require __DIR__ . "/notif.php";