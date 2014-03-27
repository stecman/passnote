#!/usr/bin/env php
<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI,
    Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

if (PHP_SAPI !== 'cli') die('This script is for CLI use only');

defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__DIR__)).'/app');

// Error handling
error_reporting(E_ALL);

// Autoloading
if (file_exists($autoload = __DIR__ . '/../vendor/autoload.php')) {
    require_once $autoload;
    unset($autoload);
} else {
    die("Composer vendors are not installed. Run `composer install`\n");
}

$whoops = new Whoops\Run();
$whoops->pushHandler(new Whoops\Handler\CallbackHandler(function(\Exception $e){
    echo "Error: ", $e->getMessage(), "\n\n", $e->getTraceAsString(), "\n";
    exit(1);
}));
$whoops->register();

// DI
$di = new CliDI();

// Load the configuration file (if any)
if(is_readable(APPLICATION_PATH . '/config/config.php')) {
    $config = include APPLICATION_PATH . '/config/config.php';
    $di->set('config', $config);
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

$di->set('db', function () use ($config) {
    return new DbAdapter(array(
        'host' => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname
    ));
});

// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? str_replace('-', '_', $argv[2]) : null));
define('LITERAL_ACTION', (isset($argv[2]) ? $argv[2] : null));

function getSubclassesOf($parent) {
    $result = array();
    foreach (get_declared_classes() as $class) {
        if (is_subclass_of($class, $parent))
            $result[] = $class;
    }
    return $result;
}

function filterMethods($array) {
    static $methods = null;

    if (!$methods) {
        $methods = [];

        $taskClass = new ReflectionClass('\Phalcon\CLI\Task');
        foreach ($taskClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methods[] = $method->getName();
        }
    }

    return array_filter($array, function($value) use ($methods) {
        return !in_array($value->getName(), $methods);
    });
}

function printHelp() {
    global $argv;
    echo "Usage: {$argv[0]} <task> [action] [args...]\n\n";
    echo "Available tasks:\n";

    foreach (glob(APPLICATION_PATH.'/tasks/*Task.php') as $filename) {
        require_once $filename;
    }

    foreach (getSubclassesOf('\Phalcon\CLI\Task') as $className) {
        $class = new ReflectionClass($className);
        echo "  ".strtolower(preg_replace('/Task$/', '', $class->getName())).":\n";

        /** @var ReflectionMethod $method */
        foreach (filterMethods($class->getMethods(ReflectionMethod::IS_PUBLIC)) as $method) {
            $name = str_replace('_', '-', $method->getName());
            echo "    ".strtolower(preg_replace('/(Action$)/', '', $name));

            foreach ($method->getParameters() as $param) {
                if ($param->isOptional()) {
                    echo " [{$param->getName()}]";
                } else {
                    echo " <{$param->getName()}>";
                }
            }

            echo "\n";
        }
    }
}

if (!CURRENT_TASK) {
    printHelp();
    exit(1);
}

$class = new ReflectionClass(\Phalcon\Text::camelize(CURRENT_TASK.'_task'));
if ($class->hasMethod(CURRENT_ACTION.'Action')) {
    $method = $class->getMethod(CURRENT_ACTION.'Action');

    if ($method->isPublic()) {
        $args = array_slice($argv, 3);
        if ($method->getNumberOfRequiredParameters() > count($args)) {
            echo "Missing required parameters.\n";
            echo "Usage: {$argv[0]} ".CURRENT_TASK." ".LITERAL_ACTION;
            foreach ($method->getParameters() as $param) {
                if ($param->isOptional()) {
                    echo " [{$param->getName()}]";
                } else {
                    echo " <{$param->getName()}>";
                }
            }
            echo "\n";
            exit(1);
        }

        $instance = $class->newInstance();
        $instance->setDi($di);
        $method->invokeArgs($instance, $args);
    } else {
        echo 'Task "'.CURRENT_TASK.'" does not have an action "'.LITERAL_ACTION.'".';
        printHelp();
    }
} else {
    echo 'Task "'.CURRENT_TASK.'" does not have an action "'.LITERAL_ACTION.'".';
    printHelp();
}