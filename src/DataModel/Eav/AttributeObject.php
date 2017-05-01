<?php
namespace Depa\Core\DataModel\Eav;
/**
 * DataObject
 *
 * Es wird die Grundfunktionalität für das Abfragen eines Daten-Array implementiert
 * Hier werden alle Werte zu ihren Attributen in einem Array aufbewahrt. 
 */


abstract class AttributeObject
{
    /**
     * Objekteigenschaften
     * @var $data array
     */
    protected $data = array();
    /**
     * Die Objekteigenschaften als Kopie um hinterher 
     * vergleichen zu können was sich geändert hat
     * @var $originalData array
     */
    protected $originalData = array();
    /**
     * Status für Markierung von Datenänderungen
     * @var $dataChangeStatus bool
     */
    protected $dataChangeStatus = false;
    /**
     * Status für die Markierung des Objektes, wenn es gelöscht wurde.
     * Es können noch Daten vorhanden sein, dürfen aber nicht mehr verwendet werden.
     *
     * @var bool
     */
    protected $deletedStatus = false;
    /**
     * Name vom ID-Feld des Objektes
     * @var string
     */
    public $idFieldName = null;
    /**
     * Wert des ID-Felds 
     * @var int
     */
    protected $idFieldValue = null;
    /**
     * Name des Parent-Id-Felds
     * @var string
     */   
    protected $parentIdFieldName = null;
    /**
     * ParentId
     * @var int
     */
    protected $parentIdFieldValue = null;
    
    
    /**
     * Constructor
     */
    private function __construct ()
    { }

    /**
     * Setzt den Namen des Id-Feldes des Objektes
     * @param   string $name
     * @return  Core_Eav_DataObject
     */
    public function setIdFieldName ($name = null)
    {
        if ($name == null)
        {
            $name = 'entity_id';
        }
        $this->idFieldName = $name;
        return $this;
    }

    /**
     * Gibt den Namen des Id-Feldes des Objektes zurück
     * @return string $this->idFieldName
     */
    public function getIdFieldName ()
    {
        return $this->idFieldName;
    }

    /**
     * Gibt die  ID des Objektes zurück
     * @return mixed $this->idFieldValue
     */
    public function getId ()
    {
        return $this->idFieldValue;
    }

    /**
     * Setzt die ID des Objektes
     *
     * @param   mixed $value
     * @return  Core_Eav_DataObject
     */
    public function setEntityId ($value)
    {
        $this->idFieldValue = $value;
        return $this;
    }
    
    
    
     /**
     * Setzt den Namen des ParentId-Feldes des Objektes
     * @param   string $name
     * @return  Core_Eav_DataObject
     */
    public function setParentIdFieldName ($name = null)
    {
        if ($name == null)
        {
            $name = 'parent_id';
        }
        $this->parentIdFieldName = $name;
        return $this;
    }

    /**
     * Gibt den Namen des ParentId-Feldes des Objektes zurück
     * @return string $this->idFieldName
     */
    public function getParentIdFieldName ()
    {
        return $this->parentIdFieldName;
    }

    /**
     * Gibt die  ParentID des Objektes zurück
     * @return mixed $this->idFieldValue
     */
    public function getParentId ()
    {
        return $this->parentIdFieldValue;
    }
	/**
     * Setzt die ParentID des Objektes
     *
     * @param   mixed $value
     * @return  Core_Model_DataObject
     */
    public function setParentId ($value)
    {
        $this->parentIdFieldValue = $value;
        return $this;
    }
    
    /**
     * Holt Daten aus dem Objekt (Entität)
     * 
     * @param   string $key
     * @return  mixed
     */
    public function getData ($key = NULL)
    {
    	if ($key == NULL)
    	{
    		return $this->data;
    	}
        if (isset($this->data[$key]))
        {
            return $this->data[$key];
        }
        return null;
    }

    /**
     * Schreibt Daten in das Objekt.
     * Vorhandene Daten werden überschrieben
     * Wenn $key ein Array ist, werden alle Daten überschrieben
     * @param string $key
     * @param mixed $value
     * @return Core_Eav_DataObject
     */
    public function setData ($key, $value = null)
    {
        $this->dataChangeStatus = true;
        if (is_array($key))
        {
            $this->data = $key;
        }
        else
        {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Löscht die Daten von Objekt
     * Wenn NULL übergeben wird, werden alle Daten gelöscht.
     * Wenn Schlüssel übergeben wird, wird nur dieser Datensatz gelöscht.
     * @param string $key
     * @return Core_Eav_DataObject
     */
    public function unsetData ($key)
    {
        $this->dataChangeStatus = true;
        if (is_null($key))
        {
            $this->data = array();
        }
        else
        {
            unset($this->data[$key]);
        }
        return $this;
    }

    /**
     * Fügt Daten zum Objekt hinzu.
     * Vorhandene Daten bleiben erhalten oder werden mit aktualisierten Daten überschrieben
     *
     * @param array $arr
     * @return Core_Eav_DataObject
     */
    public function addData (array $arr)
    {
        $this->dataChangeStatus = true;
        $this->data = array_merge($this->data, $arr);
        return $this;
    }

    /**
     * Gibt den Status darüber zurück, ob das Objekt gelöscht wurde.
     *
     * @return boolean
     */
    public function isDeleted ()
    {
        return $this->deletedStatus;
    }

    /**
     * Gibt Status darüber zurück, ob sich Daten geändert haben
     *
     * @return boolean
     */
    public function hasDataChanges ()
    {
        return $this->dataChangeStatus;
    }

    /**
     * Ist das Objekt ohne Daten?
     *
     * @return boolean
     */
    public function isEmpty ()
    {
        if (empty($this->data))
        {
            return true;
        }
        return false;
    }
}