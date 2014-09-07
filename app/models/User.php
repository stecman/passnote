<?php


use Phalcon\Mvc\Model\Validator\Email as Email;

class User extends \Phalcon\Mvc\Model
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;

    /**
     * @var integer
     */
    public $id;
     
    /**
     * Account email address
     *
     * @var string
     */
    public $email;

    /**
     * Default key to encrypt objects with
     *
     * @var int
     */
    public $accountKey_id;

    /**
     * @var Key
     */
    public $accountKey;

    /**
     * Hashed password
     *
     * @var string
     */
    protected $password;

    /**
     * One time password private key (HOTP/TOTP)
     *
     * @var string
     */
    protected $otpKey;

    /**
     * IV for OTP encryption
     *
     * @var string
     */
    protected $otpIv;

    /**
     * Session key
     *
     * Random token that can be changed to invalidate sessions.
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * Account key passphrase (encrypted)
     *
     * Key to the user's default key. Encrypted using the user's password
     *
     * @var string
     */
    protected $accountKeyPhrase;

    /**
     * IV for encrypted accountKeyPhrase
     *
     * @var string
     */
    protected $accountKeyIv;

    /**
     * Create a user and set up keys
     *
     * The steps to create a user per the spec of the system are quite specific.
     * This method should be used when creating a user to avoid duplication of
     * the steps needed to set up a completely new user correctly.
     *
     * NOTE: the key is not added to User->keys as these need to be saved separately.
     *
     * @param string $email
     * @param string $password
     * @return User
     */
    public static function createWithKeys($email, $password)
    {
        $user = new User();
        $user->email = $email;

        $keyPassphrase = $user->dangerouslyRegenerateAccountKeyPassphrase($password);
        $key = Key::generate($keyPassphrase);

        $user->accountKey = $key;

        return $user;
    }
     
    /**
     * Validations and business logic
     */
    public function validation()
    {
        $this->validate(
            new Email(
                array(
                    "field"    => "email",
                    "required" => true,
                )
            )
        );
        if ($this->validationHasFailed() == true) {
            return false;
        }

        return true;
    }

    protected function beforeValidation()
    {
        // Don't save without a session key
        if (!$this->sessionKey) {
            $this->regenerateSessionKey();
        }
    }

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->hasMany('id', 'Key', 'user_id', [
            'alias' => 'Keys',
            'reusable' => true
        ]);

        $this->hasMany('id', 'Object', 'user_id', [
            'alias' => 'Objects',
            'reusable' => true
        ]);

        $this->belongsTo('accountKey_id', 'Key', 'id', [
            'alias' => 'AccountKey',
            'reusable' => true
        ]);
    }

    protected function beforeSave()
    {
        if ($key = $this->accountKey) {
            $key->save();
        }
    }

    public function setOtpKey($key, $password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();
        $this->otpIv = $crypt->genIv();
        $this->otpKey = $crypt->encrypt($key, $password, $this->otpIv);
    }

    /**
     * Get the
     *
     * @param $password
     * @return string
     */
    public function getOtpKey($password)
    {
        if ($this->otpKey) {
            $crypt = new \Stecman\Passnote\Encryptor();
            return $crypt->decrypt($this->otpKey, $password, $this->otpIv);
        }
    }

    public function recryptOtpKey($oldPassword, $newPassword)
    {
        if (!$this->otpKey) {
            throw new \RuntimeException('User does not have an OTP key');
        }

        $crypt = new \Stecman\Passnote\Encryptor();
        $otpKey = $crypt->decrypt($this->otpKey, $oldPassword, $this->otpIv);
        $this->setOtpKey($otpKey, $newPassword);
    }

    /**
     * Decrypt and return the passphrase for the user's account key
     *
     * @param $password
     * @return string
     */
    public function getAccountKeyPassphrase($password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();

        if (!$this->accountKeyPhrase) {
            throw new \RuntimeException('User does not have an account key passphrase set');
        }

        return $crypt->decrypt($this->accountKeyPhrase, $password, $this->accountKeyIv);
    }

    /**
     * Unlock the account's default private key and encrypt it with a new random key
     *
     * @param $oldPassword
     * @param $newPassword
     * @throws RuntimeException
     */
    public function recryptAccountKey($oldPassword, $newPassword)
    {
        /** @var Key $key */
        if (!$key = $this->getAccountKey()) {
            throw new \RuntimeException('User does not have an account key');
        }

        $oldPhrase = $this->getAccountKeyPassphrase($oldPassword);
        $newPhrase = $this->dangerouslyRegenerateAccountKeyPassphrase($newPassword);

        $key->changePassphrase($oldPhrase, $newPhrase);
    }

    /**
     * Replace the currently stored account key passphrase with a new random phrase
     *
     * This method DOES NOT change the passphrase of the account key! It only changes the passphrase stored in
     * the user. To re-encrypt the account key with a new passphrase, use User::recryptAccountKey().
     *
     * @param $password
     * @return string - the new passphrase
     */
    public function dangerouslyRegenerateAccountKeyPassphrase($password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();

        $newPhrase = base64_encode(openssl_random_pseudo_bytes(48));
        $this->accountKeyIv = $crypt->genIv();
        $this->accountKeyPhrase = $crypt->encrypt($newPhrase, $password, $this->accountKeyIv);

        return $newPhrase;
    }

    /**
     * Change the account password and update the key of encrypted data that uses it
     *
     * @param $oldPassword
     * @param $newPassword
     */
    public function changePassword($oldPassword, $newPassword)
    {
        $this->setPassword($newPassword);
        $this->recryptOtpKey($oldPassword, $newPassword);
        $this->recryptAccountKey($oldPassword, $newPassword);
        $this->regenerateSessionKey();
    }

    /**
     * @return Key
     */
    public function getAccountKey()
    {
        return $this->accountKey ?: $this->getRelated('AccountKey');
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    public function regenerateSessionKey()
    {
        $this->sessionKey = openssl_random_pseudo_bytes(24);
    }

    public function validateSessionKey($key)
    {
        return trim($key) && $this->sessionKey === $key;
    }

    /**
     * Change the user's password
     *
     * @param $newPassword - plain text
     */
    public function setPassword($newPassword)
    {
        $security = new \Phalcon\Security();
        $this->password = $security->hash($newPassword);

        // Invalidate sessions on this account
        $this->regenerateSessionKey();
    }

    public function validatePassword($tryPassword)
    {
        $security = new \Phalcon\Security();
        return $security->checkHash($tryPassword, $this->password);
    }

}
