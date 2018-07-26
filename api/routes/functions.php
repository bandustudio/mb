<?php 

use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;
use Slim\Views\Twig;
use Intervention\Image\ImageManager;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\DataArraySerializer;
use Tuupola\Base62;
use App\User;
use App\Message;
use App\UserMessage;
use App\Email;
use App\History;

function reference($body,$request){

    global $container;

    if(empty($body['name'])){
        $body['name'] = $request->getUri()->getPath();
    }

    if(!empty($body['payload']) && is_array($body['payload'])){
        $body['payload'] = json_encode($body['payload']);
    }

    $event = new History($body);
    $container["spot"]->mapper("App\History")->save($event);
}

function get_preferences($user){

    global $container;

    $items = ['fav' => [],'alert' => []];
    $mapper = $container["spot"]->mapper("App\Lead")
        ->where(['user_id' => $user->id])
        //->where(['user_id' => "is not null"])        
        ->limit(1000);

    foreach($mapper as $item){
        $items[$item->type][] = $item->lead_id;
    }

    return $items;
}

function get_owned($user){

    global $container;

    $items = [];
    $mapper = $container["spot"]->mapper("App\Lead")
        ->where(['user_id' => $user->id])
        ->where(['user_id' => "is not null"])
        ->limit(1000);

    foreach($mapper as $item){
        $items[] = $item->id;
    }

    return $items;
}

function set_token($user){

    global $container;

    $now = new DateTime();
    $future = new DateTime("now +" . getenv('APP_JWT_EXPIRATION'));
    $jti = Base62::encode(random_bytes(16));

    $payload = [
        "uid" => $user->id,
        "rid" => $user->role_id,
        "iat" => $now->getTimeStamp(),
        "exp" => $future->getTimeStamp(),
        "jti" => $jti
    ];

    $secret = getenv("APP_JWT_SECRET");
    $token = JWT::encode($payload, $secret, "HS256");   

    return $token;
}

function register_if_not_exists($email){

    global $container;

    if(!strlen($email)) return false;

    $user = $container["spot"]->mapper("App\User")->first([
        "email" => $email
    ]);

    if(!$user){
        $password = strtolower(Base62::encode(random_bytes(16)));
        $emaildata['readable_password'] = $password;
        $emaildata['email_encoded'] = Base62::encode($email);
        $user = new User([
            "email" => $email,
            "password" => $password
        ]);
        $container["spot"]->mapper("App\User")->save($user);
        \send_email("Bienvenido a " . getenv('APP_TITLE'),$user,'welcome.html',$emaildata);

        return true;
    }
    return false;
}

function auth_endpoints(){

    global $container; 

    if(!session_id()) {
        session_start();
    }

    $fb = new Facebook\Facebook([
      'app_id' => getenv("FB_APP_ID"),
      'app_secret' => getenv("FB_APP_SECRET"),
      'default_graph_version' => 'v2.2',
    ]);

    $helper = $fb->getRedirectLoginHelper();
    $permissions = ['email']; // Optional permissions
    $loginUrl = $helper->getLoginUrl($container->request->getUri()->getScheme() . '://' . $container->request->getUri()->getHost() . '/facebook/signup', $permissions);

    $data['facebook'] = $loginUrl;

    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/../config/client_id.json');
    $client->setScopes(["profile","email"]);
    $loginUrl = $client->createAuthUrl();

    $data['google'] = $loginUrl;

    return json_encode($data);
    return $container->response->withStatus(200)
        ->withHeader("Content-Type", "application/json")
        ->write(json_encode($data));
}

function copy_profile_photo($url){

    $mime = ".jpg";
    $key = strtolower(Base62::encode(random_bytes(6))) . $mime;
    $dest = getenv('BUCKET_PATH') . '/users/'  . $key;

    while(file_exists($dest)){
        $key = strtolower(Base62::encode(random_bytes(6))) . $mime;
    }

    copy($url, $dest);

    $resolutions = explode(',',getenv('S3_PROFILE_RESOLUTIONS'));

    $manager = new ImageManager();

    foreach($resolutions as $res){
        $parts = explode('x',$res);
        $resized = $manager->make($dest)
            ->orientate()
            ->fit((int) $parts[0],(int) $parts[1])
            ->save(getenv('BUCKET_PATH') . '/users/' . $parts[0] . 'x' . $parts[1] . $key, (int) getenv('S3_QUALITY'));
    }

    return getenv('BUCKET_URL') . '/users/' . $key;
}

