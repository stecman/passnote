#!/usr/bin/env php
<?php

use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;

require __DIR__ . '/../app/config/cli-bootstrap.php';

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

        if ($class->isAbstract()) {
            // Skip abstract classes
            continue;
        }

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