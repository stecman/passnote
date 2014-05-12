<?php

use Phalcon\Validation\Validator\PresenceOf;

class AuthController extends ControllerBase
{

    public function indexAction()
    {

    }

    public function loginAction()
    {
        $form = new \Phalcon\Forms\Form();
        $form->add($user = new \Phalcon\Forms\Element\Text('user'));
        $form->add($pass = new \Phalcon\Forms\Element\Password('password'));
        $form->add($twoFactorAuth = new \Phalcon\Forms\Element\Numeric('token'));

        $user->addValidator(new PresenceOf([
            'message' => 'Username is required.'
        ]));

        $pass->addValidator(new PresenceOf([
            'message' => 'Password is required.'
        ]));

        $twoFactorAuth->addValidator(new PresenceOf([
            'message' => 'Token is required.'
        ]));

        $this->view->setVar('form', $form);

        if ($this->request->isPost() && $form->isValid($this->request->getPost())) {

            if (!$this->security->checkToken()) {
                $this->flash->error('The form security token was invalid. Please submit the form again.');
                $this->cleanUpRequest();
                return false;
            }

            $this->tryLogin($this->request->getPost());
        }

        $this->cleanUpRequest();
    }

    public function logoutAction()
    {
        $this->session->destroy();
        header('Location: /');
        die();
    }

    public function tryLogin($data)
    {
        // Reject requests
        if ($this->isExceedingRateLimit(2)) {
            $this->response->setStatusCode(429, 'Too many requests');
            $this->flash->notice('Too many requests.');
            return false;
        }

        /** @var User $user */
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $data['user']
            ]
        ]);

        // Sleep for 1-500ms
        usleep(mt_rand(1000, 500000));

        if ($user && $user->validatePassword($data['password'])) {

            // Validate TOTP token
            // This needs to be done at this stage as the two factor auth key is
            // encrypted with the user's password.
            if ($otpKey = $user->getOtpKey($data['password'])) {
                $otp = new \Rych\OTP\TOTP($otpKey);
                if (!$otp->validate($data['token'])) {
                    $this->flash->error('Incorrect login details');
                    return false;
                };
            }

            $this->session->set(Security::SESSION_USER_ID, $user->id);
            $this->response->redirect('');
        } else {
            // Keep timing
            $this->security->hash(openssl_random_pseudo_bytes(12));
            $this->flash->error('Incorrect login details');
        }
    }

    protected function cleanUpRequest()
    {
        // Remove some things from the post data to stop Phalcon sending them back down the wire
        unset($_POST['password']);
        unset($_POST['token']);
    }

    /**
     * Check if the current request was made more than n seconds since the last
     * This is a pretty crude implementation intended for only a few (or one) users.
     *
     * @param $seconds - minimum number of seconds to allow between requests
     * @return bool
     */
    protected function isExceedingRateLimit($seconds)
    {
        if (extension_loaded('apc')) {
            $cacheKey = 'pn-last-login';
            $last = apc_fetch($cacheKey);

            if ($last && time() < $last) {
                return true;
            }

            apc_store($cacheKey, time() + $seconds);
        }

        return false;
    }

}

