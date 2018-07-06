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

    $this->group('/app', function() {

        $this->post("/candidato", function ($request, $response, $arguments) {

            $mapper = $this->spot->mapper("App\User")
                ->where(['role_id' => 2])
                ->order(['last_activity' => 'ASC'])
                ->limit(1);

            $data = [
                'id' => $mapper[0]->id,
                'first_name' => $mapper[0]->first_name,
                'picture' => $mapper[0]->picture
            ];

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });

        $this->post("/contacto", function ($request, $response, $arguments) {

            $body = $request->getParsedBody();
            
            $user = (object) [
                'first_name' => "Administrador",
                'last_name' => "",
                'email' => getenv('MAIL_CONTACT')
            ];

            \send_email("Nueva Consulta desde la Web",$user,'contact.html',$body);

            $data["status"] = "success";
            
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));  

        });

        $this->post('/marcas', function ($request, $response, $args) {
            $mapper = $this->spot->mapper("App\Brand")
                ->where(['id >' => 0])
                ->order(['title' => 'ASC'])
                ->limit(1000);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Brand);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        $this->post('/versiones', function ($request, $response, $args) {

            $body = $request->getParsedBody();

            $models = $this->spot->mapper("App\Model")
                ->where(['brand_id' => $body['marca']])
                ->order(['title' => 'ASC'])
                ->limit(1000);

            $ids = [];

            foreach($models as $model){
                $ids[] = $model->id;
            }

            $ids = array_unique($ids);

            $mapper = $this->spot->mapper("App\Version")
                ->where(['model_id' => $ids])
                ->order(['title' => 'ASC'])
                ->limit(5000);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Version);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });  

        // Usuario crean cotizaciones, se actualizas usuarios y vehículos

        $this->post('/cotizacion', function ($request, $response, $args) {

            $body = $request->getParsedBody();
            $lead = null;

            if(empty($body)){
                throw new ForbiddenException("No parameters recieved.", 403);
            }

            // an existent lead?
            if(!empty($body['id'])){
                $id = $body['id'];
                $lead = $this->spot->mapper("App\Lead")->first(['id' => $id]);
            }

            if(!$lead){

                // create new lead
                $lead = new Lead($body);    
                $code = strtolower(Base62::encode(random_bytes(6)));

                while($this->spot->mapper("App\Lead")->first(["code" => $code])){
                    $code = strtolower(Base62::encode(random_bytes(6)));
                }

                $body['code'] = $code;
                $lead->data([
                    'code' => $code,
                    'status' => 'iniciado'
                ]);
                
                $id = $this->spot->mapper("App\Lead")->save($lead);
            }

            // register user if not exists, send him/her an email with his/her access data.
            if(!empty($body['email'])){

                $user = $this->spot->mapper("App\User")->first([
                    "email" => $body['email']
                ]);    
                
                if(!$user) { 

                    $password = strtolower(Base62::encode(random_bytes(16)));
                    $hash = sha1($password.getenv('APP_HASH_SALT'));
                    $user = new User([
                        "email" => $body["email"], 
                        "enabled" => 1,
                        "address" => !empty($lead->address) ? $lead->address : '',
                        "password" => $hash,
                        "username" => \set_username(!empty($lead->first_name) ? !empty($lead->first_name) . (!empty($lead->last_name) ? !empty($lead->first_name) : '') : "")
                    ]);

                    $this->spot->mapper("App\User")->save($user);
                    
                    $body['first_name'] = !empty($lead->first_name) ? $lead->first_name : "";
                    $body['last_name'] = !empty($lead->last_name) ? $lead->last_name : "";
                    $emaildata = $body;
                    $emaildata['readable_password'] = $password;
                    $emaildata['email_encoded'] = Base62::encode($body['email']);

                    \send_email("Bienvenido a " . getenv('APP_TITLE'),$user,'welcome.html',$emaildata);                       
                }

                // send message with email
                if(!empty($body['complete'])){

                    $exists = $this->spot->mapper("App\UserMessage")->first([
                        "lead_id" => $lead->id,
                        "recipient_id" => $user->id
                    ]);

                    if(!$exists){
                        \send_message(1,$user->id,$lead->id,"Se ha iniciado la cotización para " . $lead->brand . ' ' . $lead->model . ' ' . $lead->mt_year);
                        \send_message(1,$lead->gestor_id,$lead->id,"Se recibió un pedido de cotización online #" . $lead->code);
                    }
                }
            
                $body['user_id'] = $user->id;
            }

            if(!empty($body['brand_id']) && empty($lead->brand)){
                $brand = $this->spot->mapper("App\Brand")->first([
                    "id" => $body['brand_id']
                ]);
                $body['brand'] = $brand->title;
            }

            if(!empty($body['version_id']) && (empty($lead->model_id) OR empty($lead->version) OR empty($lead->model))){
                $version = $this->spot->mapper("App\Version")->first([
                    "id" => $body['version_id']
                ]);
                $body['version'] = $version->title;
                $body['model_id'] = $version->model_id;
                $body['model'] = $version->model->title;            
            }

            if($lead){
                $lead->data($body);
                $this->spot->mapper("App\Lead")->save($lead);
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode(['status' => $lead ? "success" : "error", 'id' => (int) $id]));
        });
    });
    
});