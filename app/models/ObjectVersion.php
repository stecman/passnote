<?php

use Stecman\Passnote\Object\ReadableEncryptedContent;
use Stecman\Passnote\Object\Renderable;

class ObjectVersion extends \Phalcon\Mvc\Model implements ReadableEncryptedContent, Renderable
{
    use \Stecman\Passnote\Object\ReadableEncryptedContentTrait;
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;
    use \Stecman\Passnote\Object\FormatPropertyTrait;
    use \Stecman\Passnote\Object\HasUuidTrait;

    const OLDER_VERSION = 'older';
    const NEWER_VERSION = 'newer';

    public $id;

    /**
     * The object this is a version of
     *
     * @var integer
     */
    public $object_id;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->belongsTo('object_id', 'StoredObject', 'id', [
            'alias' => 'Master'
        ]);
    }

    /**
     * @param StoredObject $object
     * @return ObjectVersion
     */
    public static function versionFromObject(\StoredObject $object)
    {
        $version = new ObjectVersion();

        // Metadata
        $version->master = $object;
        $version->created = $object->getDateCreated();

        // Inherit UUID from source
        // The source gets a new UUID when it's saved
        $version->uuid = $object->uuid;

        // Copy payload of object
        $object->copyStateToVersion($version);

        return $version;
    }

    public function setEncryptedContent($raw)
    {
        $this->content = $raw;
    }

    public function setEncryptedChecksum($raw)
    {
        $this->checksum = $raw;
    }

    public function setEncryptedSessionKey($key, $iv)
    {
        $this->sessionKey = $key;
        $this->sessionKeyIv = $iv;
    }

    /**
     * @return StoredObject
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

    /**
     * Get a version of the same master object, relative to this version
     *
     * @param $relativeAge - one of ObjectVersion::NEWER_VERSION or ObjectVersion::OLDER_VERSION
     * @return StoredObject
     * @throws RuntimeException
     */
    public function getSibling($relativeAge)
    {
        $query = $this->getModelsManager()->createBuilder();
        $query->limit(1);
        $query->addFrom('ObjectVersion');
        $query->andWhere('object_id = :object_id:');

        switch ($relativeAge) {
            case self::NEWER_VERSION:
                $query->andWhere('id > :current_version_id:');
                break;
            case self::OLDER_VERSION:
                $query->andWhere('id < :current_version_id:');
                $query->orderBy('id DESC');
                break;
            default:
                throw new \RuntimeException('Invalid age. The only acceptable values are the constants ObjectVersion::NEWER_VERSION and ObjectVersion::OLDER_VERSION');
        }

        return $query->getQuery()->execute([
            'current_version_id' => $this->id,
            'object_id' => $this->object_id
        ])->getFirst();
    }

    /**
     * @return \Stecman\Passnote\Encryptor
     */
    protected function getEncryptor()
    {
        return $this->getDI()->get('encryptor');
    }

}
