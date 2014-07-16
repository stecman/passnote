<?php

use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
if (!isset($di)) {
    $di = new FactoryDefault();
}

/**
 * Raven PHP - logger for Sentry
 */
$di->set('raven', function() use ($config) {
    if ($url = $config->logging->ravenUrl) {

    } else {

    }
    $raven = new Raven_Client($url);
    return $raven;
});

/**
 * Error handling
 *
 * Dev mode is enabled when the environment variable DEV_MODE is set.
 */

if (DEV_MODE) {
    ini_set('display_errors', true);
    ini_set('html_errors', true);
    new \Whoops\Provider\Phalcon\WhoopsServiceProvider($di);
} else {
    ini_set('display_errors', false);
    ini_set('html_errors', false);
    set_exception_handler(array(
        new Raven_ErrorHandler($di->get('raven')),
        'handleException'
    ));
}

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->set('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
}, true);

$di->set('router', function () {
    return require __DIR__ . '/routes.php';
});

/**
 * Setting up the view component
 */
$di->set('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines(array(
        '.volt' => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions(array(
                'compiledPath' => $config->application->cacheDir,
                'compiledSeparator' => '_',
                'compileAlways' => DEV_MODE
            ));

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));

    return $view;
}, true);

/**
 * View cache component
 */
$di->set('viewCache', function() {
    $frontend = new \Phalcon\Cache\Frontend\Output();

    if (extension_loaded('apc')) {
        $cache = new \Phalcon\Cache\Backend\Apc($frontend);
    } else {
        $cache = new \Phalcon\Cache\Backend\File($frontend);
    }

    return $cache;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->set('modelsMetadata', function() {
    if (DEV_MODE) {
        return new \Phalcon\Mvc\Model\MetaData\Memory();
    } else if (extension_loaded('apc')) {
        return new \Phalcon\Mvc\Model\MetaData\Apc();
    } else {
        return new \Phalcon\Mvc\Model\MetaData\Files();
    }
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->set('db', function () use ($config) {
    return new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname
    ));
});

$di->setShared('renderer', function() use ($config) {
    $renderService = new \Stecman\Passnote\Object\Renderer();

    foreach ($config->get('object-renderers') as $rendererClass) {
        $renderService->addRenderer(new $rendererClass);
    }

    return $renderService;
});

/**
 * Start the session the first time some component request the session service
 */

$di->setShared('session', function () {

    ini_set('session.name', 'session');
    ini_set('session.cookie_httponly', true);
    ini_set('session.hash_function', 'sha256');
    ini_set('session.gc_maxlifetime', 20 * 60 * 60);

    if (!DEV_MODE) {
        ini_set('session.cookie_secure', true);
    }

    $session = new SessionAdapter();
    $session->start();
    return $session;
});

$di->set('cookies', function() {
    $cookies = new Phalcon\Http\Response\Cookies();
    $cookies->useEncryption(false);
    return $cookies;
});


$di->set('dispatcher', function() use ($di) {
    require_once __DIR__ . '/../plugins/Security.php';

    $security = new Security($di);
    $eventsManager = $di->getShared('eventsManager');

    // Intervene to require login before anything happens
    $eventsManager->attach('dispatch', $security);

    $dispatcher = new Phalcon\Mvc\Dispatcher();
    $dispatcher->setEventsManager($eventsManager);
    return $dispatcher;
});


//Register the flash service with custom CSS classes
$di->set('flash', function(){
    $flash = new \Phalcon\Flash\Direct(array(
        'error' => 'alert alert-error',
        'success' => 'alert alert-success',
        'notice' => 'alert alert-info',
        'warning' => 'alert alert-warning',
        'plain' => 'alert'
    ));
    return $flash;
});