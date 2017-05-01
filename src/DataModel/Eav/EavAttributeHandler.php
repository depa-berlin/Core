<?php
namespace Depa\Core\DataModel\Eav;


/**
 * @author mirkofenrich
 * Die Klasse stellt die Grundfunktionalitäten zur Verfügung die man brauch
 * um Daten zu laden, zu speichern und zu löschen
 */
abstract class EavAttributeHandler extends AttributeObject
{
    /**
     * Name des verwendenden Models
     * @var string
     */
    public $modelName;
    /**
     * URL unter der die API-Schnittstelle ist.
     * @var string
     */
    public $apiUrl;
    /**
     * Name der Modelkollektion
     * @var string
     */
    protected $modelCollectionName;
    /**
     * Alle möglichen Attribute werden in diesem Array festgehalten
     * @var array
     */
    public $attributes = array();
    /**
     * info ob die Id schon gesetzt ist oder nicht
     * @var boolean
     */
    protected $entityIdStatus = false;
    protected $resourceType = 'Model\Eav\Resource\AttributeObjectDatabase';

    /**
     * Datenbankverbindung
     * 
     * @var unknown
     */
    protected $resource;
    
    /**
     * Konstruktor - läd Attribute, setzt die Id falls Id gültig
     * @param mixed $entityId
     */
    protected function __construct ($entityId = null, $resource = null)
    {
        if (! is_null($resource))
        {
            $this->setResource($resource);
        }
        $this->loadAttributes();
        if ($entityId == null)
        {
            return;
        }
        $this->setIdFieldName();
        if ($this->checkEntityIdStatus($entityId))
        {
            $this->setEntityId($entityId);
            $this->load($this->database);
        }
         //TODO: Exception bei ungültiger Id?!
    }

    /**
     * Setzt den Modelnamen und den Kollektionsnamen
     * @param string $modelName
     */
    protected function init ($modelName, $apiUrl = NULL)
    {
        $this->modelName = $modelName;
        $this->apiUrl = $apiUrl;
        $this->modelCollectionName = $modelName . '_collection';
    }

    /**
     * überprüft ob angegebene Id vorhanden
     * @param mixed $entityId
     * @return boolean
     */
    final protected function checkEntityIdStatus ($entityId)
    {
        if ($this->entityIdStatus == true)
        {
            return true;
        }
        $ret = $this->getResource()->checkEntityId($this, $entityId);
        if ($ret == $entityId)
        {
            $this->entityIdStatus = true;
            return true;
        }
        return false;
    }

    /**
     * Gibt die Datenbankklasse zurück
     * @return Core_Eav_Resource_AttributeObjectDatabase
     */
    private function getResource ()
    {
        $type = $this->resourceType;
        //DEPRECATED (vor PHP 5.3):
        //return call_user_func_array(array($type , 'getInstance'));
        return $type::getInstance($this->resource);
    }

    /**
     * lädt alle Attribute und speichert diese in einem Array
     */
    protected function loadAttributes ()
    {
        $attributeArray = $this->getResource()->loadAttributes($this);
        foreach ($attributeArray as $key => $value)
        {
            $this->attributes[$value['name']]['attribute_id'] = $value['attribute_id'];
            $this->attributes[$value['name']]['status_multiline'] = $value['status_multiline'];
            $this->attributes[$value['name']]['valuetype'] = $value['valuetype'];
            $this->attributes[$value['name']]['filter'] = $value['filter'];
            $this->attributes[$value['name']]['validator'] = $value['validator'];
        }
    }

    /**
     * Lädt die Daten in das Objekt, so dass es für Abfragen vorbereitet ist.
     */
    public function load ()
    {
        //this->beforeLoad();
        if (! $this->entityIdStatus)
        {
            return;
        }
        $result = $this->getResource()->load($this);
        $ar = array();
        foreach ($result as $key => $val)
        {
            $ar[$val['name']] = $val['value_' . $val['valuetype']];
        }
        $this->data = $ar;
        $this->originalData = $ar;
        $this->dataChangeStatus = false;
        $this->afterLoad();
    }
    
