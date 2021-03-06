<?php

class DiscountCondition 
{
    /**
     * @var int
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string
     */
    protected $_compareType; // e.g. gt, lt, gte, lte, ..

    /**
     * @var scalar
     */
    protected $_compareValue; // from DB, and prepared; based on source info

    /**
     * @var scalar
     */
    protected $_sourceValue; // from DB (Item/Product or Shipment)

    /**
     * @var string
     */
    protected $_sourceEntityType; //(config) Item, Shipment, Customer

    /**
     * @var string
     */
    protected $_sourceEntityField; //(config) Sku, ShippingMethod, Price, Zipcode, LastName

    /**
     * @var string, get rid of this?
     */
    protected $_sourceEntityFieldType; //(config) int, string, csv string/array, 

    /**
     * @var bool
     */
    protected $_isNot; // return opposite of result

    static $prefix = 'condition-';


    // array key values
    static $compareGreaterThan = 'gt';
    static $compareLessThan = 'lt';
    static $compareGreaterThanEquals = 'gte';
    static $compareLessThanEquals = 'lte';
    static $compareInArray = 'in_array';
    static $compareArrayIntersect = 'array_intersect';
    static $compareEquals = 'equals';
    static $compareEqualsStrict = 'equals_strict';

    /**
     *
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Get key for associative arrays
     */
    static function getKey($id)
    {
        return self::$prefix . $id;
    }

    /**
     * Reset default values
     *
     * @return DiscountCondition
     */
    public function reset()
    {
        $this->_id = 0;
        $this->_name = '';
        $this->_compareType = '';
        $this->_compareValue = '';
        $this->_sourceValue = null;
        $this->_sourceEntityType = '';
        $this->_sourceEntityFieldType = '';
        $this->_isNot = false;

        return $this;
    }

    /**
     * Encapsulate object as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Encapsulate object as json
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Encapsulate object as array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id'                       => $this->getId(),
            'name'                     => $this->getName(),
            'compare_type'             => $this->getCompareType(),
            'is_not'                   => $this->getIsNot(),
            'compare_value'            => $this->getCompareValue(),
            'source_entity_type'       => $this->getSourceEntityType(),
            'source_entity_field'      => $this->getSourceEntityField(),
            'source_entity_field_type' => $this->getSourceEntityFieldType(),
        );
    }

    /**
     * Import from json
     *
     * @param string $json
     * @param bool
     * @return DiscountCondition
     */
    public function importJson($json, $reset = true)
    {
        if ($reset) {
            $this->reset();
        }

        $data = @ (array) json_decode($json);

        $id = isset($data['id']) ? $data['id'] : '';
        $name = isset($data['name']) ? $data['name'] : '';
        $compareType = isset($data['compare_type']) ? $data['compare_type'] : '';
        $isNot = isset($data['is_not']) ? $data['is_not'] : false;
        $compareValue = isset($data['compare_value']) ? $data['compare_value'] : '';

        $sourceEntityType = isset($data['source_entity_type']) ? $data['source_entity_type'] : '';
        $sourceEntityField = isset($data['source_entity_field']) ? $data['source_entity_field'] : '';
        $sourceEntityFieldType = isset($data['source_entity_field_type']) ? $data['source_entity_field_type'] : '';

        $this->_id = $id;
        $this->_name = $name;
        $this->_compareType = $compareType;
        $this->_isNot = $isNot;
        $this->_compareValue = $compareValue;
        
        $this->_sourceEntityType = $sourceEntityType;
        $this->_sourceEntityField = $sourceEntityField;
        $this->_sourceEntityFieldType = $sourceEntityFieldType;

        return $this;
    }

    /**
     * Import from stdClass
     *
     * @return DiscountCondition
     */
    public function importStdClass($obj, $reset = true)
    {
        if ($reset) {
            $this->reset();
        }

        $id = isset($obj->id) ? $obj->id : '';
        $name = isset($obj->name) ? $obj->name : '';
        $compareType = isset($obj->compare_type) ? $obj->compare_type : '';
        $isNot = isset($obj->is_not) ? $obj->is_not : false;
        $compareValue = isset($obj->compare_value) ? $obj->compare_value : '';
        $sourceEntityType = isset($obj->source_entity_type) ? $obj->source_entity_type : '';
        $sourceEntityField = isset($obj->source_entity_field) ? $obj->source_entity_field : '';
        $sourceEntityFieldType = isset($obj->source_entity_field_type) ? $obj->source_entity_field_type : '';

        $this->_id = $id;
        $this->_name = $name;
        $this->_compareType = $compareType;
        $this->_isNot = $isNot;
        $this->_compareValue = $compareValue;
        $this->_sourceEntityType = $sourceEntityType;
        $this->_sourceEntityField = $sourceEntityField;
        $this->_sourceEntityFieldType = $sourceEntityFieldType;

        return $this;
    }

