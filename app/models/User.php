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
     * Hashed password
     *
     * @var string
     */
    public $password;

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

    public function initialize()
    {
        $this->hasMany('id', 'Key', 'user_id');
        $this->hasMany('id', 'Credential', 'user_id');
    }

    public function setOtpKey($key, $password)
    {
        $crypt = new \Stecman\Passnote\Encryptor();
        $this->otpIv = $crypt->genIv();
        $this->otpKey = $crypt->encrypt($key, $password, $this->otpIv);
    }

    public function getOtpKey($password)
    {
        if ($this->otpKey) {
            $crypt = new \Stecman\Passnote\Encryptor();
            return $crypt->decrypt($this->otpKey, $password, $this->otpIv);
        }
    }

}
