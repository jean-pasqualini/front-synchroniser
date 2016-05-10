<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 10/05/16
 * Time: 10:54
 */

namespace FrontSynchroniserBundle\Editeur\Layer;


/**
 * LayerRender
 *
 * @author Jean Pasqualini <jean.pasqualini@digitaslbi.fr>
 * @copyright 2016 DigitasLbi France
 * @package FrontSynchroniserBundle\Editeur\Layer;
 */
class LayerRender
{
    protected $layerRegistry;

    public function __construct(LayerRegistry $layerRegistry)
    {
        $this->layerRegistry = $layerRegistry;
    }

    public function renderCollection()
    {
        $output = '';

        $layerCollection = $this->layerRegistry->getLayerCollection();

        foreach($layerCollection as $id => $layer)
        {
            $output .= $this->render($layer, $id);
        }

        return $output;
    }

    public function render(array $layer, $id = null)
    {
        if($id === null) $id = uniqid('layer_');

        return 'render layer';
    }
}