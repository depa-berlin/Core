<?php
namespace Depa\Core\DataModel\ActiveRecord\Api;


class Api extends \Core\Api\AbstractApi
{
    /**
     * 
     * @var \Core\Model\ActiveRecord
     */
    protected $recordClass;
    
    protected $adapter;
    
    public function __construct($recordClass, $adapter)
    {
        $this->recordClass = $recordClass;
        $recordClass::loadConfig();
        $this->adapter = $adapter;
        $recordClass::setAdapter($adapter);
        parent::__construct();
    }
    
    public function create($data, $metadata)
    {
        $recordDataArray = array($data);
        if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getConfig'])['attributes']) === [])
        {
            $recordDataArray = $data;
        }
        $result = array();
        foreach ($recordDataArray as $recordData)
        {
            $record = new $this->recordClass($this->adapter);
            $ret = $this->setData($record, (array) $recordData);
            if ($ret instanceof \ZF\ApiProblem\ApiProblem)
            {
                return $ret;
            }
            $record->save();
            $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
        }
        return $result;
    }
    
    public function read($data, $metadata)
    {
        $total = 0;
        $condition = NULL;
        //Alle records?
        $recordArray = array();
        //if (isset($metadata['filter']) && isset($metadata['filterValue']) && $this['recordClass']::hasAttribute($metadata['filter']))
        if (array_key_exists('filter', $metadata) && (array_key_exists('filterValue', $metadata)) && in_array($metadata['filter'], forward_static_call([$this->recordClass, 'getConfig'])['attributes']))
        {
            $condition = [$metadata['filter'] => $metadata['filterValue']];
            $recordArray = forward_static_call([$this->recordClass, 'findAll'], $condition);
        }
        if (array_key_exists('limit', $metadata) && (array_key_exists('page', $metadata) || array_key_exists('start', $metadata)))
        {   
            if (count($recordArray) === 0)
            {
                $page = NULL;
                $start = NULL;
                if (array_key_exists('page', $metadata))
                {
                    $page = $metadata['page'];
                }
                if (array_key_exists('start', $metadata))
                {
                    $start = $metadata['start'];
                }
                if ( (int) $metadata['limit'] < 1 || ( (int) $start < 0 && (int) $page  < 0 ) )
                {
                    return new \ZF\ApiProblem\ApiProblem(400, 'Invalid pagination parameters!');
                }
                $recordArray = forward_static_call([$this->recordClass, 'getRecordsLimitedBy'], $page, $start, $metadata['limit']);
                
                unset($metadata['page']);
                unset($metadata['start']);
                unset($metadata['limit']);
            }
            $total = forward_static_call([$this->recordClass, 'getRecordCount'], $condition);
        }
        elseif (empty($recordArray))
        {
            $recordArray = forward_static_call([$this->recordClass, 'getRecords']);
        }
        $result = array();
        foreach ($recordArray as $record)
        {
            $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
        }
        $result = (array) $this->apiPaginate($result, $metadata);
        if ($total > 0)
        {
            $result['total'] = $total;
        }
        return $result;
    }
    
    public function update($data, $metadata)
    {
       $result = array();
       $recordDataArray = array($data);
       if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getPrimaryKeys'])) === [])
       {
           $recordDataArray = $data;
       }
       foreach ($recordDataArray as $recordData)
       {
           $recordData = (array) $recordData;
           $record = $this->createRecordFromData($recordData);
           if ($record instanceof \ZF\ApiProblem\ApiProblem)
           {
               return $record;
           }
           $ret = $this->setData($record, (array) $recordData);
           if ($ret instanceof \ZF\ApiProblem\ApiProblem)
           {
               return $ret;
           }
           $record->save();
           $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
       }
       return $result;
    }
    
    public function destroy($data, $metadata)
    {
        $result = array();
        $recordDataArray = array($data);
        if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getPrimaryKeys'])) === [])
        {
            $recordDataArray = $data;
        }
        foreach ($recordDataArray as $recordData)
        {
            $recordData = (array) $recordData;
            $record = $this->createRecordFromData($recordData);
            if ($record === false)
            {
                return new \ZF\ApiProblem\ApiProblem(404, 'Record with Primary Key(s) not found!');
            }
            if ($record instanceof \ZF\ApiProblem\ApiProblem)
            {
                return $record;
            }
            $record->delete();
            if (array_keys(array_diff($recordData, $this->getData($record))) === $record::getPrimaryKeys())
            {
                $result[] = $data;
            }
            else
            {
                $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
            }
            
        }
        return $result;
    }
    
    protected function setData(\Core\Model\ActiveRecord $record, $data)
    {
        foreach ($data as $attribute => $value)
        {
            if (! $record->hasAttribute($attribute))
            {
                return new \ZF\ApiProblem\ApiProblem(400, 'Unable to set data: invalid Attribute: '.$attribute);
            }
            $record->{$attribute} = $value;
        }
    }
    
    protected function getData(\Core\Model\ActiveRecord $record)
    {
        $result = array();
        foreach ($record->attributes as $attribute)
        {
            $result[$attribute] = $record->$attribute;
        }
        return $result;
    }
    
    protected function createRecordFromData($data)
    {
        $primaryKeyData = array();
        foreach (forward_static_call([$this->recordClass, 'getPrimaryKeys']) as $primaryKey)
        {
            if (!array_key_exists($primaryKey, $data))
            {
                return new \ZF\ApiProblem\ApiProblem(400, 'Unable to create record: missing primary key data!');
            }
            $primaryKeyData[$primaryKey] = $data[$primaryKey];
        } 
        $record = forward_static_call([$this->recordClass, 'find'], $primaryKeyData);
        return $record;
    }
}