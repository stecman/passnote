<?php


class Key extends \Phalcon\Mvc\Model
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;

    /**
     * @var integer
     */
    public $id;
     
    /**
     * Human name for the key
     *
     * @var string
     */
    public $name;

    protected $public_key;

    protected $private_key;

    protected $encrypted;

    protected $kdf_salt;

    protected $kdf_iterations;
     
    /**
     * User who owns this key
     *
     * @var integer
     */
    public $user_id;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->belongsTo('user_id', 'User', 'id', [
            'foreignKey' => true,
            'alias' => 'Owner'
        ]);

        $this->hasMany('id', 'Object', 'key_id', [
            'foreignKey' => true,
            'alias' => 'Objects'
        ]);
    }

    public static function generate($passphrase, $bits = 4096)
    {
        $key = new Key();

        if (!$passphrase) {
            $passphrase = null;
            $key->encrypted = false;
        } else {
            $key->encrypted = true;
        }

        $privateKey = self::generatePrivateKey($bits);
        self::storeRsaOnKey($key, $privateKey, $passphrase);
        openssl_free_key($privateKey);

        return $key;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function changePassphrase($oldPassphrase, $newPassphrase)
    {
        // Ensure the passphrase is null for a non-encrypted key
        $oldPassphrase = $this->isEncrypted() ? $oldPassphrase : null;

        if ($oldPassphrase !== null) {
            $oldPassphrase = $this->getDerivedKey($oldPassphrase);
        }

        if (!$privateKey = openssl_pkey_get_private($this->private_key, $oldPassphrase)) {
            throw new \Stecman\Passnote\KeyException('Could not open private key. Wrong passphrase? ' . openssl_error_string());
        }

        self::storeRsaOnKey($this, $privateKey, $newPassphrase);
    }

    /**
     * Encrypt data with the public key for this key
     *
     * @param $data
     * @throws Stecman\Passnote\MessageSizeException
     * @throws Stecman\Passnote\CryptException
     * @return string
     */
    public function encrypt($data)
    {
        $publicKey = openssl_pkey_get_public($this->public_key);

        $maxBytes = $this->getMaxMessageSize();
        if (strlen($data) > $maxBytes) {
            throw new \Stecman\Passnote\MessageSizeException('Message is too long ('.strlen($data).' bytes). Maximum message length for this key is '.$maxBytes.' bytes.');
        }

        $encrypted = null;

        $success = openssl_public_encrypt($data, $encrypted, $publicKey);
        openssl_free_key($publicKey);

        if (!$success) {
            throw new \Stecman\Passnote\CryptException('Encryption failed: '. openssl_error_string());
        }

        return $encrypted;
    }

    /**
     * Decrypt data encrypted the public key for this key
     *
     * @param $data
     * @param string $passphrase
     * @throws Stecman\Passnote\KeyException
     * @throws Stecman\Passnote\CryptException
     * @return string
     */
    public function decrypt($data, $passphrase = null)
    {
        // Ensure the passphrase is null for a non-encrypted key
        $passphrase = $this->isEncrypted() ? $passphrase : null;

        if ($passphrase !== null) {
            $passphrase = $this->getDerivedKey($passphrase);
        }

        if (!$privateKey = openssl_pkey_get_private($this->private_key, $passphrase)) {
            throw new \Stecman\Passnote\KeyException('Could not open private key. Wrong passphrase? ' . openssl_error_string());
        }
        $decrypted = null;

        $success = openssl_private_decrypt($data, $decrypted, $privateKey);
        openssl_pkey_free($privateKey);

        if (!$success) {
            throw new \Stecman\Passnote\CryptException('Decryption failed: '. openssl_error_string());
        }

        return $decrypted;
    }

    public function isEncrypted()
    {
        $firstLine = strstr($this->private_key, "\n", true);
        return strpos($firstLine, 'ENCRYPTED PRIVATE KEY') !== false;
    }

    /**
     * Get the maximum number of bytes that this key can encrypt
     *
     * @return int
     */
    public function getMaxMessageSize()
    {
        $publicKey = openssl_pkey_get_public($this->public_key);
        $keyBits = openssl_pkey_get_details($publicKey)['bits'];
        $maxBytes = (($keyBits - ($keyBits % 8)) / 8) - 11;
        openssl_free_key($publicKey);

        return $maxBytes;
    }

    /**
     * Using the stored key derivation properties, derive a key from a passphrase
     *
     * @param string $passphrase
     * @return string
     */
    protected function getDerivedKey($passphrase)
    {
        $crypt = \Phalcon\DI::getDefault()->get('encryptor');

        if ($this->kdf_salt !== null) {
            $derivedKey = $crypt->deriveKey($passphrase, $this->kdf_salt, $crypt->getKeySize(), $this->kdf_iterations);
        } else {
            $derivedKey = $passphrase;
        }

        return $derivedKey;
    }

    /**
     * Put a private key resource in a Key object
     *
     * @param Key $key
     * @param $privateKey
     * @param $passphrase
     */
    protected static function storeRsaOnKey(Key $key, $privateKey, $passphrase)
    {
        $privateStr = null;
        $crypt = \Phalcon\DI::getDefault()->get('encryptor');

        // Perform key derivation
        if ($passphrase !== null) {
            $key->kdf_salt = openssl_random_pseudo_bytes(32);
            $key->kdf_iterations = $crypt->getKdfIterations();
            $derivedKey = $crypt->deriveKey($passphrase, $key->kdf_salt, $crypt->getKeySize());
        } else {
            $key->kdf_salt = null;
            $key->kdf_iterations = null;
            $derivedKey = null;
        }

        openssl_pkey_export($privateKey, $privateStr, $derivedKey, [
            'encrypt_key' => true,
            'encrypt_key_cipher' => OPENSSL_CIPHER_AES_256_CBC
        ]);

        $key->private_key = $privateStr;
        $key->public_key = self::getPublicCertificate($privateKey);
    }

    /**
     * Return a certificate with a public key for $privateKey
     *
     * @param $privateKey
     * @return string
     */
    protected static function getPublicCertificate($privateKey)
    {
        $out = null;

        $csr = openssl_csr_new([], $privateKey);
        $cert = openssl_csr_sign($csr, null, $privateKey, 365);
        openssl_x509_export($cert, $out);

        return $out;
    }

    /**
     * Generate a new RSA private key resource
     *
     * @param $bits
     * @return resource
     */
    protected static function generatePrivateKey($bits)
    {
        $privateKey = openssl_pkey_new([
            'digest_alg' => 'sha512',
            'private_key_bits' => (int) $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'encrypt_key' => true
        ]);

        return $privateKey;
    }

}
