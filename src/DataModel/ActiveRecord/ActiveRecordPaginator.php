<?php
namespace Depa\Core\DataModel\ActiveRecord;

use Depa\Core\DataModel\ActiveRecord\ActiveRecord;
use Depa\Core\DataModel\ActiveRecord\Adapter\ActiveRecordAdapter;
use Depa\Core\Api\Hal;
use Depa\Core\Interfaces\Halable;
use Zend\Paginator\Paginator;
use Zend\Diactoros\Uri;

/**
 *
 * @author fenrich
 *        
 */
class ActiveRecordPaginator extends Paginator implements Halable
{

    protected static $defaultItemCountPerPage = 1;

    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter            
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ActiveRecord $activeRecord)
    {
        $adapter = new ActiveRecordAdapter($activeRecord);
        parent::__construct($adapter);
    }

    public function toHal(Uri $requestUri)
    {
        $apiHal = new Hal();
        $apiHal->addElement('count', $this->getTotalItemCount());
        $this->_makeLink($apiHal, $requestUri);
        
        foreach ($this->getCurrentItems() as $record) {
            
            // Query aus Uri entfernen
            $requestUri = $requestUri->withQuery("");
            // Path mit ID ergänzen
            $path = $requestUri->getPath();
            $requestUri = $requestUri->withPath($path . "/" . $record->id);
            
            $apiHal->addEmbed((new \ReflectionClass($record))->getShortName(), $record->toHal($requestUri));
        }
        
        return $apiHal->getHal();
    }

    protected function _makeLink($apiHal, Uri $requestUri)
    {
        $queryArray = explode('&', $requestUri->getQuery());
        
        // page Information aus QueryString entfernen
        $newQueryArray = array();
        foreach ($queryArray as $key => $value) {
            if (stripos($value, 'page') === FALSE) {
                $newQueryArray[] = $value;
            }
        }
        
        $currentPageNumber = $this->getCurrentPageNumber();
        
        $pageCount = $this->count();
        $apiHal->addLink('self', $this->_makeUri($requestUri, $newQueryArray, 'page=' . $currentPageNumber));
        $apiHal->addLink('first', $this->_makeUri($requestUri, $newQueryArray, 'page=1'));
        $apiHal->addLink('last', $this->_makeUri($requestUri, $newQueryArray, '?page=' . $pageCount));
        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $apiHal->addLink('prev', $this->_makeUri($requestUri, $newQueryArray, '?page=' . ($currentPageNumber - 1)));
        }
        
        if ($currentPageNumber + 1 <= $pageCount) {
            $apiHal->addLink('next', $this->_makeUri($requestUri, $newQueryArray, '?page=' . ($currentPageNumber + 1)));
        }
    }

    protected function _makeUri(Uri $uri, $queryArray, $newQuery)
    {
        $queryArray[] = $newQuery;
        return $uri->withQuery(implode("&", $queryArray));
    }
}

