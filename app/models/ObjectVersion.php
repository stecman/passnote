<?php

use Stecman\Passnote\ReadableEncryptedContent;

class ObjectVersion extends \Phalcon\Mvc\Model implements ReadableEncryptedContent
{
    use \Stecman\Passnote\ReadableEncryptedContentTrait;
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
        $version->created = $object->getDateCreated();
        $object->copyStateToVersion($version);

        return $version;
    }

    public function setEncryptedContent($raw)
    {
        $this->content = $raw;
    }

    public function setEncryptionKey($key, $iv)
    {
        $this->encryptionKey = $key;
        $this->encryptionKeyIv = $iv;
    }

    /**
     * @return Object
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @return Key
     */
    public function getKey()
    {
        return $this->getMaster()->getKey();
    }

    /**
     * @return int
     */
    public function getKeyId()
    {
        return $this->getMaster()->key_id;
    }

}
