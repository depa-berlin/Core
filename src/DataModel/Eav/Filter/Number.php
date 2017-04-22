<?php

/** 
 * @author Tim Mohrbach
 * 
 * 
 */
namespace depaLibraries\Core\DataModel\Eav\Filter;

use Zend;

class Core_Eav_Filter_Number
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        $value = Zend\Filter\StaticFilter::execute($value, 'Int');
        return $value;
    }
}
?>