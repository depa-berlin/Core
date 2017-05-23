<?php
namespace Depa\Core\Filter;

/**
 * Collection für Objekte mit Filterable-Trait.
 * 
 * In Verbindung mit einem Query-Objekt ist es einfach möglich ein Set an filterbaren Objekten 
 * 
 * @author alex
 *
 */
class FilterableCollection extends \Depa\Core\DataModel\IdCollection
{
    protected $itemIdArray;
    
    protected $resource;
    
    public function __construct($dbConnection)
    {
        $this->databaseConnection = $dbConnection;
        $this->resource = Resource\Database::getInstance($dbConnection);
    }
    
    /**
     * Lädt Filter nach Parametern in dem übergebenen Query-Objekt. Sind die Parameter leer, werden alle Objekte geladen. Im Gegensatz zu getFilteredItems()
     * wird hier ein SQL-Query gebaut das nur gewollte Items zurückgibt, um DB-Zugriff zu minimieren.
     * 
     * @param \Core\Filter\Query $query
     * @return \Core\Filter\FilterableCollection
     */
    public function loadByFilterQuery(\Depa\Core\Filter\Query $query)
    {
       $itemIdArray = array();
       if (count($query->getParams()) === 0)
       {
           if (count($query->getFilterables()) > 1)
           {
               throw new \Exception('FilterableCollection can only hold items of one type, received query with more than one!');
           }
           foreach ($query->getFilterables() as $filterable)
           {
              $itemIdArray[$filterable['class']] = $this->getAllItemIds($filterable);
              $this->itemIds = $this->getAllItemIds($filterable);
           }
       }
       else
       {
           $this->itemIdArray = $this->resource->loadIdsByQuery($query);
       }
      /** foreach ($itemIdArray as $filterableClassName => $filterableIdArray)
       {
           foreach ($filterableIdArray as $filterableId)
           {
               $this->addItemById($filterableClassName, $filterableId, $query);
           }
       }**/
       return $this;
    }
    
    /**
     * Erstellt ein Item und fügt es der Collection hinzu. Zusätzlich wird auf vorhandene Special-Parameter überprüft
     * 
     * @param unknown $className
     * @param unknown $id
     * @param \Core\Filter\Query $query
     */
    protected function addItemById($className, $id, \Depa\Core\Filter\Query $query)
    {
        $item = $this->createItem($id);
        $item->loadFilterData($this->resource);
        if ($this->checkSpecialParams($item, $query->getSpecialParams()) === true)
        {
            $this->addItem($item);
        }
    }
    
    protected function createItem($id)
    {

    }
    
    /**
     * Filtert schon geladene Items nach Parametern im Query-Objekt.
     * Hierbei werden keine Items aus der DB nachgeladen, sondern nur schon in der Collection vorhandene Items gefiltert.
     * 
     * @param \Core\Filter\Query $query
     * @return multitype:
     */
    public function getFilteredItems(\Depa\Core\Filter\Query $query)
    {
        $result = array();
        if (count($query->getParams()) > 0)
        {
            foreach ($query->getParams() as $categoryId => $filter)
            {
                foreach ($this as $filterableObjectId)
                {
                    $filterableObject = $this->getItemById($filterableObjectId);
                    $category = new Category($categoryId);
                    if ($filterableObject->hasFilterValueForCategory($category, $filter))
                    {
                        $result[] = $filterableObject;
                    }
                }
            }
        }
        else
        {
            foreach ($this as $filterableObjectId)
            {
                $result[] = $this->getItemById($filterableObjectId);
            }
        }
        $filteredResult = array();
        foreach ($result as $filterableObject)
        {
            if ($this->checkSpecialParams($filterableObject, $query->getSpecialParams()) === true)
            {
                $filteredResult[] = $filterableObject;
            }
        }
        //$filteredResult = array_unique($filteredResult);
        return $filteredResult;
    }

    /**
     * Überprüft ein Objekt anhand von benutzerdefinierten Funktionen.
     * Als $specialParams wird ein Array von Closures erwartet, die true zurückgeben falls die Überprüfung erfolgreich war, und ansonsten false.
     * 
     * @param unknown $filterableObject
     * @param unknown $specialParams
     * @return boolean
     */
    protected function checkSpecialParams($filterableObject, $specialParams)
    {
        if (!is_array($specialParams) || count($specialParams) === 0)
        {
            return true;
        }
        foreach ($specialParams as $param => $closure)
        {
            if ($closure($filterableObject) === true)
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Gibt alle Item-IDs eines Objekt-Typen zurück.
     * 
     * @param Array $filterable
     * @return unknown
     */
    protected function getAllItemIds($filterable)
    {
        $result = $this->resource->loadAllIds($filterable);
        return $result;
    }
}