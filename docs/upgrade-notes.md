# Passnote upgrade notes

## March 2019

### No more mcrypt

The use of mcrypt has been replaced with openssl encrypt/decrypt functions. As
the encryption under mcrypt was non-standard AES, data needs to be migrated to
work with the new code. This migration needs to be run manually for each user
and can be quite slow depending on your configured pkdf iterations and number
of objects and object versions.

If you've already updated to PHP 7.2 or 7.3 where mcrypt has been removed
you'll need to install the extension via pecl:

```sh
# Install PHP 7.3 compatible mcrypt extension if needed
# See https://lukasmestan.com/install-mcrypt-extension-in-php7-2/ for more detailed instructions
sudo pecl install mcrypt-1.0.2
```

The following script can then be run to re-encrypt every object and version.
Note that it requires the users password to run as it needs to decrypt
everything using mcrypt and encrypt again using openssl:

```sh
./bin/task.php migrate mcrypt-openssl <email>
```

## March 2015

### Checksum changes

Checksums are now an encrypted SHA256 instead of an unencrypted SHA1. They are used for checking integrity rather than
checking for changes. Any time the save button is pressed, a new version will be written - regardless of if it has actually
changed.

### Null padding change in 5.6

If you're running PHP 5.6, the behaviour of undersized keys has changed; these are no longer null padded and an warning
is trigger if the key size is not correct. This can be worked around by adding the following to `Encryptor::decrypt`:

```
if (strlen($key) < 32) {
    // Pad with null bytes if the key is too short
    // You may need to adjust the '32' here to 16, 24, or 32 (the first one that is longer than your passphrase)
    $key = str_pad($key, 32, hex2bin('00'), STR_PAD_RIGHT);
}
```
