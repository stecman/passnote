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
    public $content;

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

        $this->belongsTo('object_id', 'Object', 'id', [
            'alias' => 'Master'
        ]);
    }

    public function getMaster()
    {
        return $this->getRelated('Master');
    }

}
