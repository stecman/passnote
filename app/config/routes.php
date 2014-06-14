<?php

$router = new \Phalcon\Mvc\Router(false);

$router->add('/object/([0-9]+|new)/:action', [
    'controller' => 'object',
    'id' => 1,
    'action' => 2,
]);

$router->add('/object/([0-9]+)/versions/([0-9]+)', [
    'controller' => 'object',
    'action' => 'showVersion',
    'objectId' => 1,
    'versionId' => 2,
]);

$router->add('/object/([0-9]+)', [
    'controller' => 'object',
    'action' => 'index',
    'id' => 1,
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
 
