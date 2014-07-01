<?php


namespace Stecman\Passnote\Object;


trait FormatPropertyTrait
{
    /**
     * Format identifier to use when rendering this object
     *
     * @var string|null
     */
    protected $format = null;

    /**
     * Set the format identifier of this object
     *
     * @param string $identifier - the identifier string from an instance of \Stecman\Passnote\Object\RendererInterface
     * @throws \RuntimeException
     */
    public function setFormat($identifier)
    {
        if (is_null($identifier) || $this->getDI()->get('renderer')->hasRenderer($identifier)) {
            $this->format = $identifier;
        } else {
            throw new \RuntimeException("Refusing to set unknown format identifier '$identifier'");
        }
    }

    public function getFormatIdentifier()
    {
        if (!$this->format) {
            return 'raw-text';
        }

        return $this->format;
    }
} 