    protected function afterLoad()
    {
        
    }

    /**
     * Erstellt in der Datenbank eine neue Entität
     */
    private function createNewEntity ()
    {
        $entityId = $this->getResource()->insertNewEntity($this);
        $this->setEntityId($entityId);
        $this->afterCreateNewEntity();
    }

    protected function beforeCreateNewEntity ()
    {
    }
    
    /**
     * Funktion wird aufgerufen nachdem eine neue Entität in der Db erstellt wurde
     */
    protected function afterCreateNewEntity ()
    {
    }

    /**
     * Funktion überprüft ob DELETE, INSERT oder UPDATE ausgeführt werden muss 
     */
    protected function save ()
    {
        $this->getResource()->beforeSave($this);
        if ($this->isDeleted())
        {
            return $this->delete();
        }
        if ($this->getId() == null)
        {
            $this->createNewEntity();
        }
        if (! $this->hasDataChanges())
        {
            return $this;
        }
        //neuen DataArray in foreach durchlaufen und werte mit altem DataArray vergleichen
        foreach ($this->data as $attribute => $value)
        {
            $value = Filter::factory($this->attributes[$attribute]['filter'], $value);
            //TODO: evtl überprüfen ob Wert nach Filter anders und nur dann data überschreiben?
            $this->data[$attribute] = $value;
            if (! array_key_exists($attribute, $this->originalData))
            {
                if (isset($value) && $value !== '')
                {
                    //Wenn der key in dem alten DataArray nicht vorhanden ist, der neue Wert nicht leer und der Attributname -> INSERT
                    $this->getResource()->insertValue($this, $attribute, $value);
                }
            }
            else
            {
                if (! isset($value) || $value === '')
                {
                    //Wenn der Wert leer ist -> DELETE
                    $this->getResource()->deleteValue($this, $attribute);
                }
                elseif ($value != $this->originalData[$attribute])
                {
                    //Wenn sich der Wert einfach nur geändert hat -> UPDATE
                    $this->getResource()->updateValue($this, $attribute, $value);
                }
            }
        }
        $diffs = array_diff_key($this->originalData, $this->data);
        foreach ($diffs as $attribute => $value)
        {
            //Wenn im neuen DataArray Werte nicht mehr vorhanden sind, welche im alten noch waren -> DELETE
            $this->getResource()->deleteValue($this, $attribute);
        }
        $this->originalData = $this->data;
        $this->getResource()->afterSave($this);
    }
    /**
     * Daten des Objektes löschen
     */
    private function delete ()
    {
        //$this->beforeDelete();
        //TODO:Delete vll in eine Transaktion verpacken?
        $this->getResource()->delete($this);
        $this->unsetData(null);
        $this->setEntityId(null);
         //$this->afterDelete();
    }

    protected function getChildIdArray ($childTableName, $childIdFieldName = 'entity_id')
    {
        $result = $this->getResource()->getChildIds($this, $childTableName, $childIdFieldName);
        return $result;
    }

    /**
     * Gibt die fortlaufende Id der Kinder zurück
     * 
     * @return int
     */
    public function getDataChildId ()
    {
        return $this->getResource()->getDataChildId($this);
    }

    /**
     * Setzt die fortlaufende ID
     * 
     * @param int $childId
     */
    public function setDataChildId ($childId)
    {
        $this->getResource()->setDataChildId($this, $childId);
    }

    public function insertChild ($childId)
    {
        $this->getResource()->insertChild($this, $childId);
    }

    /**
     * 
     * 
     * @param unknown_type $eav
     */
    public function copyEav (EavAttributeHandler $eav)
    {
        foreach ($this->attributes as $key => $value)
        {
            $eavValue = $eav->getData($key);
            if (! empty($eavValue))
            {
                $this->setData($key, $eavValue);
            }
        }
        $this->save();
    }
    
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $resource;
    }
}