<?php
namespace Depa\Core\DataModel\ActiveRecord\Traits;

use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\AdapterInterface;


/**
 * Trait kümmert sich um Queries die ActiveRecord betreffen.
 * Zusätzlich werden einige nötige Parameter für das Arbeiten mit DBs definiert – zB. Table-Namen, Primary Keys.
 *
 * @author alex
 *        
 */
trait DatabasePersistenceTrait 
{

    /**
     * TableGateway ist eine ZF-Klasse, erlaubt einfachen Zugriff auf Datenbanktabelle.
     *
     * @var Zend\Db\TableGateway\TableGateway
     */
    protected $tableGateway;

    /**
     * Name der Tabelle in dem das Objekt gespeichert ist
     *
     * @var string
     */
    protected $tablename;

    /**
     * Primary-Keys die zum Finden in der Table benutzt werden können.
     *
     * @var Array
     */
    protected $primaryKeys;

    /**
     * Gibt den TableGateway für die Klasse, in der der Trait benutzt wird zurück und initialisiert den TableGateway gegenbenfalls.
     *
     * @return \Zend\Db\TableGateway\TableGateway
     */
    protected $isConfigLoaded = false;

    /**
     * Instanz der Validatorklasse
     *
     * @var Core\Model\ActiveRecord\Validator
     */
    protected static $_validator;

    protected static $_configuration = array();

    public static function getTable()
    {
        $class = get_called_class();
        $adapter = static::getAdapter($class);
        if (! static::isConfigLoaded()) {
            static::loadConfig();
        }
        if (! isset(static::$_configuration[$class]['table']) || static::$_configuration[$class]['table']->getTable() != static::$_configuration[$class]['tablename'] || $adapter !== static::$_configuration[$class]['table']->getSql()->getAdapter()) {
            static::$_configuration[$class]['table'] = new TableGateway(static::$_configuration[$class]['tablename'], $adapter, new RowGatewayFeature(new $class($adapter)));
        }
        return static::$_configuration[$class]['table'];
    }

    /**
     * Gibt den DB-Adapter zurück
     *
     * @return Ambigous <\Zend\Db\Adapter\AdapterInterface, AdapterInterface>
     */
    public static function getAdapter()
    {
        $adapter = static::$_configuration[get_called_class()]['adapter'];
        if (! $adapter instanceof AdapterInterface) {
            throw new \Exception('No database adapter!');
        }
        return $adapter;
    }

    /**
     * Setzt den DB-Adapter
     *
     * @param AdapterInterface $adapter            
     * @return AdapterInterface
     */
    public static function setAdapter(AdapterInterface $adapter)
    {
        static::$_configuration[get_called_class()]['adapter'] = $adapter;
        return static::$_configuration[get_called_class()]['adapter'];
    }

    /**
     * Gibt einen oder mehrere Records anhand von Bedingungen in Form eines assoziativen Arrays zurück.
     * Z.B. ('foo' => 'bar', 'qux' => 1)
     *
     * @param array|null $condition            
     * @param bool $single            
     * @return Ambigous <\Zend\Db\ResultSet\ResultSet, NULL, \Zend\Db\ResultSet\ResultSetInterface>
     */
    protected static function findByCondition($condition = NULL, $single = false)
    {
        $rowset = static::getTable()->select(function (Select $select) use ($condition, $single) {
            if (! is_null($condition)) {
                $select->where($condition);
            }
            if ($single == true) {
                $select->limit(1);
            }
        });
        
        return $rowset;
    }

    /**
     * Gibt einen einzelnen Record zurück
     * Der Record wird anhand von Bedingungen in Form eines assoziativen Arrays oder einem Primary-Key ermittelt.
     * Wird keine Bedingung angegeben, wird ein beliebiger Record zurückgegeben
     *
     * @param unknown $condition
     * @return \Core\Model\ActiveRecord\Ambigous
     */ 
    public static function find($condition = NULL)
    {
        if (! is_null($condition) && ! is_array($condition) && count(self::getPrimaryKeys()) === 1) {
            $condition = array(
                self::getPrimaryKeys()[0] => $condition
            );
        }
        $resultset = self::findByCondition($condition, true);
        return $resultset->current();
    }

