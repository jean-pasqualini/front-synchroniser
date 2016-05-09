<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 31/08/2015
 * Time: 10:02
 */

namespace FrontSynchroniserBundle\Editeur;


use FrontSynchroniserBundle\Service\FrontSynchroniserManager;

class CoucheContennu implements CoucheListenerInterface {

    protected $metadata;

    protected $frontSynchroniserManager;

    public function getLayer()
    {
        return 'default';
    }

    public function __construct($metadata, FrontSynchroniserManager $frontSynchroniserManager)
    {
        $this->metadata = $metadata;

        $this->frontSynchroniserManager = $frontSynchroniserManager;
    }

    protected function getPrepend($indent)
    {
        $prepend = "";

        for($i = 0; $i <= $indent; $i++) { $prepend .= "."; }

        $prepend = "<span>$prepend</span>";

        return $prepend;
    }

    public function domRender(\DOMNode $child, &$lines, $indent = 0)
    {
        $content = $this->frontSynchroniserManager->getContentByXpath($this->metadata, $child->getNodePath());

        $prepend = $this->getPrepend($indent);

        if($content !== null)
        {
            $lines[] = $prepend."<span class='node-conteneur'>".$content."</span>";

            return true;
        }
    }
}