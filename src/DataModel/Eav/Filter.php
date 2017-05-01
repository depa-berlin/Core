<?php

/** 
 * @author tim
 * 
 * 
 */
namespace Depa\Core\DataModel\Eav;



class Filter
{
    private static $filters = array();

    static public function factory ($filterName, $value)
    {
        if (! is_string($filterName) || ! strlen($filterName))
        {
            throw new \Exception('Die zu ladende Klasse muss in einer Zeichenkette benannt werden');
        }
        if (! array_key_exists($filterName, self::$filters))
        {
            $className = $filterName;
            if (class_exists($className))
            {
                self::$filters[$filterName] = new $className();
            }
            else
            {
                throw new \Exception('Filterklasse gibts nicht');
            }
        }
        return self::$filters[$filterName]->filter($value);
    }
}
?>