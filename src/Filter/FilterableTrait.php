<?php
namespace Depa\Core\Filter;

/**
 * Trait der das Filtern beliebiger Objekte erlaubt. Hierfür muss das filterbare Objekt einige Funktionen implementieren:
 * - getId() für DB-Persistenz
 * - isNull() um zu überprüfen ob der Record schon in der DB ist
 * - getFilterObjectTable() um das DB-Schema zu erfragen
 * - getIdColumn() um das DB-Schema zu erfragen
 * 
 * Der Trait bietet grundlegende Funktionalität um mit Filtern zu arbeiten.
 * 
 * @author alex
 *
 */
trait FilterableTrait
{
    protected $filters = array();
    
    protected $categories = array();
    
    abstract public function getId();
    
    abstract public function isNull();
    
    abstract public function getFilterObjectTable();
    
    abstract public function getIdColumn();
    
    /**
     * Gibt true zurück falls dieses Objekt schon einen Filterwert in der übergebenen Kategorie besitzt, ansonsten false.
     * 
     * @param Category $category
     * @return boolean
     */
    public function inFilterCategory(Category $category)
    {
        if (in_array($category, $this->categories))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Gibt alle Filterwerte, die dieses Objekt zu der übergebenen Kategorie kennt, zurück.
     * 
     * @param Category $category
     * @return multitype:|NULL
     */
    public function getFilterValue(Category $category)
    {
        if ($this->inFilterCategory($category))
        {
            return $this->filters[$category->getId()];
        }
        return null;
    }
    
    /**
     * Setzt alle Filterwerte für eine bestimmte Kategorie.
     * 
     * @param Category $category
     * @param unknown $value
     * @return boolean
     */
    public function setFilterValue(Category $category, $value)
    {
        if (!$this->inFilterCategory($category))
        {
            $this->categories[] = $category;
        }
        $this->filters[$category->getId()] = [$value];
        return $this->filters[$category->getId()];
        return false;
    }
    
    /**
     * Fügt einen Filterwert für eine Kategorie hinzu.
     * 
     * @param Category $category
     * @param unknown $value
     * @return multitype:|NULL
     */
    public function addFilterValue(Category $category, $value)
    {
        if (!$this->inFilterCategory($category))
        {
            $this->categories[] = $category;
        }
        $this->filters[$category->getId()][] = $value;
        return $this->filters[$category->getId()];
    }
    
    /**
     * Entfernt einen Filterwert für eine Kategorie.
     * 
     * @param Category $category
     * @param unknown $value
     * @return boolean
     */
    public function removeFilterValue(Category $category, $value)
    {
        if ($this->inFilterCategory($category))
        {
            unset($this->filters[$category->getId()][$value]);
            return true;
        }
        return false;
    }
    
    /**
     * Überprüft ob dieses Objekt den übergebenen Filterwert für die übergebene Kategorie besitzt.
     * 
     * @param Category $category
     * @param unknown $value
     * @return boolean
     */
    public function hasFilterValueForCategory(Category $category, $value)
    {
        if ($this->inFilterCategory($category) && in_array($this->getFilterValueForCategory($category), $value))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Gibt alle Filterdaten zurück
     * 
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }
    
    /**
     * Lädt Filterdaten aus der Datenbank. Hierfür muss das Objekt eine ID haben.
     * 
     * @throws \Exception
     */
    public function loadFilterData($databaseConnection)
    {
        $id = $this->getId();
        if ($this->isNull())
        {
            throw new \Exception('Unable to load filters for Null-Object');
        }
        $resource = Resource\Database::getInstance($databaseConnection);
        $filterArray = $resource->loadFilters($this);
        foreach ($filterArray as $filter)
        {
            $category = new Category($filter['category_id']);
            $this->addFilterValue($category, $filter['filter']);
        }
    }
    
    /**
     * Speichert Filterdaten in die Datenbank. Hierfür muss das Objekt eine ID haben.
     * 
     * @throws \Exception
     */
    public function saveFilterData($databaseConnection)
    {
        $id = $this->getId();
        if ($this->isNull())
        {
            throw new \Exception('Unable to save filters for Null-Object');
        }
        $resource = Resource\Database::getInstance($databaseConnection);
        $resource->saveFilters($this);
    }
}