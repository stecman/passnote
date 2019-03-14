<?php


use Stecman\Passnote\Object\ReadableEncryptedContent;
use Stecman\Passnote\Object\Renderable;

class StoredObject extends \Phalcon\Mvc\Model implements ReadableEncryptedContent, Renderable
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;
    use \Stecman\Passnote\Object\FormatPropertyTrait;
    use \Stecman\Passnote\Object\WritableEncryptedContentTrait;
    use \Stecman\Passnote\Object\HasUuidTrait;

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

    public function initialize()
    {
        $this->setSource('object');

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
        $version->setEncryptedSessionKey($this->sessionKey, $this->sessionKeyIv);
        $version->setEncryptedContent($this->content);
        $version->setEncryptedChecksum($this->checksum);
        $version->setFormat($this->format);

        return $version;
    }

    protected function beforeCreate()
    {
        // Ensure the object has a UUID for writing to the database
        $this->generateNewUuid();
    }

    protected function beforeUpdate()
    {
        // Save the old
        $this->saveVersion();

        // Change the UUID as the data is (assumed to be) changing
        $this->generateNewUuid();

        // Update the created date, since this is effectively a new object now (the old one is the version)
        $this->created = date('Y-m-d H:i:s');
    }

    /**
     * Create version of this object from the current state of the database
     */
    protected function saveVersion()
    {
        $previous = self::findFirst($this->id);

        // Prevent Phalcon from going into an infinite beforeUpdate loop
        if ($previous->content !== $this->content) {
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
    protected function generateSessionKey()
    {
        /** @var \Key $key */
        $key = $this->key;

        if (!$key) {
            throw new \RuntimeException("Object {$this->id} has no related key");
        }

        // Length of session key that will fit inside $this->key
        $length = min(
            $this->getEncryptor()->getKeySize(),
            $key->getMaxMessageSize()
        );

        return openssl_random_pseudo_bytes($length);
    }

    /**
     * @return \Stecman\Passnote\Encryptor
     */
    protected function getEncryptor()
    {
        return $this->getDI()->get('encryptor');
    }
}
