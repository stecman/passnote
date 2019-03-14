<?php

use Stecman\Passnote\Encryptor;

/**
 * Check that the wrapper around encryption calls are working at all
 */
class EncryptorTest extends PHPUnit_Framework_TestCase
{
    function testEncryptDecrypt()
    {
        $encryptor = new Encryptor(100);
        $key = md5('helloworld');
        $iv = $encryptor->genIv();

        $plainText = 'What is love? Baby don\'t hurt me...no more';
        $cipherText = $encryptor->encrypt($plainText, $key, $iv);

        $this->assertEquals($plainText, $encryptor->decrypt($cipherText, $key, $iv));
    }

    function testEncryptDecryptWithKeyDerivation()
    {
        $encryptor = new Encryptor(100);
        $key = $encryptor->deriveKey('helloworld', 'iamthesalt', $encryptor->getKeySize());
        $iv = $encryptor->genIv();

        $plainText = 'Never gonna give you up, never gonna let you down...';
        $cipherText = $encryptor->encrypt($plainText, $key, $iv);

        $this->assertEquals($plainText, $encryptor->decrypt($cipherText, $key, $iv));
    }
}
