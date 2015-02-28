<?php


namespace Stecman\Passnote\Object\Renderer;


use Stecman\Passnote\Object\RendererInterface;

class PlainText implements RendererInterface
{
    public function render($decryptedContent)
    {
        return '<pre><code>' . htmlentities($decryptedContent) .'</code></pre>';
    }

    public function getIdentifier()
    {
        return 'raw-text';
    }

    public function getName()
    {
        return 'Text';
    }
}
