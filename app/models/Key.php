<?php




class Key extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;
     
    /**
     *
     * @var string
     */
    public $name;
     
    /**
     *
     * @var string
     */
    public $key;
     
    /**
     *
     * @var integer
     */
    public $user_id;

    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->hasMany('id', 'Credential', 'key_id');
    }
}
