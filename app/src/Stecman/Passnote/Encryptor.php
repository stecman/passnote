<?php

namespace Stecman\Passnote;

class Encryptor
{
    const MAX_KEY_SIZE = 32;

    public function genIv()
    {
        $iv_size = $this->getIvSize();
        return mcrypt_create_iv($iv_size, MCRYPT_RAND);
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

    protected function getCipher()
    {
        return MCRYPT_RIJNDAEL_256;
    }

    protected function getMcryptMode()
    {
        return MCRYPT_MODE_CBC;
    }
}