function set_username($intended){

    global $container; 

    if($intended == ""){
        $intended = strtolower(Base62::encode(random_bytes(8)));
    }

    $j=0;
    $username = $intended;

    while($container["spot"]->mapper("App\User")->first(["username" => \slugify($username)])){
        $j++;
        $username = $intended . $j;
    }

    return \slugify($username);
}

function log2file($path, $data, $mode="a"){
   $fh = fopen($path, $mode) or die($path);
   fwrite($fh,$data . "\n");
   fclose($fh);
   chmod($path, 0777);
}


function bucket_store($tmp_name,$res,$size = '',$folder = ''){

    global $container;

    $manager = new ImageManager();

    $jti = Base62::encode(random_bytes(8) . date('_YmdHs_'));
    $key = $jti . '.' . getenv('APP_IMAGE_EXTENSION');
    $resolutions = explode(',',$res);

    try {

        $url = getenv('BUCKET_URL') . '/' . $folder . $size . $key;

        $orig = $manager->make($tmp_name)
            ->orientate()
            ->save(getenv('BUCKET_PATH') . '/' . $folder . $key, (int) getenv('APP_IMAGE_QUALITY'));

        foreach($resolutions as $res){
            $parts = explode('x',$res);
            $resized = $manager->make($tmp_name)
                ->orientate()
                ->fit((int) $parts[0],(int) $parts[1])
                ->save(getenv('BUCKET_PATH') . '/' . $folder .  $parts[0] . 'x' . $parts[1] . $key, (int) getenv('APP_IMAGE_QUALITY'));
        }

        $data['url'] = $url;

    } catch (S3Exception $e) {
      // Catch an S3 specific exception.
        $data['error'] = $e->getMessage();
    }

    return $data;
}

function login_redirect($data){
    \log2file( __DIR__ . "/../logs/ecma-" . date('Y-m-d') . ".log",json_encode($data)); 
    return "<script>location.href = '" . \login_redirect_url($data) . "';</script>";
}

function login_redirect_url($data){
    return getenv('CLIENTES_URL') . "/opener?token=" . json_encode($data) . "&url=" . getenv('APP_REDIRECT_AFTER_LOGIN');
}


function process_uploads($body,$entry,$size='',$name = 'uploads'){

    global $container;

    $valid_exts = explode(',',getenv('APP_IMAGE_UPLOAD_EXT')); // valid extensions
    $max_size = getenv('APP_IMAGE_UPLOAD_MAX') * 1024; // max file size in bytes
    $keys = [];
    $data = [];
    $status = "success";
    $ext_error = "Alguna de las imágenes no pudieron ser cargadas. Asegurate que tengan alguna de estas extensiones: " . implode(", ", $valid_exts);
    $size_error = "Alguna de las imágenes no pudieron ser cargadas. Asegurate que su tamaño no sea mayor de " . (ceil($max_size / 1024) / 1000) . 'M';

    // copy, resizes and database storage
    foreach($_FILES[$name]['tmp_name'] as $i => $tmp_name){
        if(is_uploaded_file($_FILES[$name]['tmp_name'][$i]) ){
            $ext = strtolower(pathinfo($_FILES[$name]['name'][$i], PATHINFO_EXTENSION));
            if (in_array($ext, $valid_exts)) {
                if($_FILES[$name]['size'][$i] < $max_size){

                    // generic upload method per file
                    $udata = bucket_store($_FILES[$name]['tmp_name'][$i],getenv('APP_IMAGE_LEAD'),$size);

                    if(empty($udata['error'])) {

                        $body = $entry;
                        $body['position'] = ($i+1);
                        $body['file_url'] = $udata['url'];
                        $body['filesize'] = $_FILES[$name]['size'][$i];

                        $upload = new Upload($body);
                        $data[$i] = $container["spot"]->mapper("App\Upload")->save($upload);
                        $data['url'] = $udata['url'];
                    } else {
                        $status = "error";
                        $data['proc'][$i]['error'] = $udata['error'];
                    }
                } else {
                    $status = "error";
                    $data['proc'][$i]['error'] = $size_error;
                }
            } else {
                $status = "error";
                $data['proc'][$i]['error'] = $ext_error;
            }
        }
    }
    
    $data['status'] = $status;
    return $data;
}

