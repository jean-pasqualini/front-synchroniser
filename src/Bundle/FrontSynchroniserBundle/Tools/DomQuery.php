<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 27/08/2015
 * Time: 10:58
 */

namespace FrontSynchroniserBundle\Tools;

class DomQuery extends \Artack\DOMQuery\DOMQuery {

    protected $nodes;

    /**
     * Get the descendants of each node in the current set of matched nodes, filtered by a selector
     *
     * This method allows us to search through the descendants of these nodes in the DOM tree and
     * return a new DOMQuery instance from the matching nodes
     *
     * @param $selector string
     * @return self
     */
    public function find($selector)
    {
        $expression = CssSelector::toXPath($selector);

        return $this->query($expression);
    }

    /**
     * Get the descendants of each node in the current set of matched nodes, filtered by a selector
     *
     * This method allows us to search through the descendants of these nodes in the DOM tree and
     * return a new DOMQuery instance from the matching nodes
     *
     * @param $selector string
     * @return self
     */
    public function query($expression)
    {
        $nodes      = array();

        foreach($this->nodes as $node) {
            $domX = new \DOMXPath($this->loadDOMDocument($node));

            foreach($domX->query($expression, $node) as $foundNode) {
                if($foundNode !== $node) {
                    $nodes[] = $foundNode;
                }
            }
        }

        return new self($nodes);
    }
}