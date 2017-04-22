<?php

/**
 * Resource DataObjectDatabase
 *
 * Sämtliche Zugriffe von Datenobjekten auf die Datenbank
 * greifen auf die Funktionalität dieses Objektes zurück
 *
 */
namespace Core\Model\Eav\Resource;

use Core;
use Core\Model as Model;

class AttributeObjectDatabase extends AttributeObjectAbstract
{
	/**
	 * Eine Instanz dieser Klasse
	 * 
	 * @var Core_Eav_Resource_AttributeObjectDatabase
	 */
    private static $instance;
    /**
     * Die Datenkbankklasse von Camesis
     * 
     * @var Zend_Db_Adapter_Abstract
     */
    private $db;

    /**
     * Konstruktor holt sich die DB
     */    
    protected function __construct ($databaseConnection = NULL)
    {
        if(is_null($databaseConnection))
        {
            $databaseConnection = Core\Registry::get('camesis.db');
        }
        if(!$databaseConnection instanceof Zend_Db_Adapter_Abstract)
        {
            throw new  \Exception('Expected DB-Adapter of type Zend_Db_Adapter_Abstract');
        }
        $this->db = $databaseConnection;
    }
    
    /**
     * Singleton erzeugt eine Instanz wenn noch keine vorhanden
     */
    public static function getInstance ($databaseConnection = NULL)
    {
        if (self::$instance === NULL)
        {
            self::$instance = new Model\Eav\Resource\AttributeObjectDatabase($databaseConnection);
        }
        return self::$instance;
    }

    /**
     * Guckt ob diese EntityId überhaupt vergeben ist
     * 
     * @param Core_Eav_AttributeHandler $object
     * @param $entityId
     * @return mixed $result
     */
    public function checkEntityId(Model\EavAttributeHandler $object, $entityId)
    {
        $query = 'SELECT '.$object->idFieldName.'
        		  FROM '.$object->modelName.'
        		  WHERE '.$object->idFieldName.' = '.$entityId;
        $result = $this->db->fetchOne($query);
        return $result;
    }
    
    /**
     * Lade alle Werte aus der Datenbank
     * 
     * @param Core_Eav_AttributeHandler $object
     * @return array
     */
    public function load (Model\EavAttributeHandler $object)
    {
        // http://www.1keydata.com/sql/sql-coalesce.html
        // SELECT mod_shopingo_customer_attribute.name, COALESCE( mod_shopingo_customer_value.value_varchar, mod_shopingo_customer_value.value_int ) AS value
        // FROM mod_shopingo_customer, mod_shopingo_customer_value, mod_shopingo_customer_attribute
        // WHERE mod_shopingo_customer.entity_id =1
        // AND mod_shopingo_customer.entity_id = mod_shopingo_customer_value.customer_entity_id
        // AND mod_shopingo_customer_attribute.attribute_id = mod_shopingo_customer_value.customer_attribute_attribute_id
        $query = 'SELECT ' . $object->modelName . '_value.*, ' . $object->modelName . '_attribute.name, cms_eav_valuetype.valuetype
    			  FROM  ' . $object->modelName . ', ' . $object->modelName . '_value,' . $object->modelName . '_attribute, cms_eav_valuetype
    			  WHERE ' . $object->modelName . '.entity_id = ' . $object->getId() . ' 
    			  AND ' . $object->modelName . '.entity_id = ' . $object->modelName . '_value.entity_id
    			  AND  ' . $object->modelName . '_value.attribute_id = ' . $object->modelName . '_attribute.attribute_id 
    			  AND ' . $object->modelName . '_attribute.valuetype_valuetype_id = cms_eav_valuetype.valuetype_id';
        $result = $this->db->fetchAll($query);
        return $result;
    }

    /**
     * Lösche das Objekt von der Datenbank
     * 
     * @param Core_Eav_AttributeHandler $object
     */
    public function delete (Model\EavAttributeHandler $object)
    {
        $query = 'DELETE 
        		  FROM ' . $object->modelName . '
        		  WHERE entity_id = ' . $object->getId();
        $this->db->query($query);
    }

