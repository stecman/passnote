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

    public function testSetContent()
    {
        $content = 'I am a glorious horse!';

        $this->object->setContent($content);

        $this->assertEquals($content, $this->object->getContent(self::KEY_PASSPHRASE));
    }

    public function testChecksum()
    {
        $content = openssl_random_pseudo_bytes(1024);

        $this->object->setContent($content);
        $firstRunChecksum = $this->object->checksum;
        $this->assertNotEmpty($this->object->checksum);

        $this->object->setContent($content);
        $this->assertEquals($firstRunChecksum, $this->object->checksum);

        $this->object->setContent($content.'abc');
        $this->assertNotEquals($firstRunChecksum, $this->object->checksum);
    }

}