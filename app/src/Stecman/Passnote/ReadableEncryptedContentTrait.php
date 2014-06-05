<?php


namespace Stecman\Passnote;

use RuntimeException;

trait ReadableEncryptedContentTrait
{
    /**
     * Passphrase for the AES encryption of $content.
     * Encrypted with the Key indicated by $key_id
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     * Initialisation vector for $this->encryptionKey
     *
     * @var string
     */
    protected $encryptionKeyIv;

    /**
     * Encrypted content of the object
     *
     * @var string
     */
    protected $content;

    public abstract function getKey();

    public abstract function getKeyId();

    /**
     * Fetch and decrypt the content of this object
     *
     * @param $passphrase - Key passphrase
     * @return string
     */
    public function getContent($passphrase)
    {
        $crypt = new Encryptor();
        $blub = $this->getEncryptionKey($passphrase);

        return $crypt->decrypt($this->content, $blub, $this->encryptionKeyIv);
    }

    /**
     * Get the plain-text blub used to encrypt $this->content
     *
     * @param $passphrase - passphrase for this object's Key
     * @return array
     * @throws RuntimeException
     */
    protected function getEncryptionKey($passphrase)
    {
        /** @var \Key $key */
        $key = $this->getKey();

        if (!$key) {
            throw new \RuntimeException(get_class($this) . " {$this->id} has no related key");
        }

        return $key->decrypt($this->encryptionKey, $passphrase);
    }
} 