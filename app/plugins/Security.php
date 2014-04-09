<?php

class Security extends \Phalcon\Mvc\User\Plugin
{
    const SESSION_USER_ID = 'sec-user';
    const SESSION_KEY = 'sec-seskey';

    public static function getCurrentUser()
    {
        $di = \Phalcon\DI::getDefault();
        $session = $di->get('session');

        if ($id = $session->get(self::SESSION_USER_ID)) {
            return User::findFirst($id);
        }
    }

    public function beforeDispatch(\Phalcon\Events\Event $event, Phalcon\Mvc\Dispatcher $dispatcher)
    {
        $id = $this->session->get(self::SESSION_USER_ID);
        $user = $this->getCurrentUser();

        if ($id && $user) {
            // Allow access when logged in

        } else if ($dispatcher->getHandlerClass() === 'AuthController' && $dispatcher->getActionName() === 'login') {
            // Permit the login action when not logged in

        } else {
            // Handle everything with /auth/login for guests
            $dispatcher->forward(array(
                'controller' => 'auth',
                'action' => 'login'
            ));
        }
    }
}