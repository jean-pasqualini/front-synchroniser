<?php

namespace FrontSynchroniserBundle\Controller;

use Artack\DOMQuery\DOMQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use FrontSynchroniserBundle\Service\FrontSynchroniserFinder;
use FrontSynchroniser\Render as FrontSynchroniserRender;
use \DOMNodeList;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        $configuration = $this->getParameter("front_synchroniser");

        /**
         * @var $frontSynchroniserFinder FrontSynchroniserFinder
         */
        $frontSynchroniserFinder = $this->get("front_synchroniser.finder");

        if($name !== null)
        {
            $renderManager = new FrontSynchroniserRender();
            
            return new Response($renderManager->renderStatic($configuration["staticdir"].DIRECTORY_SEPARATOR.$name));
        }

        $finder = new Finder();

        $files = $finder
            ->in($configuration["staticdir"])
            ->name("*.html")
        ;

        $list = array();

        foreach($files as $file)
        {
            $list[] = array(
                            "file" => $file->getFilename(),
                            "meta" => $frontSynchroniserFinder->find($file->getFilename())
                    );
        }

        return $this->render('FrontSynchroniserBundle:Default:index.html.twig', array(
            'list' => $list
        ));
    }
    
    public function editAction($name)
    {
        $fsManager = $this->get("front_synchroniser.manager");
        
        $pathResolver = $this->get("front_synchroniser.path_resolver.symfony");
        
        return $this->render('FrontSynchroniserBundle:Default:edit.html.twig', array(
            "editorphp" => $fsManager->render($pathResolver->locate($name), true, false),
            "editorjs" => $fsManager->render($pathResolver->locate($name), true, true)
        ));
    }

    private function getChildren(\DOMNodeList $children, &$lines, $indent = 0)
    {
        $baliseStart = htmlentities("<");
        $baliseEnd = htmlentities(">");

        foreach($children as $child)
        {
            $prepend = "";

            for($i = 0; $i <= $indent; $i++) { $prepend .= "."; }

            $prepend = "<span>$prepend</span>";

            if($child instanceof \DOMElement)
            {
                $path = $child->getNodePath();

                $attributes = array();

                foreach($child->attributes as $attribute)
                {
                    $attributes[] = "<b>".$attribute->name."='<span data-path='$path' title='$path' class='node node-attribute node-container' contenteditable='true'>".$attribute->value."</span>'</b>";
                }

                $lines[] = $prepend.$baliseStart.$child->nodeName." ".implode(" ", $attributes)." ".$baliseEnd;
            }

            if($child instanceof \DOMText)
            {
                $texte = trim($child->textContent);

                if(!empty($texte))
                {
                    $lines[] = $prepend."<span class='node node-text node-container' contenteditable='true'>".$child->textContent."</span>";
                }
            }

            if($child->hasChildNodes())
            {
                $this->getChildren($child->childNodes, $lines, $indent + 5);
            }
        }
    }

    public function testAction()
    {
        $configuration = $this->getParameter("front_synchroniser");

        $static = $configuration["staticdir"].DIRECTORY_SEPARATOR."demo.html";

        $dom = new \DOMDocument();

        $dom->loadHTML(file_get_contents($static));

        $lines = array();

        $this->getChildren($dom->childNodes, $lines);

        return $this->render('FrontSynchroniserBundle:Default:test.html.twig', array(
            "lines" => $lines
        ));
    }
}
