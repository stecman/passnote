<?php

class ObjectVersion extends \Phalcon\Mvc\Model
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;

    /**
     *
     * @var integer
     */
    public $id;

    /**
     * The object this is a version of
     *
     * @var integer
     */
    public $object_id;

    /**
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
     * SHA1 hash of the unencrypted content
     *
     * @var string
     */
    public $checksum;

    /**
     * Whether $content should be considered as binary or text
     *
     * @var boolean
     */
    public $isBinary = false;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->belongsTo('object_id', 'Object', 'id', [
            'alias' => 'Master'
        ]);
    }

    /**
     * @param Object $object
     * @return ObjectVersion
     */
    public static function versionFromObject(\Object $object)
    {
        $version = new ObjectVersion();

        $version->master = $object;
        $version->content = $object->content;
        $version->checksum = $object->checksum;

        return $version;
    }

    public function setEncryptionKey($key, $iv)
    {
        $this->encryptionKey = $key;
        $this->encryptionKeyIv = $iv;
    }

    public function getMaster()
    {
        return $this->getRelated('Master');
    }

}
