<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 09/05/16
 * Time: 17:31
 */

namespace FrontSynchroniserBundle\Editeur;

use FrontSynchroniserBundle\Service\FrontSynchroniserManager;


/**
 * CoucheDispatcher
 *
 * @author Jean Pasqualini <jean.pasqualini@digitaslbi.fr>
 * @copyright 2016 DigitasLbi France
 * @package FrontSynchroniserBundle\Editeur;
 */
class CoucheDispatcher
{
    protected $metadata;

    protected $frontSynchroniserManager;

    protected $coucheListenerCollection;

    protected $layerCollection;

    public function __construct($metadata, FrontSynchroniserManager $frontSynchroniserManager)
    {
        $this->metadata = $metadata;

        $this->frontSynchroniserManager = $frontSynchroniserManager;
    }

    public function addCoucheListener(CoucheListenerInterface $coucheListener)
    {
        $this->coucheListenerCollection[] = $coucheListener;
    }

    protected function getLineByCoucheListener(CoucheListenerInterface $coucheListener, &$lines)
    {
        if($coucheListener->getLayer() == 'default')
        {
            return $lines;
        }

        if(!isset($this->layerCollection[$coucheListener->getLayer()]))
        {
            $this->layerCollection[$coucheListener->getLayer()] = array();
        }

        $this->layerCollection[$coucheListener->getLayer()][] = "";

        return $this->layerCollection[$coucheListener->getLayer()];
    }

    public function getCoucheCollection()
    {
        return array_filter($this->coucheListenerCollection, function(CoucheListenerInterface $couche)
        {
            return $couche->getLayer() != 'default';
        });
    }

    public function domRender(\DOMNode $child, &$lines, $indent = 0)
    {
        $retour = false;

        foreach($this->coucheListenerCollection as $coucheListener)
        {
            $coucheLines = $this->getLineByCoucheListener($coucheListener, $lines);

            /** @var CoucheListenerInterface $coucheListener */

            if($coucheListener->domRender($child, $coucheLines, $indent))
            {
                $retour = true;
            }
        }

        return $retour;
    }
}