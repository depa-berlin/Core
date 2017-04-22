<?php
/**
 * 
 */
namespace depaLibraries\Core\DataModel;

abstract class IdCollection extends Collection
{
    protected $itemIds = array();
    
    protected abstract function createItem($id);
    
	public function getIterator() 
	{
		return new \ArrayIterator($this->itemIds);
	}
	/**
	 * @see Countable::count()
	 */
	public function count()
	{
		return count($this->itemIds);
	
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
		if (in_array($itemId, $this->itemIds))
		{
		    $this->items[$itemId] = $this->createItem($itemId);
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
	    parent::removeItem($item);
	    if (is_object($item))
	    {
	        $itemId = $this->getItemId($item);
	    }
	    else
	    {
	        $itemId = $item;
	    }
	    if (in_array($itemId, $this->itemIds))
	    {
	        unset($this->itemIds[array_search($itemId, $this->itemIds)]);
	        return TRUE;
	    }
	}
}