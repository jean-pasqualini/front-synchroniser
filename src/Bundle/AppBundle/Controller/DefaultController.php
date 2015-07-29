<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FrontSynchroniserManager;

class DefaultController extends Controller
{
    /**
     * @Route("/app/example", name="homepage")
     */
    public function indexAction()
    {
        /** @var FrontSynchroniserManager $frontSynchroniserManager */
        $frontSynchroniserManager = $this->get("front_synchroniser.manager");

        return $this->render($frontSynchroniserManager->compile('::default/index.fs.yml'));
    }
}
