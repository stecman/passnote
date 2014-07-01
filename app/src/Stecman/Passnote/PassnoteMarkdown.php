<?php


namespace Stecman\Passnote;


use cebe\markdown\GithubMarkdown;

/**
 * Passnote Markdown
 *
 * Markdown parser that generates more paranoid HTML than regular markdown
 */
class PassnoteMarkdown extends GithubMarkdown
{
    /**
     * Make all links use the rel="noreferrer" attribute
     *
     * @param $markdown
     * @return array
     */
    protected function parseLink($markdown)
    {
        $array = parent::parseLink($markdown);
        $array[0] = $this->addNoReferrerAttribute($array[0]);

        return $array;
    }

    /**
     * Make auto-linked URLs use the rel="noreferrer" attribute
     *
     * @param $markdown
     * @return array
     */
    protected function parseUrl($markdown)
    {
        $array = parent::parseUrl($markdown);
        $array[0] = $this->addNoReferrerAttribute($array[0]);

        return $array;
    }

    protected function addNoReferrerAttribute($anchorHtml)
    {
        return preg_replace('/^<a/', '<a rel="noreferrer"', $anchorHtml, 1);
    }

} 