function process_doc($body,$entry,$size='',$name = 'uploads'){

    global $container;

    $valid_exts = ['pdf']; // valid extensions
    $max_size = 10000 * 1024; // max file size in bytes
    $keys = [];
    $data = [];
    $status = "success";
    $ext_error = "El adjunto debe ser un pdf. Asegurate que tengan alguna de estas extensiones: " . implode(", ", $valid_exts);
    $size_error = "Alguna de los documentos no pudieron ser cargados. Asegurate que su tamaño no sea mayor de " . (ceil($max_size / 1024) / 1000) . 'M';

    // copy, resizes and database storage
    foreach($_FILES[$name]['tmp_name'] as $i => $tmp_name){
        if(is_uploaded_file($_FILES[$name]['tmp_name'][$i]) ){
            $ext = strtolower(pathinfo($_FILES[$name]['name'][$i], PATHINFO_EXTENSION));
            if (in_array($ext, $valid_exts)) {
                if($_FILES[$name]['size'][$i] < $max_size){

                    $jti = Base62::encode(random_bytes(8) . date('_YmdHs_'));
                    $key = $jti . '.pdf';

                    try {
                        $url = getenv('BUCKET_URL') . '/polizas' . $key;
                        $path = getenv('BUCKET_PATH') . '/polizas' . $key;

                        copy($_FILES[$name]['tmp_name'][$i],$path);

                        $data['url'] = $url;

                    } catch (S3Exception $e) {
                        $data['error'] = $e->getMessage();
                    }

                    if(!$data['url']) {
                        $status = "error";
                        $data['proc'][$i]['error'] = $udata['error'];
                    }
                } else {
                    $status = "error";
                    $data['proc'][$i]['error'] = $size_error;
                }
            } else {
                $status = "error";
                $data['proc'][$i]['error'] = $ext_error;
            }
        }
    }
    
    $data['status'] = $status;
    return $data;
}
// sender, recipient, item_id, parent_id, message
function send_message($sender_id,$recipient_id,$item_id,$content,$send_email = true,$tag = "notificacion"){

    global $container;

    try {

        $status = 'success';

        $sender = $container["spot"]->mapper("App\User")->first([
            'id' => $sender_id
        ]);

        $recipient = $container["spot"]->mapper("App\User")->first([
            'id' => $recipient_id
        ]);

        $item = null;
        
        if($item_id){
            $item = $container["spot"]->mapper("App\Lead")->first([
                'id' => $item_id
            ]);
        }

        $message = new Message([
            'user_id' => $sender_id,
            'content' => $content
        ]);

        $id = $container["spot"]->mapper("App\Message")->save($message);

        $relation = new UserMessage([
            'user_id' => $sender_id,
            'recipient_id' => $recipient_id,
            'lead_id' => $item_id,
            'message_id' => (int) $id,
            'tag' => $tag
        ]);

        $container["spot"]->mapper("App\UserMessage")->save($relation);

        // send email
        if($send_email){
            \send_email("Recibiste un mensaje", $recipient,'message.html',[
                'sender' => $sender,
                'item' => $item,
                'content' => $content
            ]);
        }
        
    } catch(\Exception $e){
        $message = $e->getMessage();
        var_dump($e->getMessage());
        exit;
    }

    return $message;
}