    /**
     * Gibt einen ResultSet bestehend aus einem oder mehreren ActiveRecords anhand von Bedingungen zurück.
     * Wird keine Bedingung angegeben, werden alle Records zurückgegeben
     *
     * @param unknown $condition            
     * @return \Zend\Db\ResultSet\ResultSet \
     */
    public static function findAll($condition = NULL)
    {
        return iterator_to_array(self::findByCondition($condition, false));
    }

    /**
     *
     * @param unknown $page            
     * @param unknown $start            
     * @param unknown $limit            
     * @throws \Exception
     * @return unknown * @deprecated Deprecated use getRecords($offset = NULL, $limit = NULL)
     */
    public static function getRecordsLimitedBy($page = NULL, $start = NULL, $limit)
    {
        if ($page === NULL && $start === NULL) {
            throw new \Exception('Either Start or Page parameter must be set for pagination!');
        }
        if ($page !== NULL) {
            $start = $page * $limit;
        }
        $rowset = static::getTable()->select(function (Select $select) use ($start, $limit) {
            $select->limit($limit);
            $select->offset($start);
        });
        return $rowset;
    }

    /**
     *
     * @param unknown $offset            
     * @param unknown $limit            
     * @return unknown
     */
    public static function getRecords($offset = NULL, $limit = NULL, $condition = NULL, $sort = NULL)
    {
        // $condition=['customer_id'=>1];
        if (! is_null($offset) || ! is_null($limit)) {
            $rowset = static::getTable()->select(function (Select $select) use ($offset, $limit, $condition, $sort) {
                if (! is_null($offset)) {
                    $select->offset($offset);
                }
                if (! is_null($limit)) {
                    $select->limit($limit);
                }
                if (! is_null($condition)) {
                    $select->where($condition);
                }
                if (! is_null($sort)) {
                    $select->order($sort);
                }
            });
        } else {
            $rowset = static::getTable()->select(function (Select $select) use ($condition) {
                if (! is_null($condition)) {
                    $select->where($condition);
                }
            });
        }
        return $rowset;
    }

    /**
     *
     * @param unknown $condition            
     * @return mixed
     */
    public static function getRecordCount($condition = NULL)
    {
        if (! static::isConfigLoaded()) {
            static::loadConfig();
        }
        $select = new Select();
        $select->from(static::$_configuration[get_called_class()]['tablename'])->columns([
            'recordcount' => new \Zend\Db\Sql\Expression('COUNT(*)')
        ]);
        if ($condition !== NULL) {
            $select->where($condition);
        }
        $adapter = static::getAdapter(get_called_class());
        $sql = new \Zend\Db\Sql\Sql($adapter);
        $selectString = $sql->buildSqlString($select);
        $result = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
        return $result->current()['recordcount'];
    }

    /**
     * Gibt ein Array mit allen Primary-Keys der derzeitigen Klasse zurück.
     *
     * @return Array:
     */
    public static function getPrimaryKeys()
    {
        return static::$_configuration[get_called_class()]['primaryKeys'];
    }

    /**
     * Überprüft ob ein Primary-Key mit dem übergebenen Namen existiert.
     *
     * @param string $attributeName            
     * @return boolean
     */
    public static function isPrimaryKey($attributeName)
    {
        if (in_array((string) $attributeName, static::getPrimaryKeys())) {
            return true;
        }
        return false;
    }

    public static function loadConfig()
    {}

    protected static function setConfig($configArray)
    {
        $class = get_called_class();
        static::$_configuration[$class] = $configArray;
    }

    public static function getConfig()
    {
        $class = get_called_class();
        return static::$_configuration[$class];
    }

    public static function isConfigLoaded()
    {
        $class = get_called_class();
        if (array_key_exists($class, static::$_configuration) && array_key_exists('attributes', static::$_configuration[$class])) {
            return TRUE;
        }
        return FALSE;
    }
}

?>