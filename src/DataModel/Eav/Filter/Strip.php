<?php

/** 
 * @author Tim Mohrbach
 * 
 * 
 */
namespace Depa\Core\DataModel\Eav\Filter;

use Zend\Filter\StaticFilter;

class Strip
{
    public function __construct()
    {
    }
    
    public function filter($value)
    {
        //TODO: hier könnte man ausnahmen aus einer Config laden und diese angeben
        
        $value = StaticFilter::execute($value, 'StripTags');
        $value = StaticFilter::execute($value, 'Null');
        return $value;
    }    
}
?>