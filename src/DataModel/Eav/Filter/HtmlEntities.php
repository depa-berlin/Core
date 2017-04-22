<?php

/** 
 * @author Tim Mohrbach
 * 
 * 
 */
namespace depaLibraries\Core\DataModel\Eav\Filter;

use Zend;

class HtmlEntities
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        $value = Zend\Filter\StaticFilter::execute($value, 'StringTrim');
        $value = Zend\Filter\StaticFilter::execute($value, 'HtmlEntities');
        return $value;
    }
}
?>