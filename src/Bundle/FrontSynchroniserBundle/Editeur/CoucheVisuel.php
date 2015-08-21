<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 21/08/2015
 * Time: 09:59
 */

namespace FrontSynchroniserBundle\Editeur;

class CoucheVisuel {

    protected $source;

    protected $lines;

    public function __construct($source)
    {
        $this->source = $source;
    }

    protected function getPrepend($indent)
    {
        $prepend = "";

        for($i = 0; $i <= $indent; $i++) { $prepend .= "."; }

        $prepend = "<span>$prepend</span>";

        return $prepend;
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

        return implode(",", $retour);
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

        return "<div title='$start : $countLines : $path' style='position: absolute; z-index: $zindex; top: ".$posYStart."px; height: ".$posYEnd."px; left: 0px; right: 0px;' class='couche-visuel-dom'><div class='couche-visuel-editeur' contenteditable='true'></div></div>";
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
        $dom = new \DOMDocument();

        $dom->loadHTML($this->source);

        $lines = array();

        $retour = $this->getChildren($dom->childNodes, $lines);

        return $retour;
    }
}