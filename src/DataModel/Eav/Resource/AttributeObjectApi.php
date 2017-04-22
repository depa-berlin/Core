<?php
namespace Core\Model\Eav\Resource;

use Core;
use Core\Model as Model;

class AttributeObjectApi extends AttributeObjectAbstract
{
    /**
	 * Eine Instanz dieser Klasse
	 * 
	 * @var Core_Eav_Resource_AttributeObjectApi
	 */
    private static $instance;
    
    protected function __construct ()
    {
    }
    /**
     * 
     * Singleton erzeugt eine Instanz wenn noch keine vorhanden 
     */
    public static function getInstance ()
    {
        if (self::$instance === NULL)
        {
            self::$instance = new Model\Eav\Resource\AttributeObjectApi();
        }
        return self::$instance;
    }
    
    /**
     * Lädt Attribute aus der lokalen DB
     * 
     * @param Core_Eav_AttributeHandler $object
     * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
     */
    public function loadAttributes (Model\EavAttributeHandler $object)
    {
    	return Model\Eav\Resource\AttributeObjectDatabase::getInstance()->loadAttributes($object);
    }
    
    /**
     * Erfragt ob diese EntityId überhaupt vergeben ist
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param $entityId
     * @return mixed $result
     */
    public function checkEntityId(Model\EavAttributeHandler $object, $entityId)
    {
    	if ($entityId != NULL)
    	{
    		return $entityId;
    	}
    	else
    	{
    		throw new \Exception('Entity-ID darf nicht NULL sein!');
    	}
    }
    
	/**
     * Lädt alle Werte per API. Sind die Daten des Objektes schon in der Registry, werden die Werte stattdessen aus der Registry geholt
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @return array
     */
    public function load (Model\EavAttributeHandler $object)
    {
    	$eavData = array();
    	$objectData = array();
    	if (Core\Registry::isRegistered($object->modelName . $object->getId()) &&
    	array_key_exists('data', Core\Registry::get($object->modelName . $object->getId())))
    	{
    		$data = Core\Registry::get($object->modelName . $object->getId())['data'];
    		Core\Registry::delete($object->modelName . $object->getId());
    	}
    	else
    	{
    		$data = Core_Api_ApiClientStatic::read($object->apiUrl, array('filter' => 'id', 'filterValue' => $object->getId()))[0];
    	}
		foreach ($data as $key => $val)
		{
			if (isset($object->attributes[$key]))
			{
				$valType = $object->attributes[$key]['valuetype'];
				$eavData[$key] = array('name' => $key, 'valuetype' => $valType, 'value_'.$valType => $val);
			}
			else
			{
				$objectData[$key] = $val;
			}
		}
		Core\Registry::set($object->modelName . $object->getId(), array('eav' => $eavData, 'object' => $objectData));
		return $eavData;
    }
    /**
     * Lösche das Objekt per API
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     */
    public function delete (Model\EavAttributeHandler $object)
    {
    	$data = Core\Registry::get($object->modelName . $object->getId());
    	//TODO: Vielleicht nur ID schicken?
    	Core_Api_ApiClientStatic::destroy($object->apiUrl, $data);
    	Core_Registry::set($object->modelName . $object->getId(), NULL);
    }
	/**
     * Fügt einen neuen Wert ein
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function insertValue (Model\EavAttributeHandler $object, $attributeName, $value)
    {
		$eavData = Core\Registry::get($object->modelName . $object->getId())['eav'];
		$eavData[$attributeName] = $value;
	
    }
    
	/**
     * Löscht einen Wert
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     */
    public function deleteValue (Model\EavAttributeHandler $object, $attributeName)
    {
    	$eavData = Core\Registry::get($object->modelName . $object->getId())['eav'];
    	//unset()? Was will die API? Und das EAV?
    	$eavData[$attributeName] = NULL;
    }
    
	/**
     * Updatet einen Wert
     * 
     * @param Camesis_Core_Eav_AttributeHandler $object
     * @param string $key
     * @param string $value
     */
    public function updateValue (Model\EavAttributeHandler $object, $attributeName, $value)
    {
    	$eavData = Core\Registry::get($object->modelName . $object->getId())['eav'];
    	$eavData[$attributeName] = $value;
    }
    
    public function afterSave(Model\EavAttributeHandler $object)
    {
    	$data = Core\Registry::get($object->modelName . $object->getId());
    	Core_Api_ApiClientStatic::update($object->apiUrl, $data);
    }
}