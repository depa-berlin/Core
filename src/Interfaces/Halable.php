<?php
namespace Depa\Core\Interfaces;

/**
 * Konvertiert das Objekt in seine HAL-Darstellung
 *
 * @param int $options            
 * @return string
 */
interface Halable
{

    public function toHal(\Laminas\Diactoros\Uri $requestUri);
}

?>