<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 29/07/2015
 * Time: 18:04
 */

namespace FrontSynchroniserBundle\Twig\Extension;

class FrontSynchroniserExtension extends \Twig_Extension {

    protected $frontSynchroniserManager;

    public function __construct($frontSynchroniserManager)
    {
        $this->frontSynchroniserManager = $frontSynchroniserManager;
    }

    /**
     * @param mixed $frontSynchroniserManager
     */
    public function setFrontSynchroniserManager($frontSynchroniserManager)
    {
        $this->frontSynchroniserManager = $frontSynchroniserManager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction("fscompile", array($this, "compile"))
        );
    }

    public function compile($template)
    {
        return $this->frontSynchroniserManager->compile($template);
    }

    public function getName()
    {
        return "fse";
    }
}