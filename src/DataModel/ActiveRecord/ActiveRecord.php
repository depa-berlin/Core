<?php
namespace Depa\Core\DataModel\ActiveRecord;

use Zend\Db\RowGateway\AbstractRowGateway;
use Zend\Db\Sql\Sql;
use Depa\Core\DataModel\ActiveRecord\DatabasePersistenceTrait;

/**
 * Stellt einen ActiveRecord dar, erweitert die Funktionalität des RowGateways.
 * Jede Klasse die von ActiveRecord erbt muss ihre Attribute über public nicht-statische Properties bestimmen.
 * Diese Attribute werden über Regeln validiert.
 *
 * Nach:
 * http://books.google.de/books?id=FyWZt5DdvFkC&lpg=PA1&pg=PT187&redir_esc=y#v=onepage&q=active%20record&f=false
 * (Patterns of Enterprise Application Architecture, Martin Fowler, P.160 - 163)
 *
 * @author alex
 *
 */
class ActiveRecord extends AbstractRowGateway
{
    /**
     * Array mit Attributen der Klasse.
     * @var Array
     */
    public $attributes;

    /**
     * Array mit veränderten Attributen die beim Eintragen in die DB überprüft werden müssen
     *
     * @var Array
     */
    protected $dirtyAttributes = [];

    /**
     * Array mit Validierungsregeln für Attribute
     *
     * @var Array
     */
    protected $rules;

    protected $relations = [];

    /**
     * Trait fügt statische Properties/Methoden hinzu, die benötigt werden um Records aus der DB zu lesen,
     * und Zugriff auf DBMS zu ermöglichen - zB Primary Keys.
     */
    use DatabasePersistenceTrait;

    /**
     * Konstruktor, erwartet einen DB-Adapter.
     * Beim initialisieren eines Records werden keine Daten aus der DB geladen–
     * um einen Record anhand eines Primary Keys aus der Datenbank zu holen sollte die statische Methode find() benutzt werden:
     *
     * $record = ActiveRecord::find($primaryKeyValue)
     *
     * @param string $adapter
     */

    public function __construct($adapter)
    {
        $configArray = static::loadConfig($this);
        if ($configArray !== null) {
            $this->attributes = $configArray['attributes'];
            $this->rules = $configArray['rules'];
            $this->tablename = $configArray['tablename'];
            $this->primaryKeys = $configArray['primaryKeys'];
            $this->relations = $configArray['relations'];
        }
        static::setAdapter($adapter);
        $this->primaryKeyColumn = $this->getPrimaryKeys();
        $this->table = $this->tablename;
        $this->sql = new Sql($adapter, $this->table);
        $this->initialize();
    }
    /**
     * Fügt Attributsüberprüfung zu RowGateway hinzu.
     *
     * @see \Zend\Db\RowGateway\AbstractRowGateway::__get()
     */
    public function __get($name)
    {
        if ($this->hasAttribute($name) && isset($this[$name])) {
            return parent::__get($name);
        }
        if ($this->hasAttribute($name)) {
            return null;
        }
        throw new Exception('Undefined Attribute! Trying to get '.get_called_class().' - '.$name);
    }
    /**
     * Fügt Attributsüberprüfung zu RowGateway hinzu.
     *
     *
     * @see \Zend\Db\RowGateway\AbstractRowGateway::__set()
     */
    public function __set($name, $value)
    {
        if (!$this->hasAttribute($name)) {
            throw new Exception('Undefined Attribute! Trying to set '.$name);
        }
        parent::__set($name, $value);
        if (!in_array($name, $this->dirtyAttributes)) {
            $this->dirtyAttributes[$name] = [];
        }
        return;
    }
    /**
     * Liefert eine Aussage darüber, ob der aktuelle ActivRecord in der Datenbank vorhanden ist
     *
     * @return boolean
     */
    public function existsInDatabase()
    {
        return ($this->rowExistsInDatabase());
    }

    public function getDirtyAttributes()
    {
        return $this->dirtyAttributes;
    }


    public function save()
    {
        foreach ($this->rules as $rule) {
            if (in_array($rule['attribute'], $this->primaryKeys)) {
                continue;
            }
            $attribute = $rule['attribute'];
            $value = null;
            if (isset($this[$attribute])) {
                $value = $this[$attribute];
            }
            if ($rule['type'] === 'required') {
                $rules = $this->getRulesForAttribute($attribute);
                if (count($rules) > 0 && $this->validateAttribute($rules, $value) == false) {
                    $this->dirtyAttributes[$attribute] =  ["rule" => $rule['type']];
                    continue;
                }
            }
            if (isset($value) && $this->validateAttribute($rule, $value) != true) {
                  $this->dirtyAttributes[$attribute] =  ["rule" => $rule['type'], "value" => $value];
                //Log::notice("save() fehlgeschlagen, Datensatz ungültig: {$attribute} - {$this->{$attribute}}");
                continue;
            } else {
                unset($this->dirtyAttributes[$attribute]);
            }
        }
        if ($this->dirtyAttributes) {
            return false;
        }
        return parent::save();
    }

