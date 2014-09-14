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

[See docs/installation.md for step by step installation instructions](docs/installation.md)

## Road map

Passnote is still in development and not all core features are implemented yet.

**Core functionality that still needs to be implemented:**

* Account settings: managing password and keys
* Unlocking objects that use a key other than a user's account key (forms required)
* Deleting and/or archiving objects
* Pagination of list view / better list or search view

**Planned functionality:**

* Command-line interface (console)
* Ability to share objects between users