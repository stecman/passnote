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
        $rendererMap = $this->di->get('renderer')->getRendererNameMap();

        $this->add($title = new Text('title'));
        $this->add(new Text('description'));
        $this->add($body = new TextArea('body'));
        $this->add($key = new \Phalcon\Forms\Element\Select('key_id', $keyMap));
        $this->add($format = new \Phalcon\Forms\Element\Select('format', $rendererMap));

        $key->setDefault($this->user->accountKey_id);
        
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

        $format->addValidator(new \Phalcon\Validation\Validator\InclusionIn([
            'message' => 'Format is required',
            'domain' => array_keys( $rendererMap )
        ]));
    }

    public function setBody($content)
    {
        if (!$this->request->isPost()) {
            $this->get('body')->setDefault($content);
        }
    }

    /**
     * Handle the form and return the saved object
     *
     * @return Object
     */
    public function handleSubmit()
    {
        $object = $this->getEntity() ?: new Object();

        if ($object->key_id != $this->request->getPost('key_id')) {
            $object->key = Key::findFirst( (int) $this->request->getPost('key_id') );
        }

        $object->user = $this->user;
        $object->title = $this->request->getPost('title');
        $object->description = $this->request->getPost('description');
        $object->setFormat( $this->request->getPost('format') );
        $object->setContent( $this->request->getPost('body') );

        $object->save();

        $this->flash->success('Object saved');

        return $object;
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