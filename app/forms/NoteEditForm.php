<?php

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;

class NoteEditForm extends \Phalcon\Forms\Form
{
    public function initialize()
    {
        $this->add(new Text('title'));
        $this->add(new TextArea('body'));
        die('test');
    }
}