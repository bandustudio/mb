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
use App\Post;
use App\User;
use App\Dealer;
use App\Service;
use App\Product;
use App\Email;
use App\VisitSchedule;
use App\ProductModel;
use App\ThemePosition;
use App\ThemeType;
use App\ThemeSection;

$app->group('/v1', function() {

    $this->group('/app', function() {

        $this->post("/turnos", function ($request, $response, $arguments) {
            //$body = $request->getParsedBody();
            $body = $request->getParams();

            $turno = null;

            if(empty($body)){
                throw new ForbiddenException("No parameters recieved.", 403);
            }

            $data['status'] = "error";
            $turno = new VisitSchedule();   
            $code = strtolower(Base62::encode(random_bytes(6)));

            while($this->spot->mapper("App\VisitSchedule")->first(["code" => $code])){
                $code = strtolower(Base62::encode(random_bytes(6)));
            }

            $parts = explode(' ',$body['full_name']);
            $first_name = $parts[0];
            unset($parts[0]);
            $last_name = implode(' ',$parts);

            $body['code'] = $code;
            $body['first_name'] = $first_name;
            $body['scheduled'] = new \DateTime(date('Y-m-d H:i:s',strtotime($body['scheduled'])));
            $body['last_name'] = $last_name;

            $turno->data($body);
            $id = $this->spot->mapper("App\VisitSchedule")->save($turno);

            if($id){
                $data['status'] = "success";
                $data['turno_id'] = $id;
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });

        $this->post("/subnav", function ($request, $response, $arguments) {

            $featured = [];
            $services = [];
            $dealers = [];

            $items = $this->spot->mapper("App\Product")
                ->where(['enabled' => 1])
                ->order(['title' => "ASC"]);

            $featured_row_count = 5;

            // featured
            foreach($items as $item){
                if($item->type->title){
                    if(!isset($featured[strtolower($item->type->title)])) $featured[strtolower($item->type->title)] = [];

                    if(count($featured[strtolower($item->type->title)]) <= $featured_row_count){
                        $featured[strtolower($item->type->title)][] = (object) [
                            'title' => $item->title,
                            'intro' => $item->intro?:\words($item->content,20),
                            'slug' => $item->title_slug,
                            'pic' => \subpic('200x140',$item->pic1_url)
                        ];
                    }
                }
            }  

            // services
            $_services = $this->spot->mapper("App\Service")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(1000);

                $services=[];
            foreach($_services as $item){
                $services[$item->title_slug] = (object) [
                    'id' => $item->id,
                    'title' => $item->title,
                    'intro' => $item->intro,
                    'content' => $item->content_html,
                    'slug' => $item->title_slug,
                    'picture' => $item->pic1_url,
                    'pic_on' => $item->pic_on_url,
                    'pic_off' => $item->pic_off_url
                ];
            }


            // dealers
            $_dealers = $this->spot->mapper("App\Dealer")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(1000);

                $dealers=[];
            foreach($_dealers as $item){
                $dealers[] = (object) [
                    'id' => $item->id,
                    'title' => $item->title,
                    'lat' => (float) $item->lat,
                    'lng' => (float) $item->lng,
                    'slug' => $item->title_slug,
                    'address' => $item->address,
                    'vicinity' => $item->vicinity,
                    'administrative_area_level_1' => $item->administrative_area_level_1,
                    'formatted_address' => $item->formatted_address,
                    'pic' => \subpic('640x480',$item->pic1_url)
                ];
            }

            $data = (object) ['featured' => $featured,'services' => $services,'dealers' => $dealers];
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });

        $this->post("/dealers", function ($request, $response, $arguments) {
            $body = $request->getParsedBody();

            $mapper = $this->spot->mapper("App\Dealer")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(1000);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Dealer);
            $data = $fractal->createData($resource)->toArray();
            
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));  
        });

        $this->post('/dealers/{slug}', function ($request, $response, $args) {

            $mapper = $this->spot->mapper("App\Dealer")
                ->where(['enabled' => 1])
                ->where(['title_slug' => urldecode( $request->getAttribute('slug') )])
                ->first();

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Item($mapper, new Dealer);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        $this->post("/services", function ($request, $response, $arguments) {
            $body = $request->getParsedBody();

            $mapper = $this->spot->mapper("App\Service")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(1000);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Service);
            $data = $fractal->createData($resource)->toArray();
            
            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));  
        });

        $this->post('/services/{slug}', function ($request, $response, $args) {

            $mapper = $this->spot->mapper("App\Service")
                ->where(['enabled' => 1])
                ->where(['title_slug' => urldecode( $request->getAttribute('slug') )])
                ->first();

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Item($mapper, new Service);
            $data['item'] = $fractal->createData($resource)->toArray();

            $mapper = $this->spot->mapper("App\Dealer")
                ->where(['enabled' => 1]);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Dealer);
            $data['dealers'] = $fractal->createData($resource)->toArray();

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

        $this->post('/home', function ($request, $response, $args) {

            $mapper = $this->spot->mapper("App\Product")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(10);


            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Product);
            $data['items'] = $fractal->createData($resource)->toArray();

            $mapper = $this->spot->mapper("App\Post")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(10);


            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Post);
            $data['posts'] = $fractal->createData($resource)->toArray();


            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        $this->post('/products', function ($request, $response, $args) {
            $mapper = $this->spot->mapper("App\Product")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(1000);

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Product);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        $this->post('/products/{slug}', function ($request, $response, $args) {

            $type = $this->spot->mapper("App\ThemeType")
                ->where(['title' => ucfirst( $request->getAttribute('slug'))])
                ->first();

            if($type === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            $mapper = $this->spot->mapper("App\Product")
                ->where(['enabled' => 1])
                ->where(['type_id' => $type->id])
                ->order(['created' => 'DESC'])
                ->limit(1000);

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Product);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 


        $this->post('/posts', function ($request, $response, $args) {
            $mapper = $this->spot->mapper("App\Post")
                ->where(['enabled' => 1])
                ->order(['created' => 'DESC'])
                ->limit(10);

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new Post);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 


        $this->post("/posts/{slug}", function ($request, $response, $arguments) {

            $mapper = $this->spot->mapper("App\Post")
                ->where(['title_slug' => $request->getAttribute('slug')])
                ->where(['enabled' => 1])
                ->first();

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }

            /* Serialize the response data. */

            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Item($mapper, new Post);
            $data = $fractal->createData($resource)->toArray();                    

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });

        $this->post('/types', function ($request, $response, $args) {
            $mapper = $this->spot->mapper("App\ThemeType")
                ->where(['enabled' => 1])
                ->order(['title' => 'ASC'])
                ->limit(1000);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new ThemeType);
            $data = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        // Usuario crean cotizaciones, se actualizas usuarios y vehículos

        $this->post('/lead', function ($request, $response, $args) {

            //$body = $request->getParsedBody();
            $body = $request->getParams();

            $lead = null;

            if(empty($body)){
                throw new ForbiddenException("No parameters recieved.", 403);
            }

            $lead = new Lead($body);    
            $code = strtolower(Base62::encode(random_bytes(6)));

            while($this->spot->mapper("App\Lead")->first(["code" => $code])){
                $code = strtolower(Base62::encode(random_bytes(6)));
            }

            $body['code'] = $code;
            $lead->data($body);
            $id = $this->spot->mapper("App\Lead")->save($lead);

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

            if(!empty($body['model_id']) && empty($lead->model)){
                $model = $this->spot->mapper("App\ProductModel")->first([
                    "id" => $body['model_id']
                ]);
                $body['model'] = $model->title;
            }

            if(!empty($body['version_id']) && (empty($lead->model_id) OR empty($lead->version) OR empty($lead->model))){
                $version = $this->spot->mapper("App\Version")->first([
                    "id" => $body['version_id']
                ]);
                $body['version'] = $version->title;
                $body['model_id'] = $version->model_id;
                $body['model'] = $version->model->title;            
            }

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode(['status' => $lead ? "success" : "error", 'id' => (int) $id]));
        });

        $this->post('/{slug}', function ($request, $response, $args) {

            $mapper = $this->spot->mapper("App\Product")
                ->where(['title_slug' => $request->getAttribute('slug')])
                ->where(['enabled' => 1])
                ->first();

            if($mapper === false){
                throw new ForbiddenException("No resource was found.", 404);
            }
            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Item($mapper, new Product);
            $data['item'] = $fractal->createData($resource)->toArray();

            $mapper = $this->spot->mapper("App\ProductModel")
                ->where(['enabled' => 1]);

            /* Serialize the response data. */
            $fractal = new Manager();
            $fractal->setSerializer(new DataArraySerializer);
            $resource = new Collection($mapper, new ProductModel);
            $data['models'] = $fractal->createData($resource)->toArray();

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

    });    
});