<?php
namespace Depa\Core\Filter;


class FilterCollection extends \Depa\Core\DataModel\Collection
{
    protected $databaseConnection;
    
    public function __construct($dbConnection)
    {
        $this->databaseConnection = $dbConnection;
    }
    
    public function loadByFilterableName($filterableName)
    {
        $this->items = Resource\Database::getInstance($this->dbConnection)->loadAllFilters($filterableName);
        return $this;
    }
}