<?php


namespace Stecman\Passnote\Object;


interface Renderable
{
    /**
     * @see Stecman\Passnote\Object\FormatPropertTrait::setFormat
     * @param string $identifier
     */
    public function setFormat($identifier);

    /**
     * @see Stecman\Passnote\Object\FormatPropertTrait::getFormatIdentifier
     * @return string
     */
    public function getFormatIdentifier();
} 