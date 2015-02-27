# **Passnote**: encrypted data store

Passnote is a web application for storing secrets. It's not complete yet, but it's in a working state.

![Passnote screenshot](https://i.imgur.com/OpFPetu.png)

* [Architecture overview](docs/architecture.md)

## Installation and configuration

[Step by step installation instructions can be found in docs/installation.md.](docs/installation.md) In general, to run Passnote you'll need:

* PHP >= 5.4 configured with `--with-openssl` (this is normal)
* The PHP MCrypt extension
* [Phalcon PHP](http://phalconphp.com/) >= 1.3.0
* A database. The schema for MySQL is in `docs/mysql/schema.sql`, though Phalcon supports other databases
* Composer
* A web server with the document root set to the project's `public` directory and set up to pass unresolved requests through to `public/index.php`. The built-in PHP web sever can be used for development by running `php -S 127.0.0.1:8000` in the `public/` directory.

Additionally, to build and develop the interface components, you'll need:

* Node JS and NPM to run most of the interface toolkit
* [LESS](http://lesscss.org/) compiler `lessc`
* [Bower](http://bower.io/) to install Javascript and CSS dependencies: `bower`

To build the CSS, you'll need to run `make less` in the project root.

## Road map

Passnote is still in development and not all core features are implemented yet.

**Core functionality that still needs to be implemented:**

* Account settings: managing password and keys
* Unlocking objects that use a key other than a user's account key (forms required)
* Deleting and/or archiving objects

**Planned functionality:**

* Fast session expiry
* Attachments on objects
* Pagination of list view / better list or search view
* Command-line interface (console)
* Ability to share objects between users
* Bulk key change (eg. for replacing an RSA key across all objects that use it)
* A choice of encryption algorithms (eg. elliptic curve instead of RSA)


## Note about security

Passnote is designed to encrypt and manage data in a safer manner than storing your secrets in plain text. Data in Passnote is encrypted ¹, however the total security of the data you choose to store in Passnote is dependent on a number of external factors including the server environment it runs in and the passwords used for accounts and keys. Ideally Passnote shouldn't be exposed directly to the internet. If you're accessing Passnote over the internet it must be over an encrypted connection (eg. HTTPS, SSH tunnel), and the following headers should be configured at a web server level:

    Strict-Transport-Security: max-age=31536000
    Content-Security-Policy: default-src 'self'; frame-src 'none'; object-src 'none'
    X-Content-Type-Options: nosniff
    X-Frame-Options: DENY
    X-XSS-Protection: 1; mode=block

If you're serious about security though, you probably want to consider not using this project at all - it's just a toy and the security knowledge of the author is limited.

¹ Objects in Passnote are encrypted using 256 bit AES (MCRYPT_RIJNDAEL_256 in CBC mode) with a random 32 byte passphrase for each object. The random passphrase of each object is stored encrypted using an RSA key selected by the author. Keys are are generated as 4096 bit by default. The important thing to remember is that objects are only as safe as the password on the RSA key associated with them.
