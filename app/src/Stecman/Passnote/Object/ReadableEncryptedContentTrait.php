<?php


namespace Stecman\Passnote\Object;

use Phalcon\DI;
use RuntimeException;
use Stecman\Passnote\Encryptor;
use Stecman\Passnote\IntegrityException;

trait ReadableEncryptedContentTrait
{
    /**
     * Passphrase for the AES encryption of $content.
     * Encrypted with the Key indicated by $key_id
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * Initialisation vector for $this->sessionKey
     *
     * @var string
     */
    protected $sessionKeyIv;

    /**
     * Encrypted content of the object
     *
     * @var string
     */
    protected $content;

    /**
     * Encrypted hash of the plain text content
     *
     * @var string
     */
    protected $checksum;

    /**
     * @return \Key
     */
    abstract public function getKey();

    /**
     * @return int
     */
    abstract public function getKeyId();

    /**
     * @return Encryptor
     */
    abstract protected function getEncryptor();

    /**
     * Fetch and decrypt the content of this object
     *
     * This does not check the integrity of the decrypted content - that is left for user code to do.
     *
     * @param $passphrase - Key passphrase
     * @return string
     */
    public function getContent($passphrase)
    {
        $crypt = $this->getEncryptor();
        $sessionKey = $this->getSessionKey($passphrase);

        return $crypt->decrypt($this->content, $sessionKey, $this->sessionKeyIv);
    }

    /**
     * Return the content checksum for this object
     *
     * @param string $data - plain text data to recompute checksum from
     * @param $passphrase - Key passphrase
     * @return string
     */
    public function isChecksumValid($data, $passphrase)
    {
        $crypt = $this->getEncryptor();
        $sessionKey = $this->getSessionKey($passphrase);

        $newHash = $this->computeChecksum($data);
        $oldHash = $crypt->decrypt($this->checksum, $sessionKey, $this->sessionKeyIv);

        return $newHash === $oldHash;
    }

    /**
     * Store the encrypted hash of the given plain text against this object
     * This is used during decryption to check that the contents hasn't changed
     *
     * @param $data - plain text data
     * @param $sessionKey - session key to encrypt checksum with
     */
    protected function storeChecksum($data, $sessionKey)
    {
        $hash = $this->computeChecksum($data);

        $crypt = $this->getEncryptor();
        $this->checksum = $crypt->encrypt($hash, $sessionKey, $this->sessionKeyIv);
    }

    /**
     * Get the checksum for the given data
     *
     * @param string $data
     * @return string
     */
    protected function computeChecksum($data)
    {
        return hash('sha256', $data, true);
    }

    /**
     * Get the plain-text session key used to encrypt $this->content
     *
     * @param $passphrase - passphrase for this object's Key
     * @return array
     * @throws RuntimeException
     */
    protected function getSessionKey($passphrase)
    {
        /** @var \Key $key */
        $key = $this->getKey();

        if (!$key) {
            throw new \RuntimeException(get_class($this) . " {$this->id} has no related key");
        }

        return $key->decrypt($this->sessionKey, $passphrase);
    }
} 
