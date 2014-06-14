<?php

use Phalcon\Mvc\Controller;

class ControllerBase extends Controller
{
    protected function initialize()
    {
        $this->view->setPartialsDir('partials');
        $this->view->setVar('isDev', DEV_MODE);
    }

    protected function handleAs404($message = 'Not found')
    {
        $this->response->setStatusCode(404, 'Not found');
        $this->view->setVar('status', 404);
        $this->view->setVar('message', $message);
        $this->dispatcher->setControllerName('index');
        $this->dispatcher->setActionName('error');
    }
}
