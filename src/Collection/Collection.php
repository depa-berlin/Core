<?php
namespace Depa\Core\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Depa\Core\Interfaces\Arrayable;
use Depa\Core\Interfaces\Jsonable;

/**
 *
 * @author fenrich
 *        
 */
class Collection extends ArrayCollection implements Arrayable, Jsonable
{
    
    /*
     * Id des Items, welches als Standard definiert ist.
     * Es kann immer nur ein Element als Standard definiert sein.
     */
    protected $standardElement = NULL;

    
    
    
    
    public function __construct(array $elements = [])
    {
        $this->elements = $this->getArrayableItems($elements);
    }
    
    /*
     * Gibt alle Elemente als Array zurück
     */
    public function all()
    {
        return $this->toArray();
    }
    
    /**
     * Setzt ein Item als Standard
     *
     * @param mixed $item
     * @throws \Exception
     * @return boolean
     */
    public function setStandard($element)
    {
        
        if(isset($this->elements[$element]))
        {
            $this->standardElement = $element;
            return $element;
        }
        return NULL;
    }
    /**
     * Liefert das Element, welches als Standard definiert ist
     * 
     * @return unknown || NULL
     */
    public function standard()
    {
        return $this->standardElement;
    }
    
    
    
    /**
     * Get the collection of items as JSON.
     *
     * @see \Core\Interfaces\Jsonable::toJson()
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    
    /**
     * Convert the object into something JSON serializable.
     *
     * @see JsonSerializable::jsonSerialize()
     * @return array
     */
    public function jsonSerialize()
    { 
        return array_map(function ($value) {
            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->items);
    }
    
    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param mixed $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof \JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof \Traversable) {
            return iterator_to_array($items);
        }
        
        return (array) $items;
    }
    
    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}

