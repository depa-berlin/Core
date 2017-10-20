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

    public function __construct(ActiveRecord $activeRecord, $conditions = '')
    {
        $this->activeRecord = $activeRecord;
        $this->conditions = $conditions;
    }

    /**
     * Returns an array of items for a page.
     *
     * @param int $offset
     *            Page offset
     * @param int $itemCountPerPage
     *            Number of items per page
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        
        // Resultset von ActiveRecord holen
        // offset ist das errechnete Element wo ich beginne (Seitenzahl * element je seite)
        $resultSet = forward_static_call([$this->activeClass,'getRecords'], $offset, $itemCountPerPage);
        //muss iterator to array sein?
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
        return (forward_static_call([$this->activeClass,'getRecordCount']));
    }
}

