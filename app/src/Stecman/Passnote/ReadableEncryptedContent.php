<?php


namespace Stecman\Passnote;


interface ReadableEncryptedContent
{
    /**
     * @return \Key
     */
    public function getKey();

    /**
     * @return int
     */
    public function getKeyId();

    /**
     * Fetch and decrypt the content of this object
     *
     * @param $passphrase - Key passphrase
     * @return string
     */
    public function getContent($passphrase);
} 