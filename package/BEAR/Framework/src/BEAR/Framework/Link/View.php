<?php
/**
 * BEAR.Framework;
 *
 * @package BEAR.Resource
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Framework\Link;

use BEAR\Resource\Object as ResourceObject;
use BEAR\Framework\Exception\TemplateNotFound;
use BEAR\Framework\View\Renderer;

/**
 * Trait for smarty view link.
 *
 * @package BEAR.Framework
 * @author  Akihito Koriyama <akihito.koriyama@gmail.com>
 */
 trait View
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @Inject
     * @Named("link")
     */
    public function setRenderer($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @Inject
     * @Named("template_ext")
     *
     * @param string $ext
     */
    public function setExt($ext)
    {
        $this->ext = $ext;
    }

    /**
     * @Link
     *
     * View link
     *
     * @param ResourceObject $resource
     *
     * @return self
     */
    public function onLinkView(ResourceObject $resource)
    {
        if (is_array($resource->body) ||  $resource->body instanceof \Traversable) {
            foreach ($resource->body as &$element) {
                if (is_callable($element)) {
                    $element = $element();
                }
            }
        }
        $paegFile = (new \ReflectionClass($resource))->getFileName();
        $dir = pathinfo($paegFile, PATHINFO_DIRNAME);
        $this->renderer->assign('resource', $resource);
        if (is_array($resource->body) || $resource->body instanceof \Traversable) {
            $this->renderer->assign($resource->body);
        }
        $withoutExtention = substr(basename($paegFile), 0 ,strlen(basename($paegFile)) - 3);
        $templateFile =  $dir . DIRECTORY_SEPARATOR . $withoutExtention . '.' . $this->ext;
            if (! file_exists($templateFile)) {
            $templateFileInViewFodler =  $dir . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . $withoutExtention . '.' . $this->ext;
            if (! file_exists($templateFileInViewFodler)) {
                throw new TemplateNotFound($templateFile);
            }
        }
        $resource->body = $this->renderer->fetch($templateFile);
        return $resource;
    }
}