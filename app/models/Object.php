<?php


use Stecman\Passnote\ReadableEncryptedContent;

class Object extends \Phalcon\Mvc\Model implements ReadableEncryptedContent
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;
    use \Stecman\Passnote\WritableEncryptedContentTrait;

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
            'alias' => 'Versions',
            'reusable' => true
        ]);
    }

    /**
     * @return Key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getKeyId()
    {
        return $this->key_id;
    }

    /**
     * Get the raw encrypted content of this object
     *
     * @param ObjectVersion $version
     * @return string - encrypted bytes
     */
    public function copyStateToVersion(ObjectVersion $version)
    {
        $version->setEncryptionKey($this->encryptionKey, $this->encryptionKeyIv);
        $version->setEncryptedContent($this->content);
        $version->isBinary = $this->isBinary;
        $version->checksum = $this->checksum;

        return $version;
    }

    protected function beforeUpdate()
    {
        $this->saveVersion();

        // Update the created date, since this is effectively a new object now (the old one is the version)
        $this->created = date('Y-m-d H:i:s');
    }

    /**
     * Create version of this object from the current state of the database
     */
    protected function saveVersion()
    {
        $previous = self::findFirst($this->id);

        if ($previous && $previous->checksum !== $this->checksum) {
            $version = ObjectVersion::versionFromObject($previous);
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
