<?php 

use App\Post;
use App\Product;
use App\Service;

function subpic($id,$url){
    $parts = explode('/',$url);
    $name = $parts[count($parts)-1];
    $parts[count($parts)-1] = $id.$parts[count($parts)-1];
    return implode('/',$parts);
}

function words($str,$cut=10){
    $words=str_word_count($str,true);
    $a=array_slice($words,$cut);
    $s=join(' ',$a);
    return $s;    
}

$container = $app->getContainer();

$env = $container['spot']->mapper("App\Config")
    ->where(['enabled' => 1]);

foreach($env as $config){
    define($config->config_key,$config->config_value);
}

$container['view'] = function ($c) {

    $uriparts = array_values(array_filter(explode('/', $c->request->getUri()->getPath())));
    $view = new \Slim\Views\Twig('templates', [
        'cache' => false
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $share_title = getenv('APP_TITLE');
    $share_text = getenv('APP_TEXT');
    $share_pic = getenv('APP_PIC');
    $share = null;

    // social networks
    if($uriparts[0]==='posts'){
        $share = $c['spot']->mapper("App\Post")
            ->where(['title_slug' => $uriparts[1]])
            ->where(['enabled' => 1])
            ->first();
    } else {
        $share = $c['spot']->mapper("App\Product")
            ->where(['title_slug' => $uriparts[1]])
            ->where(['enabled' => 1])
            ->first();
    }

    if($share){
        $share_title = $share->title;
        $share_text = $share->intro;
        $share_pic = $share->picture;        
    }

    $view->offsetSet('params', $_REQUEST?:false);
    $view->offsetSet('rev_parse', substr(exec('git rev-parse HEAD'),0,7));
    $view->offsetSet('localhost', ($_SERVER['REMOTE_ADDR'] == "127.0.0.1"));
    $view->offsetSet('share_title', $share_title);
    $view->offsetSet('share_text', $share_text);
    $view->offsetSet('share_pic', $share_pic);

    foreach(['app_title','app_text','app_url','api_url','static_url','app_phone','app_whatsapp','app_facebook'] as $tag){
        if(defined(strtoupper($tag))){
            $view->offsetSet($tag,constant(strtoupper($tag)));
        }
    }

    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->get('[{path:.*}]', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
});

$app->run();