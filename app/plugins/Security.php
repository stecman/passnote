<?php

use Phalcon\Mvc\Dispatcher;

class Security extends \Phalcon\Mvc\User\Plugin
{
    const SESSION_USER_ID = 'sec-user';
    const SESSION_KEY = 'sec-seskey';

    /**
     * @return \User
     */
    public static function getCurrentUser()
    {
        $di = \Phalcon\DI::getDefault();
        $session = $di->get('session');

        if ($id = $session->get(self::SESSION_USER_ID)) {
            return User::findFirst($id);
        }
    }

    public function beforeDispatch(\Phalcon\Events\Event $event, Dispatcher $dispatcher)
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

    /**
     * Handle routing failure with a 404 page
     */
    public function beforeException(\Phalcon\Events\Event $event, Dispatcher $dispatcher, \Exception $exception)
    {
        if ($exception instanceof Phalcon\Mvc\Dispatcher\Exception) {
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:

                    $this->response->setStatusCode(404, 'Not found');
                    $this->view->setVar('status', 404);
                    $this->view->setVar('message', 'Not found');
                    $this->view->setVar('exception', $exception->getMessage());
                    $this->view->render('index', 'error');

                    $this->response->send();

                    return false;
            }
        }
    }
}