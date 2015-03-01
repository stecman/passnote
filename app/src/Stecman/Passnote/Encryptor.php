<?php

namespace Stecman\Passnote;

class Encryptor
{
    /**
     * @var string - one of the mcrypt cipher constants
     */
    protected $cipher = MCRYPT_RIJNDAEL_256;

    /**
     * @var string - one of the MCRYPT_MODE_* constants
     */
    protected $mode = MCRYPT_MODE_CBC;

    /**
     * @var int
     */
    protected $kdfIterations;

    public function __construct($kdfIterations)
    {
        $this->kdfIterations = $kdfIterations;
    }

    public function genIv()
    {
        $iv_size = $this->getIvSize();
        return mcrypt_create_iv($iv_size, MCRYPT_RAND);
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
        return mcrypt_encrypt($this->getCipher(), $key, $data, $this->getMcryptMode(), $iv);
    }

    public function decrypt($data, $key, $iv)
    {
        $value = mcrypt_decrypt($this->getCipher(), $key, $data, $this->getMcryptMode(), $iv);

        // Strip null padding bytes
        return rtrim($value, "\0");
    }

    public function getIvSize()
    {
        return mcrypt_get_iv_size($this->getCipher(), $this->getMcryptMode());
    }

    public function getKeySize()
    {
        return mcrypt_get_key_size($this->getCipher(), $this->getMcryptMode());
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

    protected function getMcryptMode()
    {
        return $this->mode;
    }
}
