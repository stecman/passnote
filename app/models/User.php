<?php


use Phalcon\Mvc\Model\Validator\Email as Email;

class User extends \Phalcon\Mvc\Model
{

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
    public $defaultKey_id;

    /**
     * @var Key
     */
    public $defaultKey;

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
     * Default Key passphrase (encrypted)
     *
     * Key to the default key. Encrypted using the user's password
     *
     * @var string
     */
    protected $defaultKeyPhrase;

    /**
     * IV for encrypted Default Key passphrase
     *
     * @var string
     */
    protected $defaultKeyIv;

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

        $keyPassphrase = $user->setupDefaultKeyPassphrase($password);
        $key = Key::generate($keyPassphrase);

        $user->defaultKey = $key;

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
            'alias' => 'Keys'
        ]);

        $this->hasMany('id', 'Object', 'user_id', [
            'alias' => 'Objects'
        ]);

        $this->belongsTo('defaultKey_id', 'Key', 'id', [
            'alias' => 'DefaultKey'
        ]);
    }

    protected function beforeSave()
    {
        if ($key = $this->defaultKey) {
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

    public function getDefaultKeyPassphrase($password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();
        return $crypt->decrypt($this->defaultKeyPhrase, $password, $this->defaultKeyIv);
    }

    /**
     * Unlock the private key and encrypt it with a new random key
     * @param $oldPassword
     * @param $newPassword
     * @throws RuntimeException
     */
    public function recryptDefaultKey($oldPassword, $newPassword)
    {
        /** @var Key $key */
        if (!$key = $this->getDefaultKey()) {
            throw new \RuntimeException('User does not have a default key');
        }

        $oldPhrase = $this->getDefaultKeyPassphrase($oldPassword);
        $newPhrase = $this->setupDefaultKeyPassphrase($newPassword);

        $key->changePassphrase($oldPhrase, $newPhrase);
    }

    public function setupDefaultKeyPassphrase($password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();

        $newPhrase = base64_encode(openssl_random_pseudo_bytes(48));
        $this->defaultKeyIv = $crypt->genIv();
        $this->defaultKeyPhrase = $crypt->encrypt($newPhrase, $password, $this->defaultKeyIv);

        return $newPhrase;
    }

    /**
     * Change the account password and update the key of encrypted data that use it
     *
     * @param $oldPassword
     * @param $newPassword
     */
    public function changePassword($oldPassword, $newPassword)
    {
        $this->setPassword($newPassword);
        $this->recryptOtpKey($oldPassword, $newPassword);
        $this->recryptDefaultKey($oldPassword, $newPassword);
    }

    /**
     * @return Key
     */
    public function getDefaultKey()
    {
        return $this->defaultKey ?: $this->getRelated('DefaultKey');
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