function send_email($subject,$recipient,$template,$data,$debug = 0){

    global $container; 

    $view = new \Slim\Views\Twig( __DIR__ . '/../templates', [
        'cache' => false
    ]);

    $code = strtolower(Base62::encode(random_bytes(16)));

    while($container["spot"]->mapper("App\Email")->first(["code" => $code])){
        $code = strtolower(Base62::encode(random_bytes(16)));
    }

    $data['code'] = $code;
    $data['app_url'] = getenv('APP_URL');
    $data['recipient'] = $recipient;
    $data['api_url'] = $container->request->getUri()->getScheme() . '://' . $container->request->getUri()->getHost();
    $data['static_url'] = getenv('STATIC_URL');
    $data['app_url'] = getenv('APP_URL');
    $data['clientes_url'] = getenv('CLIENTES_URL');
    $data['gestion_url'] = getenv('GESTION_URL');

    $html = $view->fetch('emails/' . $template,$data);

    if( strpos($subject,getenv('APP_TITLE')) === false) {
        $subject = getenv('APP_TITLE') . " " . $subject;
    }

    $full_name = $recipient->first_name + ' ' + $recipient->last_name;
    $body = [
        'code' => $code,
        'subject' => $subject,
        'user_id' => $recipient->id,
        'email' => $recipient->email,
        'full_name' => $full_name,
        'content' => $html
    ];

    $email = new Email($body);
    $container["spot"]->mapper("App\Email")->save($email);

    if($_SERVER['REMOTE_ADDR'] == '127.0.0.1') return ['status' => 'success'];
    
    //Create a new PHPMailer instance
    $mail = new \PHPMailer;
    $mail->IsSMTP(); 
    $mail->SMTPDebug = $debug?:getenv('MAIL_SMTP_DEBUG');
    $mail->SMTPAuth = getenv('MAIL_SMTP_AUTH');
    $mail->SMTPSecure = getenv('MAIL_SMTP_SECURE');
    $mail->Host = getenv('MAIL_SMTP_HOST');
    $mail->Port = getenv('MAIL_SMTP_PORT');
    $mail->CharSet = "utf8mb4";
    $mail->IsHTML(true);
    $mail->Username = getenv('MAIL_SMTP_ACCOUNT');
    $mail->Password = getenv('MAIL_SMTP_PASSWORD');
    $mail->setFrom(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
    $mail->addReplyTo(getenv('MAIL_FROM'), getenv('MAIL_FROM_NAME'));
    $mail->Subject = $subject;
    $mail->Body = $html;
    $mail->AltBody = \html2text($html);
    $mail->addAddress($recipient->email, $full_name);

    //$mail->addAttachment('images/phpmailer_mini.png');
    $data = [];
    //send the message, check for errors
    if ( ! $mail->send()) {
        $data['status'] =  "error";
        $data['message'] = $mail->ErrorInfo;
    } else {
        $data['status'] = "success";
    }

    return $data;
}

function html2text($Document) {
    $Rules = array ('@<style[^>]*?>.*?</style>@si',
                    '@<script[^>]*?>.*?</script>@si',
                    '@<[\/\!]*?[^<>]*?>@si',
                    '@([\r\n])[\s]+@',
                    '@&(quot|#34);@i',
                    '@&(amp|#38);@i',
                    '@&(lt|#60);@i',
                    '@&(gt|#62);@i',
                    '@&(nbsp|#160);@i',
                    '@&(iexcl|#161);@i',
                    '@&(cent|#162);@i',
                    '@&(pound|#163);@i',
                    '@&(copy|#169);@i',
                    '@&(reg|#174);@i',
                    '@&#(d+);@e'
             );
    $Replace = array ('',
                      '',
                      '',
                      '',
                      '',
                      '&',
                      '<',
                      '>',
                      ' ',
                      chr(161),
                      chr(162),
                      chr(163),
                      chr(169),
                      chr(174),
                      'chr()'
                );
  return preg_replace($Rules, $Replace, $Document);
}

function human_timespan_short($time){

    $str = "";
    $diff = time() - $time; // to get the time since that moment
    $diff = ($diff<1)? $diff*-1 : $diff;

    $Y = date('Y', $time);
    $n = date('n', $time);
    $w = date('w', $time);
    $wdays = ['dom','lun','mar','mié','jue','sáb'];

    if($diff < 86400){
        $str = date('H:i',$time); 
    } elseif($diff < 604800){
        $str = $wdays[$w];
    } elseif($Y <> date('Y')){
        $str = date('j/n/y',$time);  
    } elseif($n <> date('n')){
        $str = date('j/n',$time); 
    } else {
        $str = date('j',$time);  
    }

    return $str;
}

function human_timespan($time){

    $time = time() - $time; // to get the time since that moment
    $time = ($time<1)? $time*-1 : $time;
    $tokens = array (
        31536000 => 'año',
        2592000 => 'mes',
        604800 => 'semana',
        86400 => 'día',
        3600 => 'hora',
        60 => 'minuto',
        1 => 'segundo'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1)?($text=='mes'?'es':'s'):'');
    }
}

function slugify($text){

    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    // trim
    $text = trim($text, '-');

    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);

    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return strtolower(Base62::encode(random_bytes(8)));
    }

    return $text;
}
