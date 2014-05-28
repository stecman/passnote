# Passnote Architechture

This document describes the relationships between data objects in Passnote, and generally how it is designed to work. I wrote this mostly because I kept getting confused about the big picture when I was implementing details.


## Purpose

Passnote is designed for storing private data in a reasonably safe way, and making it accessible over the internet to the owner with sufficiently stringent authentication.


## Model

### User

The owner of Objects and Keys. Most probably a person. Every user must have at least one Key, with one Key designated as their *Account Key*.

### Key

An RSA key pair used when encrpyting and decrypting an Object. Encrpyted using a password decided by the user. As the payload size of RSA is limited by key size, Keys are not used directly to encrypt data, but rather a random passphrase we'll call a *Blub*.

### Object

A piece of encrypted data (a secret). Is encrypted using a Key, and is owned by a User.

### Object Version

An historic version of an Object. Belongs to an Object, and is encrypted using the same key as that Object (the 'master'). Object Versions cannot be changed.

### Account Key

A Key that is used when no other key is selected. The Account Key implicitly has the same password as the User's account (though the actual passphrase is random), and is unlocked when a user authenticates. The Account Key remains unlocked during the User's session, allowing them to decrpyt Objects without typing in a password for the Key. This is intended to provide a good compromise between security and ease of access.

### Blub

A random string of bytes used to encrypt the data in an Object. Each Object has its own Blub. The Blub is not known to the user, and is stored encrypted (by a Key) in the database along with the Object it encrpyted.

A Blub is really an 'encryption key' or 'object passphrase'. The term 'blub' has been used to avoid ambiguity or confusion, as the words 'key', 'passphrase' and 'password' are already used to identify other components.


## Process overview

* An Object needs a Blub to be decrypted
* A Blub needs a Key to be decrypted
* A Key needs a password to be decrypted

### Encryption

1. User submits plain-text data and specifies a Key to encrypt with.
2. A Blub of the maximum length for the Key is generated.
3. The User's plain-text data is encrypted using AES with the Blub as the passphrase for encryption.
4. The Blub is encrypted using the Key
5. The encrypted Blub and data are stored in the database in an Object

Things of note:

* The user does not need to provide a password to encrypt data

### Decryption

1. User requests the contents of an Object and provides the password for the Key associated with that Object
2. The password is used to unlock the Key
3. The Key is used to decrypt the Object's Blub
4. The Object content is decrypted using the Blub and sent back to the user

In the case the User is using their Account Key, a password would not be sent with the request for content. Instead, step 2 would have effectively occurred at the time the User logged in.

### Account key passphrase

When a user logs in, their Account Key is unlocked. This is done in the following manner:

1. User sends password
2. The password is used to decrypt a passphrase stored with the User
3. The decrypted passphrase is used to unlock the Account Key

The additional encrpyted passphrase for Account Keys is necessary to avoid storing the user's password in the session in some form, since the Account Key needs to be unlockable using it at any point during the session. The encrypted passphrase might be an unecessarily complex way of solving this - simply hashing and mangling the user's password to use as a key seems like it might get the job done in a cleaner way.