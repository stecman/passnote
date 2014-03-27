<?php


class Credential extends \Phalcon\Mvc\Model
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
    public $title;

    /**
     *
     * @var string
     */
    public $body;
     
    /**
     *
     * @var integer
     */
    public $user_id;
     
    /**
     *
     * @var integer
     */
    public $key_id;

    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('key_id', 'Key', 'id');
    }
}
