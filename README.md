# **Passnote**: encrypted data store

Passnote is an application for storing secrets.

![Passnote screenshot](https://i.imgur.com/OpFPetu.png)

* [Architecture overview](docs/architecture.md)

## Installation and configuration

[Step by step installation instructions can be found in docs/installation.md.](docs/installation.md) In general, to run Passnote you'll need:

* PHP 7.0 configured with `--with-openssl` (this is normal)
* [Phalcon PHP](http://phalconphp.com/) >= 3.4.0
* A database. The schema for MySQL is in `docs/mysql/schema.sql`, though Phalcon supports other databases
* Composer
* A web server with the document root set to the project's `public` directory and set up to pass unresolved requests through to `public/index.php`. The built-in PHP web sever can be used for development by running `php -S 127.0.0.1:8000` in the `public/` directory.

Additionally, to build and develop the interface components, you'll need:

* Node JS and NPM to run most of the interface toolkit
* [LESS](http://lesscss.org/) compiler `lessc`
* [Bower](http://bower.io/) to install JavaScript and CSS dependencies: `bower`

To build the CSS, you'll need to run `make less` in the project root.

## Updating

To update an existing install, pull down the latest code from master and run any database updates:

    ./bin/task.php migrate run

Also see [docs/upgrade-notes.md](docs/upgrade-notes.md) for any extra steps required.

## Road map

Passnote is stable, but more features are planned:

**Core functionality that still needs to be implemented:**

* Account settings: managing password and keys
* Unlocking objects that use a key other than a user's account key
* Archiving objects (instead of just deleting)

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

You probably want to consider not using this project other than as a Phalcon PHP demo - it's just a toy and the security knowledge of the author is limited.

¹ Objects in Passnote are each encrypted using 256 bit AES in CBC mode with a random 32 byte key. The random key for of each object is encrypted using an RSA key and stored alongside the encrypted content. Generated RSA keys are 4096 bit by default. Remember that objects are only as safe as the password on the RSA key associated with them.
