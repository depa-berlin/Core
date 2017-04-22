<?php

/** 
 * @author tim
 * 
 * 
 */
namespace depaLibraries\Core\DataModel\Eav\Filter;

use Zend;

class Boolean
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        $value = Zend\Filter\StaticFilter::execute($value, 'Boolean');
        return $value;
    }
}
?>