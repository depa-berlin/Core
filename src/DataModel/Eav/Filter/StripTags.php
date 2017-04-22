<?php

/** 
 * @author Mirko Fenrich
 * 
 * 
 */
namespace depaLibraries\Core\DataModel\Eav\Filter;

use Zend;

class StripTags
{
    public function __construct()
    {
    }
    
    public function filter($value)
    {
        //TODO: hier könnte man ausnahmen aus einer Config laden und diese angeben
        
        $value = Zend\Filter\StaticFilter::execute($value, 'StripTags');
        $value = Zend\Filter\StaticFilter::execute($value, 'Null');
        return $value;
    }    
}
?>