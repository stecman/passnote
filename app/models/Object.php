<?php


class Object extends \Phalcon\Mvc\Model
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;

    public $id;
     
    /**
     * Title of the object
     * @var string
     */
    public $title;

    /**
     * Description if the object needs one.
     *
     * Since the object's content is encrypted, it is not possible to search in content.
     * This field is intended for providing context/information without giving away secrets.
     *
     * @var string
     */
    public $description;
     
    /**
     * Encrypted content of the object
     *
     * @var string
     */
    protected $content;
     
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
     * RSA key pair used to encrypt $key
     *
     * @var integer
     */
    public $key_id;
     
    /**
     * The user who owns this object
     *
     * @var integer
     */
    public $user_id;
     
    /**
     * The object this object belongs to
     *
     * @var integer
     */
    public $parent_id;
     
    /**
     * Whether $content should be considered as binary or text
     *
     * @var boolean
     */
    public $isBinary = false;

    /**
     * SHA1 hash of the unencrypted content
     *
     * @var string
     */
    public $checksum;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('key_id', 'Key', 'id');

        $this->hasMany('id', 'Object', 'parent_id', [
            'alias' => 'Children'
        ]);

        $this->hasMany('id', 'ObjectVersion', 'object_id', [
            'alias' => 'Versions'
        ]);
    }

    /**
     * Set the plain-text content of this object
     *
     * This method handles encryption of the content
     *
     * @param string $plainText
     */
    public function setContent($plainText)
    {
        $crypt = new \Stecman\Passnote\Encryptor();
        $blub = $this->generateEncryptionKey();
        $iv = $crypt->genIv();

        $this->setEncryptionKey($blub);
        $this->checksum = sha1($plainText);
        $this->encryptionKeyIv = $iv;
        $this->content = $crypt->encrypt($plainText, $blub, $iv);
    }

    /**
     * Fetch and decrypt the content of this object
     *
     * @param $passphrase - Key passphrase
     * @return string
     */
    public function getContent($passphrase)
    {
        $crypt = new \Stecman\Passnote\Encryptor();
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
        $key = $this->key;

        if (!$key) {
            throw new \RuntimeException("Object {$this->id} has no related key");
        }

        return $key->decrypt($this->encryptionKey, $passphrase);
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

    protected function beforeUpdate()
    {
        $this->saveVersion();
    }

    /**
     * Create version of this object from the current state of the database
     */
    protected function saveVersion()
    {
        $previous = self::findFirst($this->id);

        if ($previous && $previous->checksum !== $this->checksum) {
            $version = ObjectVersion::versionFromObject($previous);
            $version->setEncryptionKey($this->encryptionKey, $this->encryptionKeyIv);
            $version->create();
        }
    }

    /**
     * Generate a new random string to encrypt content with
     *
     * @throws RuntimeException
     * @return string - plain text encryption key
     */
    protected function generateEncryptionKey()
    {
        /** @var \Key $key */
        $key = $this->key;

        if (!$key) {
            throw new \RuntimeException("Object {$this->id} has no related key");
        }

        // Length of blub that will fit inside $this->key
        $length = min( \Stecman\Passnote\Encryptor::MAX_KEY_SIZE, $key->getMaxMessageSize() );

        return openssl_random_pseudo_bytes($length);
    }
     
}
