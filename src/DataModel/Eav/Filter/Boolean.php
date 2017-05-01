<?php

/** 
 * @author tim
 * 
 * 
 */
namespace Depa\Core\DataModel\Eav\Filter;

use Zend\Filter\StaticFilter;

class Boolean
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        
        $value = StaticFilter::execute($value, 'Boolean'); 
        return $value;
    }
}
?>