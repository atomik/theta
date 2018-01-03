<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $req, Response $rsp, $arg){
  return $this->view->render($rsp, 'test.php');
})->setName('index');

$app->get('/hello/{name}', function(Request $req, Response $rsp, $arg){
  return $this->view->render($rsp, 'hello.php', [
    'name' => $arg['name']
  ]);
})->setName('hello');
