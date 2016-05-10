<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 10/05/16
 * Time: 10:50
 */

namespace FrontSynchroniserBundle\Editeur\Layer;


/**
 * LayerRegistry
 *
 * @author Jean Pasqualini <jean.pasqualini@digitaslbi.fr>
 * @copyright 2016 DigitasLbi France
 * @package FrontSynchroniserBundle\Editeur\Layer;
 */
class LayerRegistry
{
    protected $layerCollection = array();

    public function addLayer($name, $layer = array())
    {
        $this->layerCollection[$name] = $layer;
    }

    public function getLayerCollection()
    {
        return $this->layerCollection;
    }

    protected function getLayer($layer)
    {
        if($layer == 'default')
        {
            return $this->layerCollection[$layer];
        }

        if(!isset($this->layerCollection[$layer]))
        {
            $this->layerCollection[$layer] = array();
        }

        $this->layerCollection[$layer][] = "";

        return $this->layerCollection[$layer];
    }
}