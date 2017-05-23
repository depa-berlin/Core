<?php
namespace Depa\Core\Filter;

/**
 * Query für filterbare Objekte. Datenobjekt das alle Parameter enthält um ein Set an Objekten nach bestimmten Kriterien zu filtern.
 * Das Query besitzt zwei Arten von Parametern.
 * - Normale Parameter benutzen die Schnittstelle des FilterableTraits und folgen daher der Konvention von Kategorien denen Filterwerte zugeordnet werden.
 * - Special-Parameter können benutzt werden um Filteraktionen durchzuführen die nicht die Schnittstelle des FilterableTraits benutzen.
 * Es werden mehrere filterbare Klassen per Query unterstützt.
 * Will man so zum Beispiel Äpfel und Birnen vergleichen funktioniert dass solange wie diese beide den Filterable-Trait benutzen und in den gleichen Kategorien Filter haben.
 * 
 * @author alex
 *
 */
class Query
{
    /**
     * Array mit Parametern im Format [[categoryId] => [filter1, filter2]]
     * 
     * @var array
     */
    protected $params = array();
    /**
     * Array mit Special-Parametern im Format [parameterName => Closure]
     * 
     * @var array
     */
    protected $specialParams = array();
    /**
     * Array mit filterbaren Objekten
     * 
     * @var array
     */
    protected $filterables = array();
    
    protected $pagination;
    
    /**
     * Fügt einen Parameter zum Query-Objekt hinzu.
     * 
     * @param Category $category
     * @param string $filter
     * @return boolean
     */
    public function addParam(Category $category, $filter)
    {
        if (! $this->hasParam($category, $filter))
        {
            $this->params[$category->getId()][] = $filter;
        }
        return $this;
    }
    
    /**
     * Entfernt einen Parameter aus dem Query-Objekt
     * 
     * @param Category $category
     * @param string $filter
     * @return boolean
     */
    public function removeParam(Category $category, $filter)
    {
        if ($this->hasParam($category, $filter))
        {
            unset($this->params[$category->getId()][array_search($filter, $this->params[$category->getId()])]);
        }
        return $this;
    }
    
    /**
     * Setzt einen Special-Parameter.
     * Dies sollte eine Closure sein, die ein filterbares Objekt entgegennimmt und dann true bei Erfolg oder ansonsten false zurückgibt.
     * 
     * @param string $paramName
     * @param Closure $closure
     * @throws \Exception
     */
    public function setSpecialParam($paramName, $closure)
    {
        if (!$closure instanceof \Closure)
        {
            throw new \Exception('Expected Closure as Special Parameter!');
        }
        $this->specialParams[$paramName] = $closure;
        return $this;
    }
    
    /**
     * Gibt alle Special-Parameter zurück.
     * 
     * @return array:
     */
    public function getSpecialParams()
    {
        return $this->specialParams;
    }
    
    /**
     * Setzt alle Filter-Parameter. Das Format wird nicht überprüft, also wird es zu Fehlern kommen wenn es falsch ist.
     * 
     * @param array $paramArray
     */
    public function setParams($paramArray)
    {
        $this->params = $paramArray;
        return $this;
    }
    
    /**
     * Überprüft ob ein Parameter gesetzt ist.
     * 
     * @param Category $category
     * @param string $filter
     * @return boolean
     */
    public function hasParam(Category $category, $filter)
    {
        if (array_key_exists($category->getId(), $this->params) && in_array($filter, $this->params[$category->getId()]))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Gibt alle Parameter zurück.
     * 
     * @return array:
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Gibt true zurück wenn das Objekt verarbeitet werden kann, und false falls keine Objekte in dem gewünschten Set sein können.
     * 
     * @return boolean
     */
    public function isValid()
    {
        if (count($this->filterables) > 0)
        {
            return true;
        }
        return false;
    }
    
    /**
     * Fügt ein filterbares Objekt hinzu. Es wird der Klassen-Name erwartet.
     * 
     * @param string $filterable
     * @return boolean
     */
    public function addFilterableClass($filterable)
    {
        if (!$this->hasFilterable($filterable))
        {
            $reflector = new \ReflectionClass($filterable);
            $filterObjectTableMethod = $reflector->getMethod('getFilterObjectTable');
            $data['filterObjectTable'] = $filterObjectTableMethod->invoke($reflector->newInstanceWithoutConstructor());
            $idColumnMethod = $reflector->getMethod('getIdColumn');
            $data['idColumn'] = $idColumnMethod->invoke($reflector->newInstanceWithoutConstructor());
            $data['class'] = $filterable;
            $this->filterables[] = $data;
        }
        return $this;
    }
    
    /**
     * Entfernt ein filterbares Objekt.
     * 
     * @param Object $filterable
     * @return boolean
     */
    public function removeFilterable($filterable)
    {
        if ($this->hasFilterable($filterable))
        {
            unset($this->filterables[array_search($filterable, $this->filterables)]);
        }
        return true;
    }
    
    /**
     * Überprüft ob das Query schon ein filterbares Objekt mit dem übergebenen Klassen-Namen besitzt.
     * 
     * @param Object $filterable
     * @return boolean
     */
    public function hasFilterable($filterable)
    {
        foreach ($this->filterables as $f)
        {
            if ($f['class'] === $filterable)
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Gibt alle filterbaren Objekte nach denen gequeried wird zurück.
     * 
     * @return multitype:
     */
    public function getFilterables()
    {
        return $this->filterables;
    }
    
    public function setPagination($page, $pageSize)
    {
        $this->pagination = ['page' => $page, 'pageSize' => $pageSize];
        return $this->pagination;
    }
    
    public function getPagination()
    {
        return $this->pagination;
    }
}