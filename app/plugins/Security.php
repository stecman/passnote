<?php

class Security extends \Phalcon\Mvc\User\Plugin
{
    const SESSION_USER = 'current-user';

    public function beforeDispatch(\Phalcon\Events\Event $event, Phalcon\Mvc\Dispatcher $dispatcher)
    {
        if ($user = $this->session->get(self::SESSION_USER)) {
            return true;
        } else if ($dispatcher->getHandlerClass() === 'AuthController') {
            return true;
        } else {
            $dispatcher->forward(array(
                'controller' => 'auth',
                'action' => 'login'
            ));
            return true;
        }

//        $this->view->render('auth', 'denied');
//        $this->response->setStatusCode(403, 'Access denied');
//        $this->response->send();
//        die();
    }
}