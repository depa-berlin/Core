<?php
namespace Depa\Core\Filter;

/**
 * Filterkategorie. Reines Datenobjekt das zur Zeit noch nichts macht, aber einen Schnittpunkt bietet um Filterfunktonalität zu erweitern.
 * 
 * @author alex
 *
 */
class Category
{
    protected $id;
    
    protected $name;
    
    protected $filterable;
    
    protected $databaseConnection;
    
    public function __construct($filterable, $id = null, $databaseConnection = null)
    {
        $this->databaseConnection = $databaseConnection;
        $this->filterable = $filterable;
        if ($id !== null && $databaseConnection !== null)
        {
            $this->load($filterable, $id);
            $this->id = $id;
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
        return $this->getName();
    }
    
    public function save($databaseConnection = null)
    {
        if ($databaseConnection === null)
        {
            $databaseConnection = $this->databaseConnection;
        }
        $resource = Resource\Database::getInstance(databaseConnection);
        $resource->saveFilterCategoryData(array('id' => $this->getId(), 'name' => $this->getName(), 'filterable' => $this->filterable));
    }
    
    public function load($filterable, $id)
    {
        $resource = Resource\Database::getInstance($this->databaseConnection);
        $data = $resource->loadFilterCategoryData($filterable, $id);
        $this->setName($data['name']);
    }
}