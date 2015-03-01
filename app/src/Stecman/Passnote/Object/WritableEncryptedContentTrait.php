<?php


namespace Stecman\Passnote\Object;

trait WritableEncryptedContentTrait
{
    use ReadableEncryptedContentTrait;

    /**
     * Generate a new random string to encrypt content with
     *
     * @return string - plain text encryption key
     */
    abstract protected function generateSessionKey();

    /**
     * Set the plain-text content of this object
     *
     * This method handles encryption of the content
     *
     * @param string $plainText
     */
    public function setContent($plainText)
    {
        $crypt = $this->getEncryptor();

        $sessionKey = $this->generateSessionKey();
        $this->setSessionKey($sessionKey);
        $this->sessionKeyIv = $crypt->genIv();

        $this->content = $crypt->encrypt($plainText, $sessionKey, $this->sessionKeyIv);
        $this->storeChecksum($plainText, $sessionKey);
    }

    protected function setSessionKey($string)
    {
        /** @var \Key $key */
        $key = $this->key;

        if (!$key) {
            throw new \RuntimeException("Object {$this->id} has no related key");
        }

        $this->sessionKey = $key->encrypt($string);
    }
} 
