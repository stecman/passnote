<?php

error_reporting(E_ALL);

try {

    define('DEV_MODE', true);

    ini_set('session.hash_function', 'sha512');
    session_start();

    /**
     * Use composer autoloader
     */

    if (!file_exists($composerAutoload = __DIR__ . '/../vendor/autoload.php')) {
        header('HTTP/1.1 500');
        die("<h1>Server Error</h1><p>Composer vendors are not installed on the server.</p>");
    }

    require_once $composerAutoload;
    unset($composerAutoload);

    /**
     * Read the configuration
     */
    $config = include __DIR__ . "/../app/config/config.php";

    /**
     * Read auto-loader
     */
    include __DIR__ . "/../app/config/loader.php";

    /**
     * Read services
     */
    include __DIR__ . "/../app/config/services.php";

    /**
     * Read environment
     */
    if (file_exists($environment = __DIR__ . "/../environment.php")) include $environment;

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

} catch (\Exception $e) {

    /**
     * Clear the buffer and show the 500 error page
     */
    if (!DEV_MODE) {
        ob_flush();
        header('HTTP/1.1 500 Internal Server Error', true, 500);
        include __DIR__ .'/maintenance/error-500.html';
    }

    throw $e;
}