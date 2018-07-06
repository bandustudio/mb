<?php 

use Exception\NotFoundException;
use Exception\ForbiddenException;
use Exception\PreconditionFailedException;
use Exception\PreconditionRequiredException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use Tuupola\Base62;
use App\Lead;
use App\User;
use App\Email;
use App\Brand;
use App\Model;
use App\Version;
use App\Message;
use App\UserMessage;

$app->group('/v1', function() {

    $this->post("/clientes/notif", function ($request, $response, $arguments) {

        if (false === $this->token->decoded->uid) {
            throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
        }

        // if (false === $this->token->decoded->uid) {

        $data = [];
        $then = new \Datetime("now - 2 years");
        $mapper = $this->spot->mapper("App\UserMessage")
            ->where(['recipient_id' => $this->token->decoded->uid])
            ->where(['created >' => $then->format('Y-m-d H:i:s')])
            ->where(['hasread' => 0])
            ->limit(1000);

        foreach($mapper as $item){
            if( ! isset($data[$item->tag])){
                $data[$item->tag] = [];
            }
            $data[$item->tag][] = $item;
        }

        return $response->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data));
    });

    $this->post("/gestion/notif", function ($request, $response, $arguments) {

        if (false === $this->token->decoded->uid) {
            throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
        }

        $data = [];
        $then = new \Datetime("now - 2 years");
        $mapper = $this->spot->mapper("App\Lead")
            ->where(['gestor_id' => $this->token->decoded->uid])
            ->where(['created >' => $then->format('Y-m-d H:i:s')])
            ->where(['status' => 'solicitado'])
            ->limit(1000);

        foreach($mapper as $item){
            if( ! isset($data[$item->tag])){
                $data[$item->tag] = [];
            }
            $data[$item->tag][] = $item;
        }

        return $response->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data));
    });
});