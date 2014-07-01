<?php


namespace Stecman\Passnote\Object;


class Renderer
{
    /**
     * @var RendererInterface[]
     */
    protected $renderers = [];

    public function addRenderer(RendererInterface $renderer)
    {
        $this->renderers[$renderer->getIdentifier()] = $renderer;
    }

    public function hasRenderer($identifier)
    {
        return isset($this->renderers[$identifier]);
    }

    /**
     * Render an object to HTML based on its format identifier
     *
     * @param \Stecman\Passnote\Object\Renderable $renderable
     * @param string $content
     * @throws \RuntimeException
     * @return string - html
     */
    public function render(Renderable $renderable, $content)
    {
        $format = $renderable->getFormatIdentifier();

        if ($this->hasRenderer($format)) {
            return $this->renderers[$format]->render($content);
        } else {
            throw new \RuntimeException("No renderer available for format '$format'");
        }
    }

    /**
     * @return array - [identifier] => [human name]
     */
    public function getRendererNameMap()
    {
        $map = [];

        foreach ($this->renderers as $renderer) {
            $map[$renderer->getIdentifier()] = $renderer->getName();
        }

        return $map;
    }
} 