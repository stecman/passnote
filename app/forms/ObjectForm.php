<?php

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;

class ObjectForm extends \Phalcon\Forms\Form
{

    /**
     * @var \User
     */
    protected $user;

    public function __construct($entity = null, \User $user = null)
    {
        $this->user = $user;
        parent::__construct($entity, null);
    }

    public function initialize()
    {
        $keyMap = $this->getUserKeys();

        $this->add($title = new Text('title'));
        $this->add(new Text('description'));
        $this->add($body = new TextArea('body'));
        $this->add($key = new \Phalcon\Forms\Element\Select('key_id', $keyMap));
        
        $title->addValidator(new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'Title is required'
        ]));
        
        $body->addValidator(new \Phalcon\Validation\Validator\PresenceOf([
            'message' => 'Content is required'
        ]));
        
        $key->addValidator(new \Phalcon\Validation\Validator\InclusionIn([
            'message' => 'Encryption key is required',
            'domain' => array_keys( $keyMap )
        ]));
    }

    public function handleSubmit()
    {
        $object = $this->getEntity() ?: new Object();

        $object->title = $this->request->getPost('title');
        $object->description = $this->request->getPost('description');
        $object-> = $this->request->getPost('body');
    }

    /**
     * Get a map of id => name for the keys belonging to $this->user
     *
     * @return array
     */
    protected function getUserKeys()
    {
        $keyMap = [];

        $keys = Key::find([
            'user_id = :user_id:',
            'bind' => [
                'user_id' => $this->user->id
            ]
        ]);

        foreach ($keys as $key) {
            $keyMap[$key->id] = $key->name;
        }

        return $keyMap;
    }
}