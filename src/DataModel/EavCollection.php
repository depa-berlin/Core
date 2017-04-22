<?php
namespace depaLibraries\Core\DataModel;

use depaLibraries\Core\Collection\AbstractObjectCollection;

class EavCollection extends AbstractObjectCollection
{
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
     * Hole alle Einträge der Collection
     *
     * @return array
     */
    public function getItems ()
    {
        return $this->items;
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
    protected function getItemId (Eav\AttributeObject $item)
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