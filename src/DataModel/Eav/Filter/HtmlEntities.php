<?php

/** 
 * @author Tim Mohrbach
 * 
 * 
 */
namespace Depa\Core\DataModel\Eav\Filter;

use Laminas\Filter\StaticFilter;

class HtmlEntities
{

    public function __construct ()
    {
    }

    public function filter ($value)
    {
        $value = StaticFilter::execute($value, 'StringTrim');
        $value = StaticFilter::execute($value, 'HtmlEntities');
        return $value;
    }
}
?>