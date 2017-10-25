<?php
namespace Depa\Core\DataModel\ActiveRecord\Adapter;

use Zend\Paginator\Adapter\AdapterInterface;
use Depa\Core\DataModel\ActiveRecord\ActiveRecord;

/**
 *
 * @author fenrich
 *        
 */
class ActiveRecordAdapter implements AdapterInterface
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
    
    /**
     * 
     * @param ActiveRecord $activeRecord
     * @param array||NULL $conditions
     */
    public function __construct(ActiveRecord $activeRecord, $conditions = NULL)
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
        ], $offset, $itemCountPerPage, $this->conditions);
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
}

