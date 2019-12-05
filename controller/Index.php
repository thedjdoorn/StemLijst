<?php
namespace Stemlijst;

require_once __DIR__.'/../vendor/autoload.php';

class IndexController
{
    public function index()
    {
        $latte = new \Latte\Engine;
        return $latte->renderToString(__DIR__.'/../templates/home.latte');
    }

    public function notFound(){
        $latte = new \Latte\Engine;
        return $latte->renderToString(__DIR__.'/../templates/notFound.latte');
    }

}