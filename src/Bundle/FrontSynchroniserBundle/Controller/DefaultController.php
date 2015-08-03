<?php

namespace FrontSynchroniserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use FrontSynchroniserBundle\Service\FrontSynchroniserFinder;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        $configuration = $this->getParameter("front_synchroniser");

        /**
         * @var $frontSynchroniserFinder FrontSynchroniserFinder
         */
        $frontSynchroniserFinder = $this->get("front_synchroniser.finder");

        if($name !== null) return $this->render($configuration["staticdir"].DIRECTORY_SEPARATOR.$name);

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
            "editorcontent" => $fsManager->render($pathResolver->locate($name), true)
        ));
    }
}
