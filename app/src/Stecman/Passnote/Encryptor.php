<?php

namespace Stecman\Passnote;

class Encryptor
{
    public function genIv()
    {
        $iv_size = mcrypt_get_iv_size($this->getCipher(), $this->getMcryptMode());
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

    protected function getCipher()
    {
        return MCRYPT_RIJNDAEL_256;
    }

    protected function getMcryptMode()
    {
        return MCRYPT_MODE_CBC;
    }
}