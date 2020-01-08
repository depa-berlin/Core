<?php
namespace Depa\Core\DataModel\ActiveRecord;

/**
 * Einfache Klasse um Attribute eines ActiveRecords zu validieren.
 * 
 * @author alex
 *
 */
class Validator
{
    /**
     * Array im Format $attributeType => $validatorClass
     * 
     * @var array
     */
    protected static $_validators = array(
        'integer' => array('validator' => 'Laminas\Validator\Between', 'options' => []),
        'string' => array('validator' => 'Laminas\Validator\StringLength', 'options' => []),
        'required' => array('validator' => 'Laminas\Validator\NotEmpty', 'options' => [\Laminas\Validator\NotEmpty::NULL]),
        'email' => array('validator' => 'Laminas\Validator\EmailAddress', 'options' => [])
    );
    
    /**
     * Validiert ein Attribut
     * 
     * @param string $type
     * @param mixed $value
     * @return boolean
     */
    public function isValid($rule, $options = [], $value)
    {
        if (!isset(self::$_validators[$rule]))
        {
            //Log::notice('No validator for type :'.$type);
            return false;
        }
        $validator = new self::$_validators[$rule]['validator'](array_merge(self::$_validators[$rule]['options'], $options));
        if ($validator->isValid($value))
        {
            return true;
        }
        return false;
    }
}