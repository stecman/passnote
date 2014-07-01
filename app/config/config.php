<?php

$config = [
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => 'root',
        'dbname'      => 'passnote',
    ],
    'application' => [
        'controllersDir' => __DIR__ . '/../../app/controllers/',
        'modelsDir'      => __DIR__ . '/../../app/models/',
        'formsDir'      => __DIR__ . '/../../app/forms/',
        'viewsDir'       => __DIR__ . '/../../app/views/',
        'pluginsDir'     => __DIR__ . '/../../app/plugins/',
        'libraryDir'     => __DIR__ . '/../../app/library/',
        'cacheDir'       => __DIR__ . '/../../app/cache/',
        'baseUri'        => '/',
    ],
    'logging' => [
        'ravenUrl' => ''
    ],
    'object-renderers' => [
        '\Stecman\Passnote\Object\Renderer\PlainText',
        '\Stecman\Passnote\Object\Renderer\Markdown'
    ]
];

// Merge in any environment specific config
$envFile = __DIR__ . '/../../config.php';
if (file_exists($envFile)) {
    $environmentConfig = require $envFile;

    if (is_array($environmentConfig)) {
        $config = array_replace_recursive($config, $environmentConfig);
    }
}

// If dev mode wasn't enabled in the environment config, assume it should be off
if (!defined('DEV_MODE')) {
    define('DEV_MODE', false);
}

return new \Phalcon\Config($config);