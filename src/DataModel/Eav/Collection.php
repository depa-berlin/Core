<?php
use Depa\Core\DataModel\Eav\AttributeObject;

/**
 * Die Abstrakte Kollektionsklasse, welche allen Klassen die Funktionalität 
 * einer Sammlung schenkt, die Sie einbinden.
 * 
 *
 */
class Core_Model_Eav_Collection implements IteratorAggregate, Countable
{
	/**
	 * Hier werden alle Einträge festgehalten
	 * 
	 * @var array
	 */
    protected $items = array();
    /**
     * Status wird noch nicht verwendet
     * 
     * @var bool
     */
    public $collectionLoadedStatus;
    /**
     * Name dieser Kollektion
     * 
     * @var string
     */
    public $collectionName;

    /**
     * setzt den collectionsnamen
     * @param unknown_type $modelName
     */
    protected function init($collectionName)
    {
        $this->collectionName = $collectionName;
    }
    
    /**
     * Set collection item class name
     *
     * @param   string $className
     * @return  Shopingo_Core_Model_Collection
     */
    function setItemObjectClass ($className)
    {
        //TODO
        /*$className = Mage::getConfig()->getModelClassName($className);

        $this->itemObjectClass = $className;
        return $this;*/
    }

    /**
     * Hole ein neues lehre Item-Objekt
     *
     * @return Shopingo_Core_Model_Collection
     */
    public function getNewEmptyItem ()
    {
        //TODO
        //return new $this->_itemObjectClass();
    }

    /**
     * Hole alle Einträge der Collection
     *
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
    }

    /**
     * Füge einen Eintrag hinzu
     *
     * @param   AttributeObject $item
     * @return  Collection
     */
    public function addItem (AttributeObject $item)
    {
        $itemId = $this->getItemId($item);
        if (! is_null($itemId))
        {
            if (isset($this->items[$itemId]))
            {
                throw new Exception('Item (' . get_class($item) . ') mit der gleichen id "' . $item->getId() . '" existiert schon');
            }
            $this->items[$itemId] = $item;
        }
        else
        {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Hole alle Id's der Items
     *
     * @return array
     */
    public function getAllIds ()
    {
        $ids = array();
        foreach ($this->getItems() as $item)
        {
            $ids[] = $this->getItemId($item);
        }
        return $ids;
    }

    /**
     * Hole die Item Id
     *
     * @param Shopingo_Model_Core_AttributeObject $item
     * @return mixed
     */
    protected function getItemId (AttributeObject $item)
    {
        return $item->getId();
    }

    /**
     * Hole das erste Item der Collection
     *
     * @return Shopingo_Core_Model_DataObject
     */
    public function getFirstItem ()
    {
        if (count($this->items))
        {
            reset($this->items);
            return current($this->items);
        }
    }

    /**
     * Hole das letzte Item der Collection
     *
     * @return Shopingo_Core_Model_DataObject
     */
    public function getLastItem ()
    {
        if (count($this->items))
        {
            return end($this->items);
        }
    }

    /**
     * Retrieve collection all items count
     * gibt selbe ergebnis wie count
     * @return int
     */
    /*public function getSize ()
    {
        //gibt gleiche ergebnis wie count
        if (is_null($this->_totalRecords))
        {
            $this->_totalRecords = count($this->getItems());
        }
        return intval($this->_totalRecords);
    }*/

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function getColumnValues ($colName)
    {
        $col = array();
        foreach ($this->getItems() as $item)
        {
            $col[] = $item->getData($colName);
        }
        return $col;
    }

    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function getItemsByColumnValue ($column, $value)
    {
        $res = array();
        foreach ($this as $item)
        {
            if ($item->getData($column) == $value)
            {
                $res[] = $item;
            }
        }
        return $res;
    }

    /**
     * Suche das erste Item in dem das Feld ($column) mit dem Wert ($value) vorhanden ist
     *
     * @param   string $column
     * @param   mixed $value
     * @return  Shopingo_Model_Core_AttributeObject || null
     */
    public function getItemByColumnValue ($column, $value)
    {
        foreach ($this as $item)
        {
            if ($item->getData($column) == $value)
            {
                return $item;
            }
        }
        return null;
    }

    /**
     * Hole das Item per ItemId
     *
     * @param   mixed $idValue
     * @return  Shopingo_Core_Model_DataObject
     */
    public function getItemById ($idValue)
    {
        //hä???
        //das macht das doch nicht
        if (isset($this->items[$idValue]))
        {
            return $this->items[$idValue];
        }
        return null;
    }
    
    public function removeItemById ($idValue)
    {
        if (isset($this->items[$idValue]))
        {
            unset($this->items[$idValue]);
        }
        return $this;
    }

    /**
     * Implementation von IteratorAggregate::getIterator()
     */
    public function getIterator ()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Gibt die Anzahl der von der Collection geladenen Items zurück
     *
     * @return int
     */
    public function count ()
    {
        return count($this->items);
    }
    
	/**
     * Lösche Item aus der Collection per ItemId
     *
     * @param   mixed $key
     * @return  Varien_Data_Collection
     */
    public function removeItemByKey($key)
    {
        if (isset($this->items[$key])) 
        {
            unset($this->items[$key]);
        }
        return $this;
    }

    /**
     * Lehre die Collection
     *
     * @return Camesis_Core_Eav_Collection
     */
    public function clear ()
    {
        $this->setLoadedStatus(false);
        $this->items = array();
        return $this;
    }

    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function isLoaded ()
    {
        return $this->collectionLoadedStatus;
    }

    /**
     * Set collection loading status flag
     *
     * @param boolean $flag
     * @return unknown
     */
    protected function setLoadedStatus ($status = true)
    {
        $this->collectionLoadedStatus = $status;
        return $this;
    }
}