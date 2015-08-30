<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 21/08/2015
 * Time: 09:59
 */

namespace FrontSynchroniserBundle\Editeur;

class CoucheCode {

    protected $source;

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

        $attributes = array();

        $numLines = count($lines);

        foreach($child->attributes as $attribute)
        {
            $attributes[] = "<b>".$attribute->name."='<span data-path='$path' title='$path' class='node node-attribute node-container' contenteditable='true'>".$attribute->value."</span>'</b>";
        }

        $lines[] = $prepend.$baliseStart.$child->nodeName." ".implode(" ", $attributes)." ".$baliseEnd;

        if($child->hasChildNodes())
        {
            $this->getChildren($child->childNodes, $lines, $indent + 5);
        }

        $lines[] = $prepend.$baliseStart."/".$child->nodeName.$baliseEnd;
    }

    private function domText(\DOMText $child, &$lines, $indent = 0)
    {
        $prepend = $this->getPrepend($indent);

        $texte = trim($child->textContent);

        if(!empty($texte))
        {
            $lines[] = $prepend."<span class='node node-text node-container' contenteditable='true'>".$child->textContent."</span>";
        }
    }

    private function getChildren(\DOMNodeList $children, &$lines, $indent = 0)
    {
        foreach($children as $child)
        {

            if($child instanceof \DOMElement)
            {
                $this->domElement($child, $lines, $indent);
            }

            if($child instanceof \DOMText)
            {
                $this->domText($child, $lines, $indent);
            }

        }
    }

    public function render()
    {
        $dom = new \DOMDocument();

        $dom->loadHTML($this->source, LIBXML_HTML_NOIMPLIED);

        $lines = array();

        $this->getChildren($dom->childNodes, $lines);

        return "<div class='line'>".implode("</div><div class='line'>", $lines)."</div>";
    }
}