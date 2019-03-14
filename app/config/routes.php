<?php

$router = new \Phalcon\Mvc\Router(false);

$router->add('/object/new/:action', [
    'controller' => 'object',
    'uuid' => 'new',
    'action' => 1,
]);

$router->add('/object/{uuid}/:action', [
    'controller' => 'object',
    'action' => 2,
]);

$router->add('/object/{objectUuid}/versions/{versionUuid}', [
    'controller' => 'object',
    'action' => 'showVersion',
]);

$router->add('/object/{objectUuid}/delete/{versionUuid}', [
    'controller' => 'object',
    'action' => 'delete'
]);
$router->add('/object/{uuid}', [
    'controller' => 'object',
    'action' => 'index',
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
 
