<?php

use \Rych\OTP\Seed;

class UsersTask extends BaseTask
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
        $password = $this->promptCreatePassword();

        $user = new User();
        $user->email = $email;
        $user->password = $this->security->hash($password);

        $key = Key::generate( $user->setupDefaultKeyPassphrase($password) );
        $key->setName('Account key');

        // Save user and key
        $this->db->begin();
        $user->keys = [$key];
        $user->create();

        $user->defaultKey = $key;
        $user->update();
        $this->db->commit();

        echo "Created user $email with id {$user->id}\n";
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

            if (!$user->validatePassword( $oldPassword = $this->promptInput('Current password:', true) )) {
                die("Password incorrect\n");
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
            fwrite(STDIN, "OTP updated\n");
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
            if (!$user->isCorrectPassword( $oldPassword = $this->promptInput('Current password:', true) )) {
                die("Password incorrect\n");
            }

            $newPassword = $this->promptCreatePassword(true);

            // TODO: Update approriate keys on password change

//            $user->save();

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
    protected function promptCreatePassword($new = false)
    {
        $new = $new ? 'New ' : '';
        $password = $this->promptInput($new.'Password:', true);

        if ($password === '') {
            die("Password cannot be blank\n");
        }

        if ($this->promptInput('Confirm password:', true) !== $password) {
            die("Passwords didn't match\n");
        }

        return $password;
    }

    protected function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

}