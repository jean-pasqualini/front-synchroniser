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

        $profiles = array(
            array(
                "name" => "Jean"
            ),
            array(
                "name" => "Paul"
            ),
            array(
                "name" => "Gerard"
            ),
            array(
                "name" => "Micher"
            ),
            array(
                "name" => "Maxime"
            ),
            array(
                "name" => "Jerome"
            ),
            array(
                "name" => "Ludovic"
            ),
            array(
                "name" => "Madani"
            ),
            array(
                "name" => "Danielo"
            ),
        );

        $profiles = array_slice($profiles, rand(1, 9));

        shuffle($profiles);

        return $this->render($frontSynchroniserManager->compile('::default/index.fs.yml'), array(
            "profiles" => $profiles
        ));
    }
}
