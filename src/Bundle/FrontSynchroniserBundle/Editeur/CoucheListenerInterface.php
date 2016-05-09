<?php
/**
 * Created by PhpStorm.
 * User: prestataire
 * Date: 09/05/16
 * Time: 17:32
 */

namespace FrontSynchroniserBundle\Editeur;


/**
 * CoucheListenerInterface
 *
 * @author Jean Pasqualini <jean.pasqualini@digitaslbi.fr>
 * @copyright 2016 DigitasLbi France
 * @package FrontSynchroniserBundle\Editeur;
 */
interface CoucheListenerInterface
{
    public function getLayer();

    public function domRender(\DOMNode $child, &$lines, $indent = 0);
}