<?php

try {

    ini_set('phalcon.orm.exception_on_failed_save', true);
    ini_set('phalcon.orm.not_null_validations', false);


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
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    $application->response->sendHeaders();
    $application->response->sendCookies();
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