<?php

namespace FrontSynchroniserBundle\Controller;

use Artack\DOMQuery\DOMQuery;
use FrontSynchroniserBundle\Editeur\CoucheCode;
use FrontSynchroniserBundle\Editeur\CoucheVisuel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FrontSynchroniserBundle\Service\FrontSynchroniserFinder;
use FrontSynchroniser\Render as FrontSynchroniserRender;
use \DOMNodeList;
use FrontSynchroniserBundle\Service\FrontSynchroniserManager;

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
    
    public function editAction(Request $request, $name)
    {
        /** @var FrontSynchroniserManager $fsManager */
        $fsManager = $this->get("front_synchroniser.manager");

        $pathResolver = $this->get("front_synchroniser.path_resolver.symfony");

        if($request->isMethod("POST"))
        {
            $nodePath = $request->request->get("path");

            $content = $request->request->get("content");

            $fsFile = $pathResolver->locate($name);

            $fsManager->saveEditor($fsFile, array(
                 "nodePath" => $nodePath,
                 "content" => $content
            ));

            return $this->redirect($request->getUri());

            //return new Response("modification du noeud : ".$nodePath." avec le contenu '".htmlentities($content)."'");
        }

        return $this->render('FrontSynchroniserBundle:Default:edit.html.twig', array(
            "editor" => $fsManager->buildEditor($pathResolver->locate($name))
        ));
    }

    public function testAction(Request $request)
    {
        $fd = FluentDOM("<h1>[OK]</h1>", "text/html");

        return new Response(htmlentities($fd));
    }
}
