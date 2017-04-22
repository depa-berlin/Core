<?php
namespace Depa\Core\Collection;

/**
 *
 * @author fenrich
 *        
 */
class AbstractObjectCollection extends AbstractCollection
{

    /**
     *
     * @param array $value            
     *
     */
    public function __construct($value)
    {
        //value überprüfen so dass kein item NULL ist
        parent::__construct($value);
    }

    /**
     * Liefert ein ItemObject
     * Liefert entsprechend der übergebenen Id das passende ItemObject zurück
     *
     * @param mixed $id            
     * @return object
     */
    public function getItemById($itemId)
    {
        if (isset($this->items[$itemId])) {
            return $this->offsetGet($itemId);
        }
        return NULL;
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
        if (is_object($item)) {
            $itemId = $this->getItemId($item);
        } else {
            $itemId = $item;
        }
        if (isset($this->items[$itemId])) {
            $this->standardItemId = $itemId;
            return TRUE;
        }
        return FALSE;
    }
    /**
     * Prüft, ob ein Item existiert und nicht NULL ist.
     *
     * @see ArrayAccess::offsetExists()
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return (isset($this->items[$key]));
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
     * Entfernt ein Item aus der Collection
     * Das übergebene Item wird aus der Collection entfernt.
     * Es kann ein Objekt oder eine Id übergeben werden.
     * Diese Funktionalität basiert auf der Annahme, dass die ItemId als Key vom ItemArray $this-item verwendet wird.
     *
     * @param mixed $item            
     * @throws \Exception
     * @return bolean
     */
    public function removeItem($item)
    {
        if (is_object($item)) {
            //hat es denn überhaupt die funktion getId???
            $itemId = $this->getItemId($item);
        } else {
            $itemId = $item;
        }
        if (isset($this->items[$itemId])) {
            $this->offsetUnset($itemId);
            return TRUE;
        }
        return FALSE;
    }
}

?>