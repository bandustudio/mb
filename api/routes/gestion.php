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
use App\History;
use App\UserMessage;

$app->group('/v1', function() {

    $this->group('/gestion', function() {

        $this->post("/lead", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
            }

            if ($this->token->decoded->rid < 2) {
                throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
            }

            $body = $request->getParsedBody();

            if($body['id']){
                $mapper = $this->spot->mapper("App\Lead")
                    ->first(['id' => $body['id']]);
            } else {
                $mapper = new Payment($body);
            }

            $body['enabled'] = $body['enabled']?:0;
            
            $mapper->data($body);
            $this->spot->mapper("App\Lead")->save($mapper);
            
            \reference([
                "user_id" => $this->token->decoded->uid, 
                "record_id" => $body['id'], 
                "entity" => "App\Lead",
                "payload" => $body
            ],$request);

            $data['status'] = 'success';

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });


        $this->post("/dash", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
            }

            if ($this->token->decoded->rid < 2) {
                throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
            }

            $mapper = $this->spot->mapper("App\Lead")
                ->where([
                    'gestor_id' => $this->token->decoded->uid,
                    'complete' => 1
                ])
                ->order(['created' => "ASC"]);

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Lead);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));                
        });

        $this->group('/leads', function() {   

            $this->post("/gestores", function ($request, $response, $arguments) {

                if (false === $this->token->decoded->uid) {
                    throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
                }

                if ($this->token->decoded->rid < 2) {
                    throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
                }

                $mapper = $this->spot->mapper("App\User")
                    ->where([
                        'id <>' => $this->token->decoded->uid,
                        'role_id' => 2
                    ])
                    ->order(['last_activity' => "ASC"]);
                $data = [];
                foreach($mapper as $map){
                    $data[$map->id] = $map->first_name;
                }

                return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data));                
            });

            $this->post("/estados", function ($request, $response, $arguments) {

                if (false === $this->token->decoded->uid) {
                    throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
                }

                if ($this->token->decoded->rid < 2) {
                    throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
                }

                $data = [
                    'vigente' => 'Vigente',
                    'vigentedeuda' => 'Vigente con Deuda',
                    'solicitado' => 'Solicitado'
                ];

                return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data));                
            });

            $this->post("/gestor", function ($request, $response, $arguments) {

                if (false === $this->token->decoded->uid) {
                    throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
                }

                if ($this->token->decoded->rid < 2) {
                    throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
                }

                $body = $request->getParsedBody();

                $lead = $this->spot->mapper("App\Lead")
                    ->first(['id' => $body['id']]);

                $data['status'] = 'error';

                if($lead){
                    $data['status'] = 'success';

                    \send_message(1,$body['uid'],$body['id'],"Fuiste reasignado para el lead {$lead->code}");

                    $lead->data(['gestor_id' => $body['uid']]);
                    $this->spot->mapper("App\Lead")->save($lead);
                }

                return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data));                
            });

            $this->post("/estado", function ($request, $response, $arguments) {

                if (false === $this->token->decoded->uid) {
                    throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
                }

                if ($this->token->decoded->rid < 2) {
                    throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
                }

                $body = $request->getParsedBody();

                $lead = $this->spot->mapper("App\Lead")
                    ->first(['id' => $body['id']]);

                $data['status'] = 'error';

                if($lead){
                    $data['status'] = 'success';

                    //\send_message(1,$body['uid'],$body['id'],"Fuiste reasignado para el lead {$lead->code}");

                    $lead->data(['status' => $body['status']]);
                    $this->spot->mapper("App\Lead")->save($lead);
                }

                return $response->withStatus(200)
                    ->withHeader("Content-Type", "application/json")
                    ->write(json_encode($data));                
            });            
        });

        $this->post("/cotizaciones", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
            }

            if ($this->token->decoded->rid < 2) {
                throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
            }

            $mapper = $this->spot->mapper("App\Lead")
                ->where([
                    'deleted' => 0,
                    'gestor_id' => $this->token->decoded->uid,
                    'status' => ['solicitado']
                ]);

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Lead);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->post("/cotizacion/enviar/{id}", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
            }

            if ($this->token->decoded->rid < 2) {
                throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
            }

            $data = $request->getParsedBody();
            $id = $request->getAttribute('id');
            $values=[];
            $entries = [];

            $this->spot->mapper("App\Plan")->where(['lead_id' => $id])
                ->delete();

            foreach($data as $i => $prop){
                foreach($prop as $j => $value){
                    if(!is_array( $entries[$j] )) {
                        $entries[$j] = [];
                    }
                    $entries[$j][$i] = $value;
                }
            }

            foreach($entries as $i => $entry){
                $data = $entry;
                $data["lead_id"] = $id;
                $data["enabled"] = 1;
                $plan = new Plan($data);

                $this->spot->mapper("App\Plan")->save($plan);

                \reference([
                    "user_id" => $this->token->decoded->uid, 
                    "record_id" => $id, 
                    "payload" => $json,
                    "entity" => "App\Plan"
                ],$request);
            }

            $data['status'] = 'success';

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));  
        });    

        $this->post("/polizas", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
            }

            if ($this->token->decoded->rid < 2) {
                throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
            }

            $mapper = $this->spot->mapper("App\Lead")
                ->where([
                    'gestor_id' => $this->token->decoded->uid,
                    'status' => ['vigente','vigentedeuda','solicitado'],
                    'deleted' => 0
                ])
                ->order(['created' => "DESC"]);

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Lead);
            $data = $fractal->createData($resource)->toArray();

            foreach($data['data'] as $i => $poliza){
                $mapper = $this->spot->mapper("App\User")
                    ->first(['id' => $poliza['user']['id']]);
                if($mapper){
                    $data['data'][$i]['cc'] = [
                        'cc_number' => $mapper->cc_number,
                        'cc_exp_date' => $mapper->cc_exp_date,
                        'cc_entity' => $mapper->cc_entity,
                        'cc_name' => $mapper->cc_name
                    ];
                }
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));    
        });

        $this->group('/polizas', function() {   
            $this->post("/solicitar", function ($request, $response, $arguments) {
                
            });
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

        // cotizacion sugerir coberturas y precios!

        $this->group('/sugest', function() {      

            $this->group('/lead', function() {

                $this->post("/name", function ($request, $response, $arguments) {

                    if (false === $this->token->decoded->uid) {
                        throw new ForbiddenException("Solo usuarios registrados pueden operar.", 403);
                    }

                    if ($this->token->decoded->rid < 2) {
                        throw new ForbiddenException("Solo usuarios administradores pueden operar.", 403);
                    }

                    $body = $request->getParsedBody();
                    $value = $body['value'];
                    $mapper = $this->spot->mapper("App\Plan")
                        ->query("SELECT name FROM plans WHERE name LIKE '%{$value}%' ORDER BY name LIMIT 10");

                    $fractal = new Manager();
                    $fractal->setSerializer(new DataArraySerializer);
                    $resource = new Collection($mapper, new Plan);
                    $data = $fractal->createData($resource)->toArray();

                    return $response->withStatus(200)
                        ->withHeader("Content-Type", "application/json")
                        ->write(json_encode($data));    
                });
            });
        });
    });

    $this->post("/token", function ($request, $response, $arguments) {

        $data = ['status' => "error"];
        $now = new DateTime("now");
        if ($this->token->decoded->uid && $this->token->decoded->exp >= $now->format("U")) {
            $user = $this->spot->mapper("App\User")->first([
                "id" => $this->token->decoded->uid
            ]);

            $data = [];

            if ($user) {
                $fractal = new Manager();
                $fractal->setSerializer(new DataArraySerializer);
                $resource = new Item($user, new User);
                $data = $fractal->createData($resource)->toArray();
            }
        }

        return $response->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data));
    });

    $this->post("/ecmalog", function($request, $response, $arguments){
        $body = $request->getParsedBody();
        $line = date('H:i:s') . ' - ' . trim($body['line']);
        \log2file( __DIR__ . "/../logs/ecma-" . date('Y-m-d') . ".log",$line); 

        return $response->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode([]));      
    });

    $this->get("/test-email/{id}", function ($request, $response, $arguments) {

        $user = $this->spot->mapper("App\User")->first([
            "id" => $request->getAttribute('id')
        ]);

        $data["status"] = "error";
        $data["message"] = "Correo de prueba no enviado, no se encontro el usuario con id " . $request->getAttribute('id');

        if( $user ){
            $body['readable_password'] = "whatever";
            $body['email_encoded'] = "whatever";
            $sent = \send_email("Bienvenido!",$user,'welcome.html',$body,2);

            if($sent['status']=='success'){
                $data["message"] = "Correo de prueba enviado";
            } else {
                $data["status"] = "error";
                $data["message"] = "Correo de prueba no enviado";
            }
        } 
        
        return $response->withStatus(200)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode($data));
    });

    $this->get("/validar/{encoded}", function ($request, $response, $arguments) {

        $emailorphone = "email";
        $decoded = Base62::decode($request->getAttribute('encoded'));

        if( strpos( $decoded, '@' ) === false ){
            $emailorphone = "phone";
        }

        $user = $this->spot->mapper("App\User")->first([
            $emailorphone => $decoded
        ]);

        if( ! $user){
            $data["status"] = "error";
            $data["message"] = "No se encontró el usuario";
        } else {

            $body = $user->data(['validated' => 1]);
            $this->spot->mapper("App\User")->save($body);

            $data["status"] = "success";
            $data["message"] = "Tu cuenta ha sido validada";
        }

        $view = new \Slim\Views\Twig('templates', [
            'cache' => false
        ]);

        $params = $request->getQueryParams();
        $data["redirect"] = getenv('CLIENTES_URL');

        if( ! empty($params['redirect'])){
            $data["redirect"] = getenv('CLIENTES_URL') . $params['redirect'];
        }

        $fractal = new Manager();
        $fractal->setSerializer(new DataArraySerializer);
        $resource = new Item($user, new User);
        $data = $fractal->createData($resource)->toArray();

        echo \login_redirect($data['data']);
        exit;
    });


});