    /**
     * Import from Entity
     *
     * @param object|mixed
     * @return DiscountCondition
     */
    public function importEntity($entity)
    {
        $id = $entity->getId();
        $name = $entity->getName();
        $compareType = $entity->getCompareType();
        $isNot = $entity->getIsNot();
        $compareValue = $entity->getCompareValue();
        
        $sourceEntityType = $entity->getSourceEntityType();
        $sourceEntityField = $entity->getSourceEntityField();
        $sourceEntityFieldType = $entity->getSourceEntityFieldType();

        $this->_id = $id;
        $this->_name = $name;
        $this->_compareType = $compareType;
        $this->_isNot = $isNot;
        $this->_compareValue = $compareValue;
        $this->_sourceEntityType = $sourceEntityType;
        $this->_sourceEntityField = $sourceEntityField;
        $this->_sourceEntityFieldType = $sourceEntityFieldType;

        return $this;
    }

    /**
     * Validate this condition
     *
     * @return bool
     */
    public function isValid()
    {

        $explodedCompareValue = array();
        $explodedSourceValue = array();

        if (in_array($this->getCompareType(), array(self::$compareInArray, self::$compareArrayIntersect))) {
            $compareValueStr = $this->getCompareValue();
            $explodedCompareValue = $this->explodeCsv($compareValueStr);

            if ($this->getCompareType() == self::$compareArrayIntersect) {
                $explodedSourceValue = $this->explodeCsv($this->getSourceValue());
            }
        }

        switch($this->getCompareType()) {
            case self::$compareEquals:
                $result = ($this->getSourceValue() == $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareEquals:
                $result = ($this->getSourceValue() === $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareGreaterThan:
                $result = ((float) $this->getSourceValue() > (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareLessThan:
                $result = ((float) $this->getSourceValue() < (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareGreaterThanEquals:
                $result = ((float) $this->getSourceValue() >= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareLessThanEquals:
                $result = ((float) $this->getSourceValue() <= (float) $this->getCompareValue());
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareInArray:
                $result = in_array($this->getSourceValue, $explodedCompareValue);
                return $this->getIsNot() ? !$result : $result;
                break;
            case self::$compareArrayIntersect:
                $result = (count(array_intersect($explodedSourceValue, $explodedCompareValue)) > 0);
                return $this->getIsNot() ? !$result : $result;
                break;
            default:
                //no-op
                break;
        }

        return $this->getIsNot() ? true : false; // false by default
    }

    /**
     * Explode a csv string
     *
     * @param string
     * @param bool
     * @return array
     */
    public function explodeCsv($str, $trim = true)
    {
        $explodedCompareValue = explode(',', $compareValueStr);

        if (!count($explodedCompareValue)) {
            return array();
        }

        if ($trim) {
            foreach($explodedCompareValue as $idx => $val) {
                $explodedCompareValue[$idx] = trim($val);
            }
        }
        
        return $explodedCompareValue;
    }

    /**
     * Getter
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Setter
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Getter
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Setter
     */
    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * Getter
     */
    public function getCompareType()
    {
        return $this->_compareType;
    }

    /**
     * Setter
     */
    public function setCompareType($compareType)
    {
        // validate against array key values
        $this->_compareType = $compareType;
        return $this;
    }

    /**
     * Getter
     */
    public function getCompareValue()
    {
        return $this->_compareValue;
    }

        /**
     * Getter
     */
    public function getIsNot()
    {
        return (bool) $this->_isNot;
    }

    /**
     * Setter
     */
    public function setIsNot($isNot)
    {
        $this->_isNot = (bool) $isNot;
        return $this;
    }

    /**
     * Setter
     */
    public function setCompareValue($compareValue)
    {
        $this->_compareValue = $compareValue;
        return $this;
    }

    /**
     * Getter
     */
    public function getSourceValue()
    {
        return $this->_sourceValue;
    }

    /**
     * Setter
     */
    public function setSourceValue($sourceValue)
    {
        $this->_sourceValue = $sourceValue;
        return $this;
    }

    /**
     * Getter
     */
    public function getSourceEntityType()
    {
        return $this->_sourceEntityType;
    }

    /**
     * Setter
     */
    public function setSourceEntityType($entityType)
    {
        $this->_sourceEntityType = $entityType;
        return $this;
    }

    /**
     * Getter
     */
    public function getSourceEntityField()
    {
        return $this->_sourceEntityField;
    }

    /**
     * Setter
     */
    public function setSourceEntityField($sourceEntityField)
    {
        $this->_sourceEntityField = $sourceEntityField;
        return $this;
    }

    /**
     * Getter
     */
    public function getSourceEntityFieldType()
    {
        return $this->_sourceEntityFieldType;
    }

    /**
     * Setter
     */
    public function setSourceEntityFieldType($sourceEntityFieldType)
    {
        $this->_sourceEntityFieldType = $sourceEntityFieldType;
        return $this;
    }

}
