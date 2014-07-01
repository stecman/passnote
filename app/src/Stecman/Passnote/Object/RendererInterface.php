<?php


namespace Stecman\Passnote\Object;


interface RendererInterface
{
    /**
     * Storable identifier for the renderer
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Human name for the renderer
     *
     * @return string
     */
    public function getName();

    /**
     * Render an object to html for template useW
     *
     * @param $decryptedContent
     * @return string - html
     */
    public function render($decryptedContent);
} 