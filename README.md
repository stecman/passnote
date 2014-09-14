# **Passnote**: encrypted data store

Passnote is a web application for storing secrets. It's not complete yet, but it's in a working state. [More info on how it works.](docs/architecture.md)

![Passnote list view](https://i.imgur.com/OpFPetu.png)

## Installation and configuration

To run Passnote, you'll need:

* PHP >= 5.4 configured with `--with-openssl` (this is normal)
* [Phalcon PHP](http://phalconphp.com/) >= 1.3.0
* A database. The schema for MySQL is in `docs/mysql/schema.sql`, though Phalcon supports other databases
* Composer
* A web server with the document root set to the project's `public` directory and set up to pass unresolved requests through to `public/index.php`. The built-in PHP web sever can be used for development by running `php -S 127.0.0.1:8000` in the `public/` directory.

Additionally, to build and develop the interface components, you'll need:

* Node JS and NPM to run most of the interface toolkit
* [LESS](http://lesscss.org/) compiler `lessc`
* [Bower](http://bower.io/) to install Javascript and CSS dependencies: `bower`

To build the CSS, you'll need to run `make less` in the project root.

### Config

You can override master config values from `app/config/config.php` by placing a `config.php` file in the root of the install. This file should return an array to be merged over the master config. You can also use this file to set up your environment. Eg:


```php
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

### Creating an account

Once you've got the project running, you can create an account using the application's console:

    $ ./bin/task.php users create you@example.com

This will prompt for an account password, then generate an account key and an TOTP key URL. Currently there is no option to not use a one-time-password with an account. You can use an app like [Google Authenticator](https://support.google.com/accounts/answer/1066447) as a TOTP manager.

## Roadmap

Passnote is still in development and not all core features are implemented yet.

**Core functionality that still needs to be implemented:**

* Account settings: managing password and keys
* Unlocking objects that use a key other than a user's account key (forms required)
* Deleting and/or archiving objects
* Pagination of list view / better list or search view

**Planned functionality:**

* Command-line interface (console)
* Ability to share objects between users