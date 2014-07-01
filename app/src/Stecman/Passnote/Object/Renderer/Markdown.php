<?php


namespace Stecman\Passnote\Object\Renderer;


use Stecman\Passnote\Object\RendererInterface;
use Stecman\Passnote\PassnoteMarkdown;

class Markdown implements RendererInterface
{
    public function getIdentifier()
    {
        return 'markdown';
    }

    public function render($decryptedContent)
    {
        $parser = new PassnoteMarkdown();
        $parser->enableNewlines = true;
        return '<div class="obj-fmt-markdown">' . $parser->parse($decryptedContent) . '</div>';
    }

    public function getName()
    {
        return 'Markdown';
    }
}