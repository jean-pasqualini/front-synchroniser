<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 29/07/2015
 * Time: 17:38
 */

namespace FrontSynchroniserBundle\Twig\Loader;

class FilesystemLoader extends \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader {

    protected $frontSynchroniserManager;

    /**
     * @param mixed $frontSynchroniserManager
     */
    public function setFrontSynchroniserManager($frontSynchroniserManager)
    {
        $this->frontSynchroniserManager = $frontSynchroniserManager;
    }

    protected function isFrontSynchroniserTemplate($template)
    {
        return strpos($template, ".fs.") !== null;
    }

    protected function findTemplate($template)
    {
        $template = ($this->frontSynchroniserManager !== null && $this->isFrontSynchroniserTemplate($template)) ? $this->frontSynchroniserManager->compile($template) : $template;

        return parent::findTemplate($template);
    }
}