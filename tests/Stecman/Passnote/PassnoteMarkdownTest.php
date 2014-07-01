<?php


class PassnoteMarkdownTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Stecman\Passnote\PassnoteMarkdown
     */
    protected $parser;

    protected function setUp()
    {
        $this->parser = new \Stecman\Passnote\PassnoteMarkdown();
    }

    public function testParseLink()
    {
        $md = 'Hello [example](http://example.com).';
        $html = $this->parser->parseParagraph($md);

        $this->assertEquals(
            'Hello <a rel="noreferrer" href="http://example.com">example</a>.',
            $html
        );
    }

    public function testParseUrl()
    {
        $md = 'Website: http://example.com';
        $html = $this->parser->parseParagraph($md);

        $this->assertEquals(
            'Website: <a rel="noreferrer" href="http://example.com">http://example.com</a>',
            $html
        );
    }
}