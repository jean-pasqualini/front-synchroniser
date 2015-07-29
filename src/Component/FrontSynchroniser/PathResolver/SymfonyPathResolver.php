<?php
/**
 * Created by PhpStorm.
 * User: Freelance
 * Date: 29/07/2015
 * Time: 16:59
 */

namespace FrontSynchroniser\PathResolver;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser;

class SymfonyPathResolver {

    protected $templateNameParser;

    protected $templateLocator;

    public function __construct(TemplateNameParser $templateNameParser, TemplateLocator $templateLocator)
    {
        $this->templateNameParser = $templateNameParser;

        $this->templateLocator = $templateLocator;
    }

    public function locate($path)
    {
        return $this->templateLocator->locate($this->templateNameParser->parse($path));
    }
}