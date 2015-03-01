<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI;

if (PHP_SAPI !== 'cli') die('This script is for CLI use only');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', dirname(__DIR__));

// Error handling
error_reporting(E_ALL);

// Autoloading
if (file_exists($autoload = __DIR__ . '/../../vendor/autoload.php')) {
    require_once $autoload;
    unset($autoload);
} else {
    die("Composer vendors are not installed. Run `composer install`\n");
}

if (class_exists('Whoops\Run')) {
    $whoops = new Whoops\Run();
    $whoops->pushHandler(new Whoops\Handler\PlainTextHandler());
    $whoops->register();
}

// Load the configuration file (if any)
if(is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
}

// Load the loader
if(is_readable(APPLICATION_PATH . '/config/loader.php')) {
    include APPLICATION_PATH . '/config/loader.php';

    $loader->registerDirs(
        array_merge(
            $loader->getDirs(),
            [APPLICATION_PATH . '/tasks']
        )
    );

    $loader->register();
}

// DI
$di = new CliDI();

require __DIR__ . '/services.php';
