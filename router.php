<?php
require_once 'vendor/autoload.php';
require_once __DIR__.'/controller/Index.php';
require_once __DIR__.'/controller/Groep.php';
require_once __DIR__.'/controller/Stemlijst.php';

$klein = new \Klein\Klein();

$klein->respond("GET", '/', function (){
    $indexController = new \Stemlijst\IndexController();
    return $indexController->index();
});

$klein->respond("POST", '/groep', function($request, $response){
    if(!empty($request->param('firstname')) || empty($request->param('name'))){
        $response->redirect('/');
    } else {
        return (new \Stemlijst\GroepController())->create($request->param('name'));
    }
});

$klein->respond("GET", '/groep/[:slug]', function($request, $response){
    if(empty($request->slug)){
        $response->redirect('/');
    } else {
        $result = (new \Stemlijst\GroepController())->get($request->slug);
        if(!$result){
            $response->code(404);
            return '';
        } else {
            return $result;
        }
    }
});

$klein->respond("POST", '/groep/[:slug]', function($request, $response){
    if(empty($request->slug) || !empty($request->param('firstname') || empty($request->param('hash')))){
        $response->redirect('/');
    } else {
        $result = (new \Stemlijst\GroepController())->addList($request->slug, $request->param('hash'));
        if(!$result){
            $response->code(404);
            return '';
        } else {
            return $result;
        }
    }
});

$klein->respond("GET", '/putlist/[:hash]', function ($request, $response){
    if(empty($request->hash)){
        return 'wel wat invoeren lulhannes';
    } else {
        $stemlijst = (new Stemlijst($request->hash));
        $stemlijst->parse();
        return $stemlijst->save();
    }
});

$klein->respond("GET", '/over', function (){
   $latte = new \Latte\Engine();
   return $latte->renderToString(__DIR__.'/templates/about.latte');
});

//catch all errors
$klein->onHttpError(function ($code, $router) {
    if ($code == 404){
        $router->response()->body('You\'re not supposed to be here, please leave');
    }
    elseif ($code >= 400 && $code < 500) {
        $router->response()->body(
            'This is your fault ('. $code . ')'
        );
    } elseif ($code >= 500 && $code <= 599) {
        error_log('uhhh, something bad happened');
    }
});

$klein->dispatch();