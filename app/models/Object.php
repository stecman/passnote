<?php


class Object extends \Phalcon\Mvc\Model
{
    use \Stecman\Phalcon\Model\Traits\CreationDateTrait;

    public $id;
     
    /**
     * Title of the object
     * @var string
     */
    public $title;

    /**
     * Description if the object needs one.
     *
     * Since the object's content is encrypted, it is not possible to search in content.
     * This field is intended for providing context/information without giving away secrets.
     *
     * @var string
     */
    public $description;
     
    /**
     * Encrypted content of the object
     *
     * @var string
     */
    protected $content;
     
    /**
     * Passphrase for the AES encryption of $content.
     * Encrypted with the Key indicated by $key_id
     *
     * @var string
     */
    protected $encryptionKey;
     
    /**
     * RSA key pair used to encrypt $key
     *
     * @var integer
     */
    public $key_id;
     
    /**
     * The user who owns this object
     *
     * @var integer
     */
    public $user_id;
     
    /**
     * The object this object belongs to
     *
     * @var integer
     */
    public $parent_id;
     
    /**
     * Whether $content should be considered as binary or text
     *
     * @var boolean
     */
    public $isBinary;

    /**
     * SHA1 hash of the unencrypted content
     *
     * @var string
     */
    public $checksum;

    public function initialize()
    {
        $this->useDynamicUpdate(true);
        $this->setup([
            'exceptionOnFailedSave' => true
        ]);

        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('key_id', 'Key', 'id');

        $this->hasMany('id', 'Object', 'parent_id', [
            'alias' => 'Children'
        ]);

        $this->hasMany('id', 'ObjectVersion', 'object_id', [
            'alias' => 'Versions'
        ]);
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        if (!$this->encryptionKey) {

        }

        /** @var Key $key */
        $key = $this->encryptionKey;

        $this->checksum = sha1($content);
        $this->content = $key->encrypt();
    }

    /**
     * @param string $password
     */
    public function getContent($password)
    {
        /** @var Key $key */
        $key = $this->encryptionKey;
        $key->decrypt($this->content, $this->getEncryptionKey($password));
    }

    protected function getEncryptionKey($password)
    {

    }

    protected function beforeUpdate()
    {
        $this->saveVersion();
    }

    /**
     * Create version of this object from the current state of the database
     */
    protected function saveVersion()
    {
        $previous = self::findFirst($this->id);

        if ($previous && $previous->checksum !== $this->checksum) {
            $version = new ObjectVersion();
            $version->content = $this->content;
            $version->checksum = $this->checksum;
            $version->object_id = $this->id;
            $version->create();
        }
    }
     
}
