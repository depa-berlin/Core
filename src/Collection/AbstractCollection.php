<?php
namespace Depa\Core\Collection;

use Depa\Core\Interfaces\Arrayable;
use Depa\Core\Interfaces\Jsonable;

/**
 * sfdguuz
 * @author fenrich
 *        
 */
class AbstractCollection implements \ArrayAccess, Arrayable, \Countable, \IteratorAggregate, Jsonable, \JsonSerializable
{

    /**
     * Die Items der Collection
     *
     * @var array
     */
    protected $items = [];

    /**
     * Erstellt eine neue Collection.
     *
     * @param mixed $items            
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Setze ein Item per Key in die Collection.
     *
     * @param mixed $key            
     * @param mixed $value            
     * @return $this
     */
    public function add($key, $value)
    {
        $this->offsetSet($key, $value);
        
        return $this;
    }

    /**
     * Gibt alle Items der Collection zurück.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Zählt die Anzahl der Items der Collection
     *
     * @see Countable::count()
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Get an item from the collection by key.
     *
     * @param mixed $key            
     * @param mixed $default            
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }
        
        return $default;
    }
    /**
     * Gibt das erste Item der Collection zurück
     * 
     * @return mixed|NULL
     */
    public function getFirst()
    {
        if ($this->count())
        {
            reset($this->items);
            return current($this->items);
        }
        return NULL;
    }
    /**
     * Gibt das letzte Item der Collection zurück.
     * 
     * @return mixed|NULL
     */
    public function getLast()
    {
        if ($this->count())
        {
            return end($this->items);
        }
        return NULL;
    }

    /**
     * Ermittelt in der Collection ein Item anhand des Key.
     *
     * @param mixed $key            
     * @return bool
     */
    public function has($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Ermittelt, ob die Collection leer ist  oder nicht.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }
    
    /**
     * Gibt ein oder mehrere Items zufällig zurück.
     *
     * @param int $amount
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($amount = 1)
    {
        if ($amount > ($count = $this->count())) {
            throw new \InvalidArgumentException("You requested {$amount} items, but there are only {$count} items in the collection");
        }
    
        $keys = array_rand($this->items, $amount);
    
        if ($amount == 1) {
            return $this->items[$keys];
        }
    
        return new static(array_intersect_key($this->items, array_flip($keys)));
    }

    /**
     *
     * @param mixed $key            
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
            return TRUE;
        }
        return FALSE;
    }

    

    /**
     * Get an item at a given offset.
     *
     * @see ArrayAccess::offsetGet()
     * @param mixed $key            
     * @return bool
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * Prüft, ob ein bestimmtes Item existiert.
     *
     * @see ArrayAccess::offsetExists()
     * @param mixed $key            
     * @return bool
     */
    public function offsetExists($key)
    {
        return (isset($this->items[$key]) || array_key_exists($key, $this->items));
    }

    /**
     * Entferne ein Item anhand des übergebenen Key
     *
     * @see ArrayAccess::offsetUnset()
     * @param string $key            
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Setze ein Item für den übergebenen Key.
     *
     * @see ArrayAccess::offsetSet()
     * @param mixed $key            
     * @param mixed $value            
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @see \Core\Interfaces\Arrayable::toArray()
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            if ($value instanceof Arrayable) {
                return $value->toArray();
            } else {
                return $value;
            }
        }, $this->items);
    }

    /**
     * Gibt den Iterator für Items der Collection zurück.
     *
     * @see IteratorAggregate::getIterator()
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
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

?>