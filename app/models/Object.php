<?php


class Object extends \Phalcon\Mvc\Model
{

    public $id;
     
    /**
     * @var string
     */
    public $created;
     
    /**
     * @var string
     */
    public $title;
     
    /**
     * @var string
     */
    public $content;
     
    /**
     * Encrypted key for this object
     *
     * @var string
     */
    public $key;
     
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
    public $isBinary;

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
    }
     
}
