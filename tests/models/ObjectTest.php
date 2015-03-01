<?php


class ObjectTest extends PHPUnit_Framework_TestCase
{
    const KEY_PASSPHRASE = 'walrus5000';

    /**
     * @var \Object
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new Object();
        $this->object->key = Key::generate(self::KEY_PASSPHRASE, 386);
    }

    public function testSetGetContent()
    {
        $content = 'I am a glorious horse!';
        $this->object->setContent($content);
        $this->assertEquals($content, $this->object->getContent(self::KEY_PASSPHRASE));
    }

    public function testChecksum()
    {
        $content = openssl_random_pseudo_bytes(1024);
        $this->object->setContent($content);

        $decrypted = $this->object->getContent(self::KEY_PASSPHRASE);
        $this->assertEquals($content, $decrypted);
        $this->assertTrue($this->object->isChecksumValid($decrypted, self::KEY_PASSPHRASE));
    }

    public function testCopyToVersion()
    {
        $content = 'Harry the seagull bides his time.';

        $this->object->setContent($content);
        $version = ObjectVersion::versionFromObject($this->object);

        $this->assertEquals(
            $content,
            $version->getContent(self::KEY_PASSPHRASE)
        );

        $this->assertEquals(
            $this->object->getContent(self::KEY_PASSPHRASE),
            $version->getContent(self::KEY_PASSPHRASE)
        );
    }
}
