<?php
namespace Depa\Core\Interfaces;

/**
 * Konvertiert das Objekt in seine JSON-Darstellung
 *
 * @param int $options            
 * @return string
 */
interface Jsonable
{

    public function toJson($options = 0);
}

?>