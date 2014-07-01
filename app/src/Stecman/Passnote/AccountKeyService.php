<?php


namespace Stecman\Passnote;


use Phalcon\Mvc\User\Component;
use Stecman\Passnote\Object\ReadableEncryptedContent;

class AccountKeyService extends Component
{
    const SESSION_ACCOUNT_KEY_CRYPTED = 'accountKeyService:passphrase';
    const SESSION_COOKIE_KEY_IV = 'accountKeyService:cookieIv';
    const COOKIE_NAME_KEY = 'ace';

    /**
     * @var Encryptor
     */
    protected $encryptor;

    public function __construct()
    {
        $this->encryptor = new Encryptor();
    }

    public function unlockAccountKeyForSession(\User $user, $accountPassword)
    {
        $passphrase = $user->getAccountKeyPassphrase($accountPassword);
        $this->setPassphrase($passphrase);
    }

    public function decryptObject(ReadableEncryptedContent $object)
    {
        return $object->getContent(
            $this->getPassphrase()
        );
    }

    public function purgeSessionKey()
    {
        $this->cookies->set(self::COOKIE_NAME_KEY, null, time()-3600);

        if ($this->session->isStarted()) {
            $this->session->remove(self::SESSION_COOKIE_KEY_IV);
            $this->session->remove(self::SESSION_ACCOUNT_KEY_CRYPTED);
        }
    }

    protected function setPassphrase($plainText)
    {
        $key = openssl_random_pseudo_bytes(32);
        $iv = $this->encryptor->genIv();
        $encrypted = $this->encryptor->encrypt($plainText, $key, $iv);

        $this->cookies->set(self::COOKIE_NAME_KEY, bin2hex($key), null, null, null, null, true);
        $this->session->set(self::SESSION_COOKIE_KEY_IV, $iv);
        $this->session->set(self::SESSION_ACCOUNT_KEY_CRYPTED, $encrypted);
    }

    protected function getPassphrase()
    {
        if (!$this->session->has(self::SESSION_ACCOUNT_KEY_CRYPTED)) {
            throw new \RuntimeException('Account key is missing from the session');
        }

        $plainText = $this->encryptor->decrypt(
            $this->session->get(self::SESSION_ACCOUNT_KEY_CRYPTED),
            @hex2bin($this->cookies->get(self::COOKIE_NAME_KEY)),
            $this->session->get(self::SESSION_COOKIE_KEY_IV)
        );

        return $plainText;
    }
} 