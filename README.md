# **Passnote**: encrypted data store

Passnote is a web application for storing secrets.

## Installation and configuration

To run Passnote, you'll need:

* PHP >= 5.5 compiled `--with-openssl`
* [Phalcon PHP](http://phalconphp.com/) ~1.3
* A web server with the document root set to the `public` directory, and set up to pass all non-file requests through `public/index.php`.

Additionally, to build and develop the interface components, you'll need:

* Node JS and NPM to run most of the interface toolkit
* [LESS](http://lesscss.org/) compiler `lessc`
* [React](http://facebook.github.io/react/) tools: `npm install -g react-tools`
* [Bower](http://bower.io/) to install Javascript and CSS dependencies: `npm install -g bower`

### Config

You can override master config values in `app/config/config.php` by placing a `config.php` file in the root of the install (the same directory that `public` and `app` reside in). This file should return an array to be merged over the master config. You can also use this file to set up your environment. Eg:

```
<?php

error_reporting(E_ALL);

define('DEV_MODE', true);

return [
    'database' => [
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'passnote',
    ]
];
```