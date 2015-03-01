# Getting Passnote running

This file contains a brief overview of how to get Passnote running. This may be simplified in future, however the project is fairly new at the moment and ease of installation hasn't been polished at all. If you need any help at the moment, email is your best bet.


## Initial setup

**Install the Phalcon PHP extension** at v1.3.2. [The Phalcon docs contain installation instructions](http://docs.phalconphp.com/en/latest/reference/install.html). You can confirm installation by running:

    php -m | grep phalcon

or

    php -r 'echo extension_loaded("phalcon") ? "Phalcon " . phpversion("phalcon") . " is installed.\n"  : "Phalcon not installed.\n";'

**Get the code**

    git clone git@github.com:stecman/passnote.git
    cd passnote/

**Install remaining dependencies**

    composer install

**Create a database and import the Passnote schema**

    # Login and run the query "CREATE DATABASE passnote", then:
    mysql -u root -p passnote < docs/mysql/schema.sql
    
    # Once database access is configured (below), apply the incremental schema updates:
    ./bin/task.php migrate run


## Configuring

Set the configuration for your environment by creating a file in the project root called `config.php`. The array returned by this is merged into the main config from `app/config/config.php`. A basic environment config will probably be just your database details:

```php
// Dev mode is off by default
// Enable it if you want to see error messages instead of a generic error page when there's a problem.
const DEV_MODE = false;

return [
    'database' => [
        'host'        => 'localhost',
        'username'    => 'count.d',
        'password'    => 'one password, ah ah ahh',
        'dbname'      => 'passnote',
    ]
];
```


## Getting or compiling the CSS

If you just want to get Passnote running and don't care about compiling LESS, a compiled copy of the project's CSS can be found in this [Passnote CSS gist](https://gist.github.com/stecman/c60a7b645104c565a517).

To compile the CSS yourself, you'll need to first install the build tools. To do this, you'll need NodeJS and npm installed.

    # Step 1: install build tools
    npm install -g bower
    npm install -g less

    # Step 2: install dependencies and compile
    make install
    make less


## Creating an account

At this stage the project should run correctly. You can test this by making an account using the command line (this is currently the only way to create accounts):

    ./bin/task.php users create you@example.com

This will prompt for a password, generate an account key, and print a URL representing the account's time-based one-time password key (TOTP). You can use an app like [Google Authenticator](https://support.google.com/accounts/answer/1066447) as a TOTP manager, or if you're just playing with the project, you can use something like [TOTP debugger](https://google-authenticator.googlecode.com/git/libpam/totp.html) instead.

To get the OTP key into Google Authenticator or similar, you can generate a QR code containing the URL and scan it with the application. Assuming you've already created an account above:

    # Using qrencode and Imagemagick's display to show the OTP URL as a QR code
    # You could use any combination of QR code generator and display program
    ./bin/task.php users regenerate-otp you@example.com | qrencode -o - | display -


## Starting a server and logging in

You can run the application using PHP's built-in web server to get started quickly. For more permanent installations, use Nginx or Apache.

    cd public
    php -S 127.0.0.1:8000

Then go to `http://localhost:8000` in a browser, and (if everything worked) you should see the passnote login screen:

![Passnote login screen](https://i.imgur.com/vItWa7z.png)
