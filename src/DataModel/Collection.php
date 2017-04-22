<?php
/**
 * 
 */
namespace Depa\Core\DataModel;

use depaLiebraries\Core\Interfaces\NullObject;

class Collection implements \IteratorAggregate, \Countable
{
    /*
     * 
     */
    protected $items = array();
    /*
     * Id des Items, welches als Standard definiert ist.
     * Es kann immer nur ein Item als Standard definiert sein.
     */
    protected $standardItemId = NULL;
	/* 
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator() 
	{
		return new \ArrayIterator($this->items);
	}
	/**
	 * @see Countable::count()
	 */
	public function count()
	{
		return count($this->items);
	
	}
	/**
	 * @see \Core\Definition\NullObject::isNull()
	 */
	public function isNull() 
	{
		if ($this->count() == 0)
		{
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * Liefert ein ItemObject
	 * Liefert entsprechend der übergebenen Id das passende ItemObject zurück
	 *
	 * @param   mixed $id
	 * @return  object
	 */
	public function getItemById ($itemId)
	{
		if (isset($this->items[$itemId]))
		{
			return $this->items[$itemId];
		}
		return NULL;
	}
	/**
	 * Entfernt ein Item aus der Collection
	 * Das übergebene Item wird aus der Collection entfernt. Es kann ein Objekt oder eine Id übergeben werden.
	 * Diese Funktionalität basiert auf der Annahme, dass  die ItemId als Key vom ItemArray $this-item verwendet wird.
	 * 
	 * @param mixed $item
	 * @throws \Exception
	 * @return bolean
	 */
	public function removeItem($item)
	{
		if (is_object($item))
		{
			$itemId = $this->getItemId($item);
		}
		else
		{
			$itemId = $item;
		}
		if (isset($this->items[$itemId]))
		{
			unset($this->items[$itemId]);
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * Setzt ein Item als Standard
	 * 
	 * @param mixed $item
	 * @throws \Exception
	 * @return boolean
	 */
	public function setStandardItem($item)
	{
		if (is_object($item))
		{
			$itemId = $this->getItemId($item);
		}
		else{
			$itemId = $item;
		}
		if(isset($this->items[$itemId]))
		{
			$this->standardItemId = $itemId;
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * Liefert das ItemObject, welches als Standard definiert ist
	 * 
	 * @return object || NULL
	 */
	public function getStandardItem()
	{
		return $this->getItemById($this->standardItemId);
	}
	/**
	 * Fügt ein ItemObject hinzu
	 *
	 * @param   object $item
	 * @return
	 */
	public function addItem ($item)
	{
		$itemId = $item->getId($item);
		if (! is_null($itemId))
		{
			if (isset($this->items[$itemId]))
			{
				throw new \Exception('Item (' . get_class($item) . ') mit der gleichen id "' . $item->getId() . '" existiert schon');
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
	 * Liefert Id des Items
	 * Liefert die Id des ItemObjectes zurück, wenn diese vorhanden ist. Dabei wird u.a. überprüft, 
	 * ob das ItemObject die erforderliche Funktion getId() besitzt.
	 * 
	 * @param unknown $item
	 * @throws \Exception
	 * @return unknown
	 */
	private function getItemId($item)
	{
		if(!method_exists($item, 'getId'))
		{
			throw new \Exception('Item (' . get_class($item) . ') enthält keine Funktion getId()');
		}
		$itemId = $item->getId($item);
		if (is_null($itemId))
		{
			throw new \Exception('Item (' . get_class($item) . ') enthält keine ID');
		}
		return $itemId;
	}

}