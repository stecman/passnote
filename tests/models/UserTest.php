<?php


class UserTest extends PHPUnit_Framework_TestCase
{
    public function testSetPassword()
    {
        $password = 'I am a cool password';

        $user = new User();
        $user->setPassword($password);

        $this->assertTrue($user->validatePassword($password));
    }

    public function testSetOtpKey()
    {
        $otpKey = 'helloOtpKey';
        $password = 'password123';
        $secondPassword = 'helloworld';

        $user = new User();
        $user->setOtpKey($otpKey, $password);

        $this->assertEquals($otpKey, $user->getOtpKey($password));

        // Test recrypt OTP key
        $user->recryptOtpKey($password, $secondPassword);
        $this->assertEquals($otpKey, $user->getOtpKey($secondPassword));
        $this->assertNotEquals($otpKey, $user->getOtpKey($password));
    }

    public function testDefaultKeyPassphrase()
    {
        $password = 'flamingSeagull43';

        $user = new User();
        $passphrase = $user->setupDefaultKeyPassphrase($password);
        $this->assertEquals($passphrase, $user->getDefaultKeyPassphrase($password));
        $this->assertNotEmpty($passphrase);
    }

    public function testSessionKey()
    {
        $user = new User();
        $user->regenerateSessionKey();

        $key = $user->getSessionKey();
        $this->assertNotEmpty($key);
        $this->assertTrue($user->validateSessionKey($key));

        $user->regenerateSessionKey();
        $this->assertNotEquals($key, $user->getSessionKey());
    }

    public function testRecryptDefaultKey()
    {
        $password = 'test';
        $secondPassword = 'another-password';
        $data = openssl_random_pseudo_bytes(64);

        $user = new User();
        $passphrase = $user->setupDefaultKeyPassphrase($password);

        $key = $user->defaultKey = Key::generate($passphrase);
        $encrypted = $key->encrypt($data);

        $user->recryptDefaultKey($password, $secondPassword);
        $passphrase = $user->getDefaultKeyPassphrase($secondPassword);

        $decrypted = $key->decrypt($encrypted, $passphrase);

        $this->assertEquals($data, $decrypted);
    }

    public function testMultiChangePassword()
    {
        $firstPassword = 'hello world';
        $secondPassword = 'goodbye sun';
        $otpKey = 'I am a test key';
        $data = openssl_random_pseudo_bytes(117);

        // Set up a user
        $user = new User();
        $user->setOtpKey($otpKey, $firstPassword);

        // Setup a key
        $defaultKeyPassphrase = $user->setupDefaultKeyPassphrase($firstPassword);
        $key = Key::generate($defaultKeyPassphrase, 1024);
        $user->defaultKey = $key;

        // Encrypt some data
        $encryptedData = $user->getDefaultKey()->encrypt($data);

        // Change user's password
        // This should update the password on the default key and OTP key
        $user->changePassword($firstPassword, $secondPassword);

        // Decrypt data
        $newKeyPassphrase = $user->getDefaultKeyPassphrase($secondPassword);
        $decrypted = $user->getDefaultKey()->decrypt($encryptedData, $newKeyPassphrase);

        // Default Key passphrase should have changed and remain valid
        $this->assertNotEquals($newKeyPassphrase, $defaultKeyPassphrase);
        $this->assertEquals($data, $decrypted);

        // OTP key should have been encrypted with the new password
        $this->assertEquals($otpKey, $user->getOtpKey($secondPassword));
    }

    public function testCreateUserHelper()
    {
        $password = 'passWordy9000';
        $email = 'john@example.com';

        $user = User::createWithKeys($email, $password);

        $this->assertTrue($user instanceof User);
        $this->assertTrue($user->getDefaultKey() instanceof Key);
        $this->assertTrue($user->validation());

        $data = '~super fire acid sheep was here~';
        $encrypted = $user->getDefaultKey()->encrypt($data);
        $decrypted = $user->getDefaultKey()->decrypt($encrypted, $user->getDefaultKeyPassphrase($password));

        $this->assertEquals($data, $decrypted);
    }
}