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
    protected $name;

    protected $public_key;

    protected $private_key;

    protected $encrypted;
     
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
        $passphrase = $this->isEncrypted() ? $oldPassphrase : null;

        if (!$privateKey = openssl_pkey_get_private($this->private_key, $oldPassphrase)) {
            throw new \Stecman\Passnote\KeyException('Could not open private key. Wrong passphrase?');
        }

        self::storeRsaOnKey($this, $privateKey, $newPassphrase);
    }

    /**
     * Encrypt data with the public key for this key
     *
     * @param $data
     * @return string
     * @throws Stecman\Passnote\CryptException
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
     * @return string
     * @throws Stecman\Passnote\CryptException
     */
    public function decrypt($data, $passphrase=null)
    {
        // Ensure the passphrase is null for a non-encrypted key
        $passphrase = $this->isEncrypted() ? $passphrase : null;

        if (!$privateKey = openssl_pkey_get_private($this->private_key, $passphrase)) {
            throw new \Stecman\Passnote\KeyException('Could not open private key. Wrong passphrase?');
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

    public function getMaxMessageSize()
    {
        $publicKey = openssl_pkey_get_public($this->public_key);
        $keyBits = openssl_pkey_get_details($publicKey)['bits'];
        $maxBytes = (($keyBits - ($keyBits % 8)) / 8) - 11;
        openssl_free_key($publicKey);

        return $maxBytes;
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

        openssl_pkey_export($privateKey, $privateStr, $passphrase, [
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
