<?php


class UserTest extends PHPUnit_Framework_TestCase
{
    public function testSetPassword()
    {
        $password = 'I am a cool password';

        $user = new User();
        $user->dangerouslySetPassword($password);

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
        $passphrase = $user->dangerouslyRegenerateAccountKeyPassphrase($password);
        $this->assertEquals($passphrase, $user->getAccountKeyPassphrase($password));
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
        $this->assertFalse($user->validateSessionKey($key));
    }

    public function testRecryptDefaultKey()
    {
        $password = 'test';
        $newPassword = 'another-password';
        $data = openssl_random_pseudo_bytes(64);

        $user = new User();
        $passphrase = $user->dangerouslyRegenerateAccountKeyPassphrase($password);

        $key = $user->accountKey = Key::generate($passphrase);
        $encrypted = $key->encrypt($data);

        $user->recryptAccountKey($password, $newPassword);
        $passphrase = $user->getAccountKeyPassphrase($newPassword);

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
        $defaultKeyPassphrase = $user->dangerouslyRegenerateAccountKeyPassphrase($firstPassword);
        $key = Key::generate($defaultKeyPassphrase, 1024);
        $user->accountKey = $key;

        // Encrypt some data
        $encryptedData = $user->getAccountKey()->encrypt($data);

        // Change user's password
        // This must update the password on the default key and OTP key as well
        $user->changePassword($firstPassword, $secondPassword);

        // Decrypt data
        $newKeyPassphrase = $user->getAccountKeyPassphrase($secondPassword);
        $decrypted = $user->getAccountKey()->decrypt($encryptedData, $newKeyPassphrase);

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
        $this->assertTrue($user->getAccountKey() instanceof Key);
        $this->assertTrue($user->validation());

        $data = '~super fire acid sheep was here~';
        $encrypted = $user->getAccountKey()->encrypt($data);
        $decrypted = $user->getAccountKey()->decrypt($encrypted, $user->getAccountKeyPassphrase($password));

        $this->assertEquals($data, $decrypted);
    }
}
