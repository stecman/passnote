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
     * Encrypted key for this object
     *
     * @var string
     */
    protected $key;
     
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
