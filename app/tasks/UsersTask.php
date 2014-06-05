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

        if ($user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ])) {
            die("The account $email already exists. Duplicate account emails are not allowed.\n");
        }

        echo "Creating user '$email'\n";
        $password = $this->promptCreatePassword();

        echo "Keying...\n";

        $user = new User();
        $user->email = $email;
        $user->setPassword($password);

        // Create OTP key
        $otp = Seed::generate(40);
        $user->setOtpKey($otp->getValue(Seed::FORMAT_BASE32), $password);

        // Create account key
        $key = Key::generate( $user->dangerouslyRegenerateAccountKeyPassphrase($password) );
        $key->setName('Account key');

        // Save user and key
        $this->db->begin();
        $user->create();

        $key->user_id = $user->id;
        $key->create();

        $user->accountKey_id = $key->id;
        $user->update();
        $this->db->commit();

        echo "Created user $email with id {$user->id}\n";
        echo "OTP: {$this->generateOtpUri($user, $otp)}\n";
    }

    /**
     * Enable two factor auth on an account
     *
     * The result of this can be piped, for example, into a QR code generating utility.
     *
     * @param $email
     */
    public function regenerate_otpAction($email)
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

            if (!$user->validatePassword( $password )) {
                die("Password incorrect\n");
            }

            $otp = Seed::generate(40);
            $user->setOtpKey($otp->getValue(Seed::FORMAT_BASE32), $password);
            $user->update();

            echo $this->generateOtpUri($user, $otp);
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
        /** @var User $user */
        $user = User::findFirst([
            'email = :email:',
            'bind' => [
                'email' => $email
            ]
        ]);

        if ($user) {
            $oldPassword = $this->promptInput('Current password:', true);

            if (!$user->validatePassword( $oldPassword )) {
                die("Password incorrect\n");
            }

            $newPassword = $this->promptCreatePassword(true);
            $user->changePassword($oldPassword, $newPassword);

            $user->accountKey->save();
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

    protected function generateOtpUri(\User $user, \Rych\OTP\Seed $otp)
    {
        return sprintf(
            'otpauth://totp/%s?secret=%s&issuer=Passnote',
            urlencode($user->email),
            $otp->getValue(Seed::FORMAT_BASE32)
        );
    }

}