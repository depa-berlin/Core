<?php
namespace Core\Model\Eav\Resource;

use depaLibraries\Core\Singleton;
use depaLibraries\Core\DataModel as DataModel;

class AttributeObjectAbstract extends Singleton
{
	/**
     * Funktion wird vom AttributeHandler aufgerufen, bevor die Veränderungen an die Resource übertragen werden.
     */
    public function beforeSave (DataModel\EavAttributeHandler $object)
    {}
	/**
     * Funktion wird vom AttributeHandler aufgerufen, nachdem alle Veränderungen an die Resource übertragen wurden.
     */
    public function afterSave(DataModel\EavAttributeHandler $object)
    {}
}