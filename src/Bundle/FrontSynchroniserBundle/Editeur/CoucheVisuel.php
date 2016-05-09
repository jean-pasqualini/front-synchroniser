<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 21/08/2015
 * Time: 09:59
 */

namespace FrontSynchroniserBundle\Editeur;

use FrontSynchroniserBundle\Service\FrontSynchroniserManager;

class CoucheVisuel implements CoucheListenerInterface {

    protected $source;

    protected $domDocument;

    protected $frontSynchroniserManager;

    protected $lines;

    protected $metadata;

    public function __construct($source, $metadata, FrontSynchroniserManager $frontSynchroniserManager)
    {
        $this->source = $source;

        $this->domDocument = new \DOMDocument();

        $this->frontSynchroniserManager = $frontSynchroniserManager;

        $this->metadata = $metadata;
    }

    protected function getPrepend($indent)
    {
        $prepend = "";

        for($i = 0; $i <= $indent; $i++) { $prepend .= "."; }

        $prepend = "<span>$prepend</span>";

        return $prepend;
    }

    public function getLayer()
    {
        return 'visuel';
    }

    private function domElement(\DOMElement $child, &$lines, $indent = 0)
    {
        $baliseStart = htmlentities("<");
        $baliseEnd = htmlentities(">");

        $prepend = $this->getPrepend($indent);

        $path = $child->getNodePath();

        $start = count($lines);

        $lines[] = "";

        $retour = array();

        if($child->hasChildNodes())
        {
            $retour[] = $this->getChildren($child->childNodes, $lines, $indent + 5);
        }

        $lines[] = "";

        $numLines = count($lines) - ($start);

        $retour[] = $this->coucheVisuel($child, $start, $numLines, $lines);

        return implode("", $retour);
    }

    private function domText(\DOMText $child, &$lines, $indent = 0)
    {
        $prepend = $this->getPrepend($indent);

        $texte = trim($child->textContent);

        $start = count($lines);

        if(!empty($texte))
        {
            $lines[] = $prepend."<span class='node node-text node-container' contenteditable='true'>".$child->textContent."</span>";
        }

        $numLines = count($lines) - ($start);

        return $this->coucheVisuel($child, $start, $numLines, $lines);
    }

    private function coucheVisuel(\DOMNode $element, $start, $countLines, &$lines)
    {
        $zindexStart = 5;

        $zindex = $zindexStart * $start;

        $top = 0;

        $sizeLine = 20;

        $posYStart = $top + (($start) * $sizeLine);

        $posYEnd = ($countLines * $sizeLine);

        $path = $element->getNodePath();

        return "<div title='$start : $countLines : $path' style='position: absolute; z-index: $zindex; top: ".$posYStart."px; height: ".$posYEnd."px; left: 0px; right: 0px;' class='couche-visuel-dom'><div class='couche-visuel-editeur'><form action'' method='post'><input type='hidden' name='path' value='$path'><textarea name='content'></textarea><textarea class='code'>".$this->domDocument->saveHTML($element)."</textarea><input type='submit'></form></div></div>";
    }

    private function getChildren(\DOMNodeList $children, &$lines, $indent = 0)
    {
        $retour = array();

        foreach($children as $child)
        {
            if($child instanceof \DOMElement)
            {
                $retour[] = $this->domElement($child, $lines, $indent);
            }



            if($child instanceof \DOMText)
            {
                $retour[] = $this->domText($child, $lines, $indent);
            }
        }

        return implode("", $retour);
    }

    public function render()
    {
        $dom = $this->domDocument;

        $dom->loadHTML($this->source, LIBXML_HTML_NOIMPLIED);

        $lines = array();

        $retour = $this->getChildren($dom->childNodes, $lines);

        return $retour;
    }

    public function domRender(\DOMNode $child, &$lines, $indent = 0)
    {
        $content = $this->frontSynchroniserManager->getContentByXpath($this->metadata, $child->getNodePath());

        if($content !== null)
        {
            $lines[] = "<span class=''>OK</span>";

            return false;
        }

        return false;
    }
}