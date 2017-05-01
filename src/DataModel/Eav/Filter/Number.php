<?php

/** 
 * @author Tim Mohrbach
 * 
 * 
 */
namespace Depa\Core\DataModel\Eav\Filter;

use Zend\Filter\StaticFilter;

class Core_Eav_Filter_Number
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        $value = StaticFilter::execute($value, 'Int');
        return $value;
    }
}
?>