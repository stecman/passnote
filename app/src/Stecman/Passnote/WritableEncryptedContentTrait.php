<?php


namespace Stecman\Passnote;

trait WritableEncryptedContentTrait
{
    use ReadableEncryptedContentTrait;

    /**
     * Set the plain-text content of this object
     *
     * This method handles encryption of the content
     *
     * @param string $plainText
     */
    public function setContent($plainText)
    {
        $crypt = new Encryptor();
        $blub = $this->generateEncryptionKey();
        $iv = $crypt->genIv();

        $this->setEncryptionKey($blub);
        $this->checksum = sha1($plainText);
        $this->encryptionKeyIv = $iv;
        $this->content = $crypt->encrypt($plainText, $blub, $iv);
    }

    protected function setEncryptionKey($string)
    {
        /** @var \Key $key */
        $key = $this->key;

        if (!$key) {
            throw new \RuntimeException("Object {$this->id} has no related key");
        }

        $this->encryptionKey = $key->encrypt($string);
    }
} 