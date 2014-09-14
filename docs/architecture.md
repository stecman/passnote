# Passnote Architechture

This document describes the relationships between data objects in Passnote, and generally how it is designed to work. I wrote this mostly because I kept getting confused about the big picture when I was implementing details.


## Purpose

Passnote is designed for storing private data in a reasonably safe way, and making it accessible over the internet to the owner (if required) with sufficiently stringent authentication.


## Model

### User

The owner of Objects and Keys. Most probably a person. Every user must have at least one Key, with one Key designated as their *Account Key*.

### Key

An RSA key pair used when encrypting and decrypting an Object. Encrypted using a password decided by the user. As the payload size of RSA is limited by key size, Keys are not used directly to encrypt data; the Key is used to encrypt/decrypt a random passphrase which we'll call a *Blub*. To view an object, the password for the associated Key must be entered at the time of access.

### Blub

A random string of bytes used as the passphrase for symmetrically encrypting the data in an Object. Each Object has its own Blub. The Blub is not known to the user and is stored encrypted (by a Key) in the database along with the Object it encrypted.

A Blub is really an 'encryption key' or 'object passphrase'. The term 'blub' has been used to avoid ambiguity or confusion, as the words 'key', 'passphrase' and 'password' are already used to identify other components.

### Object

A piece of encrypted data (a secret). An object is encrypted symmetrically with a *blub* as the passphrase.

### Object Version

An historic version of an Object. Belongs to an Object and is encrypted using the same key as that Object (the 'master'). Object Versions cannot be changed.

### Account Key

A Key that is implicitly unlocked for a session when a user logins in, instead of requiring a password to unlock upon accessing an Object. The user doesn't know the actual password for their Account Key as it is managed automatically, but it can be indirectly unlocked using their account password. This is intended to provide a compromise between security and ease of access.


## Process overview

* An Object needs a Blub to be decrypted
* A Blub needs a Key to be decrypted
* A Key needs a password to be decrypted

### Encryption

1. User submits plain-text data and specifies a Key to encrypt with.
2. A Blub of the maximum length for the Key is generated.
3. The User's plain-text data is encrypted using AES with the Blub as the passphrase for encryption.
4. The Blub is encrypted using the public side of the Key
5. The encrypted Blub and data are stored in the database in an Object record

Things of note:

* The user does not need to provide a password to encrypt data, as it is encrypted using the public RSA key of the selected Key.

### Decryption

1. User requests the contents of an Object and provides the password for the Key associated with that Object
2. The password is used to unlock the Key
3. The Key is used to decrypt the Object's Blub
4. The Object content is decrypted using the Blub and sent back to the user

In the case the User is using their Account Key, a password would not be sent with the request for content. Instead, step 2 would have effectively occurred at the time the User logged in.

### Account key passphrase

When a user logs in, their Account Key is unlocked. This is done in the following manner:

1. User submits login form which includes their password
2. The user password is used to decrypt a randomly generated passphrase that has been stored against the User
3. The decrypted passphrase is used to unlock the Account Key

The additional encrypted passphrase for Account Keys is necessary to avoid storing the user's password in the session in some form, since the Account Key needs to be unlockable using it at any point during the session.