<?php


class KeyTest extends PHPUnit_Framework_TestCase
{
    const TEST_KEY_SIZE = 512;

    public function testRsaEncryption()
    {
        $passphrase = 'johntheripper';
        $data = 'The horse had forty three legs. One had a mild rash.';

        $key = Key::generate($passphrase, self::TEST_KEY_SIZE);

        $encrypted = $key->encrypt($data);
        $decrypted = $key->decrypt($encrypted, $passphrase);

        $this->assertEquals($data, $decrypted);
    }

    public function testPassphraselessRsaEncryption()
    {
        $data = 'Some domestic cats can smell as far as 7 miles.';
        $key = Key::generate(null, self::TEST_KEY_SIZE);

        $encrypted = $key->encrypt($data);
        $decrypted = $key->decrypt($encrypted);

        $this->assertEquals($data, $decrypted);
    }

    public function testIsEncrypted()
    {
        $key = Key::generate('password', self::TEST_KEY_SIZE);
        $this->assertTrue($key->isEncrypted());

        $key = Key::generate(null, self::TEST_KEY_SIZE);
        $this->assertFalse($key->isEncrypted());
    }

    public function testPassphraseChange()
    {
        $passphrase = 'johntheripper';
        $secondPassphrase = 'on the bayou';
        $data = 'Super secret message 3000';
        $key = Key::generate($passphrase, self::TEST_KEY_SIZE);

        // Encrypt and change the passphrase
        $encrypted = $key->encrypt($data);
        $key->changePassphrase($passphrase, $secondPassphrase);

        // Decrypt with the new passphrase
        $decrypted = $key->decrypt($encrypted, $secondPassphrase);
        $this->assertEquals($data, $decrypted);

        // Ensure the old passphrase no longer works
        try {
            $decrypted = $key->decrypt($encrypted, $passphrase);
            $this->assertNotEquals($data, $decrypted);
        } catch (\Stecman\Passnote\KeyException $e) {
            // Decryption with the old passphrase failed.
            // This is the correct behaviour
        }
    }

    public function testMaxMessageLength()
    {
        $key = Key::generate('jazzosaurus7', 1024);
        $key->encrypt(openssl_random_pseudo_bytes($key->getMaxMessageSize()));
    }

    /**
     * @expectedException \Stecman\Passnote\MessageSizeException
     */
    public function testExceedMaxMessageLength()
    {
        $key = Key::generate('smith', 386);
        $key->encrypt(openssl_random_pseudo_bytes($key->getMaxMessageSize() + 1));
    }
}
