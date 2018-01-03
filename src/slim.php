<?php

require './vendor/autoload.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['view'] = function($container){
  $view = new \Slim\Views\Twig('views', [
    // 'cache' => 'views/cache'
    'cache' => false
  ]);

  $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
  $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

  return $view;
};

require 'routes.php';

$app->run();
