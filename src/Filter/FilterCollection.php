<?php
namespace Depa\Core\Filter;


use Depa\Core\DataModel\Collection;

class FilterCollection extends Collection
{
    protected $adapter;
    
    
    use DatabaseCollectionTrait;
    
    public function __construct($dbConnection)
    {
        $this->adapter = $dbConnection;
    }
    
    public function loadByFilterableName($filterableName)
    {
        $this->items = $this->loadAllFilters($filterableName);
        return $this;
    }
}