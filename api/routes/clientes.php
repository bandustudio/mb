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
use App\Plan;
use App\User;
use App\Email;
use App\Brand;
use App\Model;
use App\Version;
use App\Message;
use App\UserMessage;

$app->group('/v1', function() {

    $this->group('/clientes', function() {

        /* cotizaciones */
        $this->post("/cotizaciones", function ($request, $response, $arguments) {
            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }
            
            $filter = [
                'user_id' => $this->token->decoded->uid,
                'complete' => 1,
                'status' => ['iniciado','solicitado','cotizado','cancelado'],
                //'product_since' => 'IS NULL',
                'deleted' => 0
            ];

            $order = [
                'updated' => "DESC"
            ];

            $mapper = $this->spot->mapper("App\Lead")->all()
                ->where($filter)
                ->order($order);

            $leads_ids = [];
            foreach($mapper as $item){
                $leads_ids[]= $item->id;
            }

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Lead);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/cotizaciones/{code}", function ($request, $response, $arguments) {
            
            $data = [];
            $code = $request->getAttribute('code');

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            // detect lead
            $lead = $this->spot->mapper("App\Lead")
                ->first(['code' => $code]);

            if($lead AND !$lead->deleted){

                $fractal = new Manager();
                $fractal->setSerializer(new DataArraySerializer);
                $resource = new Item($lead, new Lead);
                $data = $fractal->createData($resource)->toArray();
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        /* polizas */

        $this->post("/polizas", function ($request, $response, $arguments) {
            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }
            
            $filter = [
                'user_id' => $this->token->decoded->uid,
                'complete' => 1,
                'status' => ['vigente','vigentedeuda'],
                'deleted' => 0
            ];

            $order = [
                'updated' => "DESC"
            ];

            $mapper = $this->spot->mapper("App\Lead")->all()
                ->where($filter)
                ->order($order);

            $leads_ids = [];
            foreach($mapper as $item){
                $leads_ids[]= $item->id;
            }

            $notifications = $this->spot->mapper("App\UserMessage")
                ->where(['lead_id' => $leads_ids])
                ->where(['recipient_id' => $this->token->decoded->uid])
                ->where(['tag' => 'notificacion'])
                ->where(['hasread' => 0]);

            foreach($notifications as $item){
                $item->data(['hasread' => 1]);
                $this->spot->mapper("App\UserMessage")->save($item);
            }

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Lead);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/polizas/solicitar/{id}", function ($request, $response, $arguments) {
            
            $id = $request->getAttribute('id');
            $data['status'] = 'error';

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $plan = $this->spot->mapper("App\Plan")
                ->first(['id' => $id]);

            if($plan){

                $lead = $this->spot->mapper("App\Lead")
                    ->first([
                        'user_id' => $this->token->decoded->uid,
                        'id' => $plan->lead_id
                    ]);

                if($lead){
                    $data['status'] = 'success';
                    $body = [
                        'request_sent' => new \DateTime('now'),
                        'plan_id' => $plan->id,
                        'status' => "solicitado"
                    ];

                    // notification both
                    \send_message(1,$lead->user_id,$lead->id,"Se solicitó cobertura para {$lead->brand} {$lead->model} $lead->mt_year (#{$lead->code})");
                    \send_message(1,$lead->gestor_id,$lead->id,"Se solicitó cobertura para {$lead->brand} {$lead->model} $lead->mt_year (#{$lead->code})");

                    $lead->data($body);
                    $this->spot->mapper("App\Lead")->save($lead);
                }
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/polizas/descargar/{id}", function ($request, $response, $arguments) {
            
            $id = $request->getAttribute('id');
            $data['status'] = 'error';

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $plan = $this->spot->mapper("App\Plan")
                ->first(['id' => $id]);

            if($plan){

                $lead = $this->spot->mapper("App\Lead")
                    ->first([
                        'user_id' => $this->token->decoded->uid,
                        'id' => $plan->lead_id
                    ]);

                if($lead){
                    $data['status'] = 'success';
                    $body = [
                        'request_sent' => null,
                        'plan_id' => null,
                        'status' => "cancelado"
                    ];

                    //\send_message()
                    \send_message(1,$lead->gestor_id,$lead->id,"La solicitud #{$lead->code} fue cancelada");

                    $lead->data($body);
                    $this->spot->mapper("App\Lead")->save($lead);
                }
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/polizas/cancelar/{id}", function ($request, $response, $arguments) {
            
            $id = $request->getAttribute('id');
            $data['status'] = 'error';

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $plan = $this->spot->mapper("App\Plan")
                ->first(['id' => $id]);

            if($plan){

                $lead = $this->spot->mapper("App\Lead")
                    ->first([
                        'user_id' => $this->token->decoded->uid,
                        'id' => $plan->lead_id
                    ]);

                if($lead){
                    $data['status'] = 'success';
                    $body = [
                        'request_sent' => null,
                        'plan_id' => null,
                        'status' => "cancelado"
                    ];

                    //\send_message()
                    \send_message(1,$lead->gestor_id,$lead->id,"La solicitud #{$lead->code} fue cancelada");

                    $lead->data($body);
                    $this->spot->mapper("App\Lead")->save($lead);
                }
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/polizas/{code}", function ($request, $response, $arguments) {
            
            $data = [];
            $code = $request->getAttribute('code');

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            // detect lead
            $lead = $this->spot->mapper("App\Lead")
                ->first(['code' => $code]);

            if($lead AND !$lead->deleted){

                $fractal = new Manager();
                $fractal->setSerializer(new DataArraySerializer);
                $resource = new Item($lead, new Lead);
                $data = $fractal->createData($resource)->toArray();
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });


        /* notificaciones */
        $this->post("/notificaciones", function ($request, $response, $arguments) {

            $params = $request->getQueryParams();

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }
            
            $unread = $this->spot->mapper("App\UserMessage")->all()
                ->where([
                    'recipient_id' => $this->token->decoded->uid,
                    'tag' => "notificacion",
                    'hasread' => 0
                ])
                ->order([
                    'created' => "DESC"
                ]);

            $where = [
                'recipient_id' => $this->token->decoded->uid,
                'tag' => "notificacion",
                'hasread' => 1
            ];

            if($params['up']){
                $where['created' < $params['up']];
            }

            $read = $this->spot->mapper("App\UserMessage")->all()
                ->where($where)
                ->order([
                    'created' => "DESC"
                ]);

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($unread, new UserMessage);
            $data['unread'] = $fractal->createData($resource)->toArray();

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($read, new UserMessage);
            $data['read'] = $fractal->createData($resource)->toArray();

            // mark unread as read
            foreach($unread as $item){
                $item->data(['hasread' => 1]);
                $this->spot->mapper("App\UserMessage")->save($item);
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/reclamos", function ($request, $response, $arguments) {

            // publicaciones
            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }
            
            $filter = [
                'recipient_id' => $this->token->decoded->uid,
                'tag' => "reclamo"
            ];

            $order = [
                'updated' => "DESC"
            ];

            $mapper = $this->spot->mapper("App\UserMessage")->all()
                ->where($filter)
                ->order($order);

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Message);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        }); 

        $this->post("/archivar/{id}", function ($request, $response, $arguments) {

            $lead = $this->spot->mapper("App\Lead")->first([
                "id" => $request->getAttribute('id')
            ]);

            if(empty($lead)){
                throw new ForbiddenException("No lead found.", 403);
            }

            $deleted = ['deleted' => 1];
            $lead->data($deleted);
            $this->spot->mapper("App\Lead")->save($lead);

            \reference([
                "user_id" => $this->token->decoded->uid, 
                "record_id" => $request->getAttribute('id'),
                "entity" => "App\Lead",
                "payload" => $deleted
            ],$request);

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode(['status' => $lead ? "success" : "error"]));
        });       

        $this->post('/cuenta', function ($request, $response, $args) {

            $body = $request->getParsedBody();
            $data = [];

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            if(empty($body)){
                throw new ForbiddenException("No parameters recieved.", 403);
            }

            $user = $this->spot->mapper("App\User")->first([
                "id" => $this->token->decoded->uid
            ]);   

            if(!$user) { 
                $data['status'] = "error";
            } else {
                $data['status'] = "success";
                $user->data($body);
                $this->spot->mapper("App\User")->save($user);

                \reference([
                    "user_id" => $this->token->decoded->uid, 
                    "record_id" => $user->id,
                    "entity" => "App\User",
                    "payload" => $body
                ],$request);

            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });


        $this->post('/billing', function ($request, $response, $args) {

            $body = $request->getParsedBody();
            $data = [];

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            if(empty($body)){
                throw new ForbiddenException("No parameters recieved.", 403);
            }

            $user = $this->spot->mapper("App\User")->first([
                "id" => $this->token->decoded->uid
            ]);   

            if(!$user) { 
                $data['status'] = "error";
            } else {
                $data['status'] = "success";
                $user->data($body);
                $this->spot->mapper("App\User")->save($user);

                \reference([
                    "user_id" => $this->token->decoded->uid, 
                    "record_id" => $user->id,
                    "entity" => "App\User",
                    "payload" => $body
                ],$request);
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });        
    });
});