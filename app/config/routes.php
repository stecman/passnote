<?php

$router = new \Phalcon\Mvc\Router(false);

$router->add('/object/([A-Fa-f0-9])+/:action', [
    'controller' => 'object',
    'id' => 1,
    'action' => 2,
]);

$router->add('/object/([A-Fa-f0-9])+', [
    'controller' => 'object',
    'action' => 'index',
    'id' => 1,
]);

$router->add('/object/new', [
    'controller' => 'object',
    'action' => 'new'
]);

$router->add('/auth/:action', [
    'controller' => 'auth',
    'action' => 1
]);

$router->add('/', [
    'controller' => 'index',
    'action' => 'index'
]);

$router->notFound([
    'controller' => 'index',
    'action' => 'error'
]);

return $router;
 