    /**
     * Überprüft ob die Klasse ein bestimmtes Attribut besitzt.
     *
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        if (in_array($name, $this->attributes, true)) {
            return true;
        }
        return false;
    }

    /**
     * Überprüft ob ein Attribut einer bestimmten Regel entspricht
     *
     * @param string $type
     * @param string $value
     * @return boolean
     */
    public function validateAttribute($rules, $value)
    {
        if (count($rules) < 1) {
            throw new Exception('Missing rule/s to validate attribute!');
        }

        if (array_key_exists('type', $rules)) {
            $rules = [$rules];
        }
        if (! isset(self::$_validator)) {
            self::$_validator = new Validator();
        }
        $valid = true;

        foreach ($rules as $rule) {
            $options = [];
            if (isset($rule['options'])) {
                $options = $rule['options'];
            }
            if (! self::$_validator->isValid($rule['type'], $options, $value)) {
                $valid = false;
            }
        }
        return $valid;
    }

    /**
     * Fügt Attributsüberprüfung zu RowGateway hinzu.
     *
     * @param array $rowset
     * @param bool $rowExistsInDatabase
     * @see \Zend\Db\RowGateway\AbstractRowGateway::populate()
     */
    public function populate(array $rowData, $rowExistsInDatabase = false)
    {
        $data = [];
        foreach ($rowData as $col => $val) {
            if ($this->hasAttribute($col)) {
                $data[$col] = $val;
            } else {
               // Log::notice('Attribute in row-set doesn\'t match known attributes: '.$col);
            }
        }
        parent::populate($data, $rowExistsInDatabase);
        $relations = $this->relations;
        $this->relations = [];
        foreach ($relations as $relationName => $relation) {
            if (!is_a ($relation, 'Depa\Core\DataModel\ActiveRecord\ActiveRelation')) {
                $this->relations[$relationName] = new ActiveRelation($this, $relation['model'], $relation['link'], $relation['relatedLink']);
            }
        }
    }

    /**
     * Gibt die Regeln zu einem bestimmten Attribut zurück
     *
     * @param unknown $attribute
     * @return multitype:unknown
     */
    public function getRulesForAttribute($attribute)
    {
        $result = [];
        foreach ($this->rules as $rule) {
            if ($this->hasAttribute($attribute) && $rule['attribute'] == $attribute) {
                $result[] = $rule;
            }
        }
        return $result;
    }

    /**
     * Lädt die config von ActiveRecord und dem DataPersistenceTrait
     *
     */
    public static function loadConfig()
    {
        if (static::isConfigLoaded() == true) {
            $configArray = static::getConfig();
            return $configArray;
        }
        $reflectionClass = new \ReflectionClass(get_called_class());
        $xmlFilePath = str_replace('.php', '.xml', $reflectionClass->getFileName());
        if (!file_exists($xmlFilePath)) {
            $config = $reflectionClass->getDefaultProperties();
            $configArray = [
                'attributes' => $config['attributes'],
                'rules' => $config['rules'],
                'relations' => $config['relations'],
                'tablename' => $config['tablename'],
                'primaryKeys' => $config['primaryKeys']
            ];
            static::setConfig($configArray);
            return null;
        }
        $xml = new \SimpleXMLElement($xmlFilePath, null, true);
        $primaryKeys = [];
        foreach ($xml->primaryKeys->primaryKey as $key) {
            $primaryKeys[] = (string) $key;
        }
        $attributes = [];
        foreach ($xml->attributes->attribute as $attribute) {
            $attributes[] = (string) $attribute;
        }
        $rules = [];
        foreach ($xml->rules->rule as $rule) {
            if (in_array($rule->attribute, $primaryKeys)) {
                throw new Exception('Invalid ActiveRecord-Configuration: Rule for Primary Key defined!');
            }
            $ruleArray = (array) $rule;
            if (isset($rule->options)) {
                $ruleArray['options'] = (array) $rule->options;
            }
            $rules[] = $ruleArray;
        }
        $relations = [];
        if (isset($xml->relations)) {
            foreach ($xml->relations->relation as $relationName => $relation) {
                $relationArray = (array) $relation;
                $relationArray['name'] = $relationName;
                $relations[] = $relationArray;
            }
        }
        $tablename = (string) $xml->tablename;
        $configArray = [
            'attributes' => $attributes,
            'rules' => $rules,
            'relations' => $relations,
            'tablename' => $tablename,
            'primaryKeys' => $primaryKeys
        ];
        static::setConfig($configArray);
        return $configArray;
    }

    public function getRelated($relationName)
    {
        if (!array_key_exists($relationName, $this->relations)) {
            return null;
        }
        $relation = $this->relations[$relationName];
        return $relation->getRelated();
    }

    public function findRelated($relationName, $condition)
    {
        if (!array_key_exists($this->relations, $relationName)) {
            return null;
        }
        $relation = $this->relations[$relationName];
        return $relation->findAllRelated($condition);
    }
}
