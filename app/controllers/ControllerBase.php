<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    protected function initialize()
    {
        $this->view->setPartialsDir('partials');
        $this->view->setVar('isDev', DEV_MODE);
    }
}
