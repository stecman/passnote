<?php

use \Rych\OTP\Seed;

class UsersTask extends \Phalcon\CLI\Task
{
    /**
     * Create a new user
     *
     * @param $email
     */
    public function createAction($email)
    {
        if (!$this->isValidEmail($email)) {
            die("'$email' is not a valid email address\n");
        }

        echo "Creating user '$email'\n";
        $password = $this->promptPassword();

        if ($this->promptInput('Confirm password:', true) !== $password) {
            die("Passwords didn't match\n");
        }

        $user = new User();
        $user->email = $email;
        $user->password = $this->security->hash($password);

        if ($user->save()) {
            echo "Created user $email with id {$user->id}\n";
        } else {
            print_r($user->getMessages());
            exit(1);
        }
    }

    /**
     * Enable two factor auth on an account
     *
     * @param $email
     */
    public function enable_otpAction($email)
    {
        /** @var User $user */
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if ($user) {
            $password = $this->promptInput('User\'s password:', true);

            if (!$this->security->checkHash($password, $user->password)) {
                die("Incorrect password.\n");
            }

            $otp = Seed::generate(40);
            $user->setOtpKey($otp->getValue(Seed::FORMAT_BASE32), $password);
            $user->update();

            $uri = sprintf(
                'otpauth://totp/Passnote:%s?secret=%s&issuer=Passnote',
                urlencode($user->email),
                $otp->getValue(Seed::FORMAT_BASE32)
            );

            echo "$uri";
            fwrite(STDERR, "OTP updated: $uri\n");
        } else {
            die("No user found for $email\n");
        }
    }

    /**
     * List all users
     */
    public function listAction()
    {
        $users = User::find();

        foreach ($users as $user) {
            echo "{$user->email}\n";
        }
    }

    /**
     * Set the password of an existing user
     *
     * @param $email
     */
    public function set_passwordAction($email)
    {
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if ($user) {
            if (!$this->security->checkHash(
                $this->promptInput('Current password:', true),
                $user->password
            )) {
                die("Password incorrect\n");
            }

            $newPassword = $this->promptPassword(true);
            $user->password = $this->security->hash($newPassword);
            $user->save();

            echo "Password updated.\n";
        } else {
            die("No user found for $email\n");
        }
    }

    /**
     * Prompt user for password and verify entry
     *
     * @param bool $new - ask for 'new password' instead of 'password'
     * @return string
     */
    protected function promptPassword($new = false)
    {
        $new = $new ? 'New ' : '';
        $password = $this->promptInput($new.'Password:', true);

        if ($this->promptInput('Confirm password:', true) !== $password) {
            die("Passwords didn't match\n");
        }

        return $password;
    }

    protected function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Prompt user for input through STDIN
     */
    protected function promptInput($prompt, $hideInput = false)
    {
        fwrite(STDERR, $prompt);
        $options = $hideInput ? '-s' : '';
        $value = trim(`bash -c 'read $options uservalue && echo \$uservalue'`);
        fwrite(STDERR, "\n");

        return trim($value);
    }

}