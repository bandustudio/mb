<?php 

use App\Post;
use App\Vehicle;

function subpic($id,$url){
    $parts = explode('/',$url);
    $name = $parts2[count($parts2)-1];
    $parts[count($parts)-1] = $id.$parts[count($parts)-1];
    return implode('/',$parts);
}

$container = $app->getContainer();

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
    $pic = null;

    if($uriparts[0]==='posts'){
        $share = $c['spot']->mapper("App\Post")
            ->where(['title_slug' => $uriparts[1]])
            ->where(['enabled' => 1])
            ->first();
    } else {
        $share = $c['spot']->mapper("App\Vehicle")
            ->where(['title_slug' => $uriparts[1]])
            ->where(['enabled' => 1])
            ->first();
    }

    $mapper = $c['spot']->mapper("App\Vehicle")
        ->where(['enabled' => 1])
        ->order(['title' => "ASC"]);

    foreach($mapper as $item){
        if($item->type->title){
            if(!isset($featured[strtolower($item->type->title)])) $featured[strtolower($item->type->title)] = [];
            $featured[strtolower($item->type->title)][] = [
                'title' => $item->title,
                'intro' => $item->intro?:substr(0,50,strip_tags($item->content_html)),
                'pic' => \subpic('200x140',$item->pic1_url)
            ];
        }
    }

    if($share){
        $share_title = $share->title;
        $share_text = $share->intro;
        $pic = $share->picshare_url?:$share->pic1_url;
    }

    if($pic){
        $share_pic = \subpic('640x480',$pic);
    }

    $view->offsetSet('params', $_REQUEST?:false);
    $view->offsetSet('featured', $featured);
    $view->offsetSet('rev_parse', substr(exec('git rev-parse HEAD'),0,7));
    $view->offsetSet('localhost', ($_SERVER['REMOTE_ADDR'] == "127.0.0.1"));
    $view->offsetSet('share_title', $share_title);
    $view->offsetSet('share_text', $share_text);
    $view->offsetSet('share_pic', $share_pic);

    foreach(['app_title','app_text','app_url','api_url','static_url','app_phone','app_whatsapp','app_facebook'] as $tag){
        $view->offsetSet($tag,getenv(strtoupper($tag)));
    }

    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

$app->get('[{path:.*}]', function ($request, $response, $args) {
    return $this->view->render($response, 'index.html');
});

$app->run();