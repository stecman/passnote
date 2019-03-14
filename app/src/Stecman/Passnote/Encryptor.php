<?php

namespace Stecman\Passnote;

class Encryptor
{
    /**
     * @var string - one of the mcrypt cipher constants
     */
    protected $cipher = 'aes-256-cbc';

    /**
     * @var int
     */
    protected $kdfIterations;

    /**
     * Options parameter for openssl encrypt/decrypt functions
     * @var int
     */
    private $options = OPENSSL_RAW_DATA;

    public function __construct($kdfIterations)
    {
        $this->kdfIterations = $kdfIterations;
    }

    public function genIv()
    {
        return openssl_random_pseudo_bytes($this->getIvSize());
    }

    public function deriveKey($password, $salt, $keyLength, $iterations = null)
    {
        $iterations = $iterations ?: $this->kdfIterations;
        $key = openssl_pbkdf2($password, $salt, $keyLength, $iterations, 'sha512');

        // This is really not ideal, but openssl's PEM key encryption doesn't
        // work if there are any null bytes in the passphrase. See the doc comment
        // for Encryptor::generateRandomBytesWithoutNulls() for more detail.
        return str_replace(hex2bin('00'), hex2bin('FF'), $key);
    }

    public function getKdfIterations()
    {
        return $this->kdfIterations;
    }

    public function encrypt($data, $key, $iv)
    {
        return openssl_encrypt($data, $this->getCipher(), $key, $this->options, $iv);
    }

    public function decrypt($data, $key, $iv)
    {
        $value = openssl_decrypt($data, $this->getCipher(), $key, $this->options, $iv);

        // Strip null padding bytes
        return rtrim($value, "\0");
    }

    public function getIvSize()
    {
        return openssl_cipher_iv_length($this->getCipher());
    }

    public function getKeySize()
    {
        // Return a roughly the right size key (maybe be larger than necessary in some cases)
        // This exists as it used to be handled by an mcrypt function
        return openssl_cipher_iv_length($this->getCipher()) * 2;
    }

    /**
     * Generate a string of random bytes that contains no null bytes
     *
     * This is required as openssl_pkey_export() and openssl_pkey_get_private() fail to
     * work correctly if the passphrase given contains any null bytes.
     *
     * @see https://gist.github.com/bertjwregeer/5300110#file-pemfunctions-md
     *
     * @param int $length
     * @return string
     */
    public function generateRandomBytesWithoutNulls($length)
    {
        do {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
        } while(strpos($bytes, hex2bin('00')) !== false || !$strong);

        return $bytes;
    }

    protected function getCipher()
    {
        return $this->cipher;
    }
}
