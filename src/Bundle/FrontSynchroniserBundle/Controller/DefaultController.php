<?php

namespace FrontSynchroniserBundle\Controller;

use Artack\DOMQuery\DOMQuery;
use FrontSynchroniserBundle\Editeur\CoucheCode;
use FrontSynchroniserBundle\Editeur\CoucheVisuel;
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

    public function testAction()
    {
        $configuration = $this->getParameter("front_synchroniser");

        $static = $configuration["staticdir"].DIRECTORY_SEPARATOR."demo.html";

        $coucheCode = new CoucheCode(file_get_contents($static));

        $coucheVisuel = new CoucheVisuel(file_get_contents($static));

        return $this->render('FrontSynchroniserBundle:Default:test.html.twig', array(
            "coucheCode" => $coucheCode,
            "coucheVisuel" => $coucheVisuel
        ));
    }
}
