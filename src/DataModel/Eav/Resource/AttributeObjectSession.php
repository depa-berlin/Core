<?php

//TODO wieso gibt es hier detailierte programmlogik und nicht einfach ein objekt, welches in die session speichert?
/**
 * Resource DataObjectDatabase
 *
 * Sämtliche Zugriffe von Datenobjekten auf die Datenbank
 * greifen auf die Funktionalität dieses Objektes zurück
 *
 */
class Core_Model_Eav_Resource_AttributeObjectSession extends Core_Model_Eav_Resource_AttributeObjectAbstract
{
    private static $instance;

    protected function __construct ()
    {
    }

    public static function getInstance ()
    {
        if (self::$instance === NULL)
        {
            self::$instance = new Core_Model_Eav_Resource_AttributeObjectSession();
        }
        return self::$instance;
    }

    /**
     * Die EntityIds bei Sessions müssen vorher festgelegt werden. In dem Zuge wurde also schon längst sichergestellt das
     * diese Id auch ok und valide ist. Damit die Entität jedoch zugriff erhält möchte das EAV an dieser Stelle noch einmal Prüfen(macht bei
     * Datenbanken mehr Sinn) und deswegen wird das EAV an dieser Stelle ausgetrickst
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param $entityId
     * @return int $entityId
     */
    public function checkEntityId (Core_Model_EavAttributeHandler $object, $entityId)
    {
        if ($entityId != NULL)
        {
            return $entityId;
        }
        else
        {
            throw new Exception('Die EntityId darf bei Sessions niemals NULL sein, wenn man an dieser stelle ankommt!');
        }
    }

    /**
     * Diese Funktion ist etwas das ausschließlich eine Datenbank benötigt, da Sie jedoch aufgerufen wird, muss sie implementiert werden
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     */
    public function insertNewEntity (Core_Model_EavAttributeHandler $object)
    {
    }

    /**
     * Läd alle Werte aus der Session
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @return array
     */
    public function load (Core_Model_EavAttributeHandler $object)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        $it = $session->getIterator();
        $result = array();
        if (isset($it['entries']) && isset($it['entries'][$object->getId()]) && isset($it['entries'][$object->getId()]['value']))
        {
            foreach ($it['entries'][$object->getId()]['value'] as $key => $val)
            {
                $result[] = array('attribute_id' => $val['attribute_id'] , 
                				  'value_' . $val['valuetype'] => $val['value_' . $val['valuetype']] , 
                				  'valuetype' => $val['valuetype'] , 
                				  'name' => $val['key']);
            }
        }
        return $result;
    }

    /**
     * Lösche das Objekt aus der Session
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     */
    public function delete (Core_Model_EavAttributeHandler $object)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        unset($session->entries[$object->getId()]);
    }
    
	/**
     * Fügt einen neuen Wert in die Session ein
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function insertValue (Core_Model_EavAttributeHandler $object, $attributeName, $value)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        $session->entries[$object->getId()]['id'] = $object->getId();
        $session->entries[$object->getId()]['value'][$attributeName] = array('attribute_id' => $object->attributes[$attributeName]['attribute_id'],
        																	 'valuetype' => $object->attributes[$attributeName]['valuetype'],
        																	 'value_' . $object->attributes[$attributeName]['valuetype'] => $value,
        																	 'key' => $attributeName);
    }
    
	/**
     * Löscht einen Wert aus der Session
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     */
    public function deleteValue (Core_Model_EavAttributeHandler $object, $attributeName)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        unset($session->entries[$object->getId()]['value'][$attributeName]);
    }
    
	/**
     * Updatet einen Wert in der Session
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function updateValue (Core_Model_EavAttributeHandler $object, $attributeName, $value)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        $valueField = 'value_'.$object->attributes[$attributeName]['valuetype'];
        $session->entries[$object->getId()]['value'][$attributeName][$valueField] = $value;
    }
    
	/**
     * Holt alle Ids zu den Kindern des übergebenen Objekts
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $childTableName
     * @param string $childIdFieldName
     */
    public function getChildIds (Core_Model_EavAttributeHandler $object, $childTableName, $childIdFieldName)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        $it = $session->getIterator();
        $result = array();
        if (isset($it['entries']) && isset($it['entries'][$object->getId()]) && isset($it['entries'][$object->getId()]['childs']))
        {
            $result = array_keys($it['entries'][$object->getId()]['childs']);
        }
        return $result;
    }
    
	public function insertChild(Core_Model_EavAttributeHandler $object, $childId)
    {
    	$session = new Camesis_Session_Namespace($object->modelName);
    	$session->entries[$object->getId()]['childs'][$childId] = $childId;
    }
    
	public function getDataChildId (Core_Model_EavAttributeHandler $object)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        if(isset($session->entries[$object->getId()]['childId']))
        {
        	return $session->entries[$object->getId()]['childId'];
        }
        else 
        {
        	return 1;
        }
//        return $session->entries[$object->getId()]['childId'];
    }
    
	public function setDataChildId (Core_Model_EavAttributeHandler $object, $childId)
    {
        $session = new Camesis_Session_Namespace($object->modelName);
        $session->entries[$object->getId()]['childId'] = $childId;
    }
}