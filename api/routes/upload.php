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
use App\Upload;
use App\Email;
use App\Brand;
use App\Model;
use App\Version;
use App\Message;
use App\UserMessage;

$app->group('/v1', function() {

    $this->group('/upload', function() {

        $this->post("/simple", function ($request, $response, $arguments) {

            if ($_FILES['file']['name']) {
                if (!$_FILES['file']['error']) {
                    $fsy = getenv('UPLOADS_PATH') . '/' . date('Y');
                    $folder = date('Y').'/'.date('m');
                    $fsx = getenv('UPLOADS_PATH') . '/' . $folder;
                        
                    if( ! is_dir($fsy)){
                        mkdir($fsy);
                        chmod($fsy,0777);
                    }

                    if( ! is_dir($fsx)){
                        mkdir($fsx);
                        chmod($fsx,0777);
                    }

                    $name = md5(rand(100, 200));
                    $ext = explode('.', $_FILES['file']['name']);
                    $filename = $name . '.' . $ext[1];
                    $destination = $fsx . '/' . $filename; //change this directory
                    $location = $_FILES["file"]["tmp_name"];

                    move_uploaded_file($location, $destination);
                    $message =  getenv('STATIC_URL') . '/uploads/' . $folder . '/' . $filename;//change this URL
                }
                else
                {
                  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
                }
            }

            return $message;
        });

        $this->post("/lead/{code}", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $mapper = $this->spot->mapper("App\Lead")->first([
                "code" => $request->getAttribute('code'),
                "user_id" => $this->token->decoded->uid
            ]);

            if( ! $mapper){
                throw new NotFoundException("Lead not found. (1)", 404);        
            }

            $body = $request->getParsedBody();
            
            $data = \process_uploads($body,['lead_id' => $mapper->id],'200x200');

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });

        $this->post("/avatar/{id}", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $mapper = $this->spot->mapper("App\User")->first([
                "id" => $this->token->decoded->uid
            ]);

            if( ! $mapper){
                throw new NotFoundException("User not found. (1)", 404);        
            }

            $body = $request->getParsedBody();
            
            $data = \process_uploads($body,['user_id' => $mapper->id],'200x200');

            $mapper->data(['picture' => $data['url']]);

            $this->spot->mapper("App\User")->save($mapper);

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        }); 

        $this->post("/poliza/{code}", function ($request, $response, $arguments) {

            if (false === $this->token->decoded->uid) {
                throw new ForbiddenException("Token not allowed to list vehicles.", 403);
            }

            $mapper = $this->spot->mapper("App\Lead")->first([
                "code" => $request->getAttribute('code')
            ]);

            if( ! $mapper){
                throw new NotFoundException("Lead not found. (1)", 404);        
            }

            $body = $request->getParsedBody();
            
            $data = \process_doc($body,['lead_id' => $mapper->id]);

            $mapper->data(['doc_poliza' => $data['url']]);

            $this->spot->mapper("App\Lead")->save($mapper);

            return $response->withStatus(200)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data));
        });                
    });
});