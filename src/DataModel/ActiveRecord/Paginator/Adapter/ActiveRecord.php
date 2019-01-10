<?php
namespace Depa\Core\DataModel\ActiveRecord\Paginator\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use Depa\Core\DataModel\ActiveRecord\ActiveRecord;

/**
 *
 * @author fenrich
 *        
 */
class ActiveRecord implements AdapterInterface
{

    /**
     *
     * @var \Core\Model\ActiveRecord
     */
    protected $activeRecord;

    /**
     *
     * @var array||NULL
     */
    protected $conditions;

    protected $sort;

    /**
     *
     * @param ActiveRecord $activeRecord            
     * @param array||NULL $conditions            
     * @param unknown $sort            
     */
    public function __construct(ActiveRecord $activeRecord, $conditions = NULL, $sort = NULL)
    {
        $this->activeRecord = $activeRecord;
        $conditionsTmp = NULL;
        
        if (! is_null($conditions)) {
            $conditionsTmp = array();
            foreach ($conditions as $key => $value) {
                if ($activeRecord->hasAttribute($key)) {
                    $conditionsTmp[$key] = $value;
                }
            }
        }
        $this->conditions = $conditionsTmp;
        
        $this->sort = $sort;
    }
    
    

    /**
     * Returns an array of items for a page.
     *
     * @param int $offset
     *            Page offset
     * @param int $itemCountPerPage
     *            Number of items per page
     * @return array
     * @see \Zend\Paginator\Adapter\AdapterInterface::getItems()
     */
    public function getItems($offset, $itemCountPerPage)
    {
        // Resultset von ActiveRecord holen
        // offset ist das errechnete Element wo ich beginne (Seitenzahl * element je seite)
        $resultSet = forward_static_call([
            $this->activeRecord,
            'getRecords'
        ], $offset, $itemCountPerPage, $this->conditions, $this->sort);
        // muss iterator to array sein?
        
        return iterator_to_array($resultSet);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     *
     */
    public function count()
    {
        // Gesamtzahl der Elemente in DB
        return (forward_static_call([
            $this->activeRecord,
            'getRecordCount'
        ], $this->conditions));
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }
    

}