    /**
     * Lade alle definierten Attribute aus der Datenbank 
     * 
     * @param Core_Eav_AttributeHandler $object
     * @return array
     */
    public function loadAttributes (Model\EavAttributeHandler $object)
    {
        $query = 'SELECT name, attribute_id, status_multiline, valuetype, filter, validator
        		  FROM ' . $object->modelName . '_attribute, cms_eav_inputfilter, cms_eav_inputvalidator, cms_eav_valuetype
        		  WHERE ' . $object->modelName . '_attribute.valuetype_valuetype_id = cms_eav_valuetype.valuetype_id 
        		  AND ' . $object->modelName . '_attribute.inputfilter_inputfilter_id = cms_eav_inputfilter.inputfilter_id
        		  AND ' . $object->modelName . '_attribute.inputvalidator_inputvalidator_id = cms_eav_inputvalidator.inputvalidator_id';
        $result = $this->db->fetchAll($query);
        return $result;
    }

    /**
     * Füge einen neuen Wert in die Datenbank ein
     * Der Wert wird in der  * _value Tabelle gespeichert
     * 
     * @param Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function insertValue (Model\EavAttributeHandler $object, $key, $value)
    {
        $insertDataArray = array(
        'entity_id' => $object->getId(), 
        'attribute_id' => $object->attributes[$key]['attribute_id'], 
        'value_' . $object->attributes[$key]['valuetype'] => $value);
        $this->db->insert( $object->modelName . '_value', $insertDataArray);
    }
    
    /**
     * Neue Entität in die Datenbank einfügen
     * 
     * @param Core_Eav_AttributeHandler $object
     */
    public function insertNewEntity(Model\EavAttributeHandler $object)
    {
        $parentIdFieldName = $object->getParentIdFieldName();
        $parentIdValue = $object->getParentId();
        if(isset($parentIdFieldName, $parentIdValue))
        {
            $query = 'INSERT
            		  INTO '.$object->modelName.' (entity_id, '.$parentIdFieldName.')
            		  VALUES (NULL, '.$parentIdValue.')';
        }
        else
        {
            $query = 'INSERT
            		  INTO '.$object->modelName.' (entity_id)
            		  VALUES (NULL)';            
        }
        $this->db->query($query);
        $result = $this->db->lastInsertId();
        return $result;
    }

    /**
     * Lösche einen Wert von der Datenbank
     * Der Wert wird aus der  * _value Tabelle gelöscht
     * 
     * @param Core_Eav_AttributeHandler $object
     * @param string $key
     */
    public function deleteValue (Model\EavAttributeHandler $object, $key)
    {
        $query = 'DELETE
	    		  FROM ' . $object->modelName . '_value
	    		  WHERE attribute_id = ' . $object->attributes[$key]['attribute_id'] . '
                  AND entity_id = ' . $object->getId();
        $this->db->query($query);
    }

    /**
     * Update einen Wert in der Datenbank
     * Der Wert wird in der "value" Tabelle geupdatet
     * 
     * @param Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function updateValue (Model\EavAttributeHandler $object, $key, $value)
    {
        $data = array('value_' . $object->attributes[$key]['valuetype'] => $value);
        $where['attribute_id = ?'] = $object->attributes[$key]['attribute_id'];
        $where['entity_id = ?'] = $object->getId();
        $this->db->update( $object->modelName . '_value', $data, $where);
    }
    
    /**
     * Holt alle Ids zu den Kindern des übergebenen Objekts
     * 
     * @param Core_Eav_AttributeHandler $object
     * @param string $childTableName
     * @param string $childIdFieldName
     */
    public function getChildIds(Model\EavAttributeHandler $object, $childTableName, $childIdFieldName)
    {
        $query = 'SELECT '.$childIdFieldName.'
        		  FROM '.$childTableName.'
        		  WHERE parent_id = ?';
        $result = $this->db->fetchCol($query, $object->getId());
        return $result;
    }
}