<?php
namespace Depa\Core\DataModel\ActiveRecord\Paginator;

use Depa\Core\DataModel\ActiveRecord\ActiveRecord;
use Depa\Core\DataModel\ActiveRecord\Paginator\Adapter;
use Zend\Paginator\Paginator;

/**
 *
 * @author fenrich
 *        
 */
class ActiveRecordPaginator extends Paginator
{

    protected static $defaultItemCountPerPage = 10;
    

    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter            
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ActiveRecord $activeRecord, $conditions = NULL, $sort = NULL)
    {
        $adapter = new Adapter\ActiveRecord($activeRecord, $conditions, $sort);
        parent::__construct($adapter);
    }


    /**
     * Setzt den "default item count per page" nur neu, wenn dieser > 0.
     *
     * @param int $count            
     */
    public static function setDefaultItemCountPerPage($count)
    {
        if ((int) $count > 0) {
            static::$defaultItemCountPerPage = (int) $count;
        }
    }
   /**
    * 
    * @param array $sort
    */
    public function setItemSort($sort)
    {
        if (is_array($sort)&&count($sort)>0)
        {
        $this->getAdapter()->setSort($sort);
        }
    }
}

