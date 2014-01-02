<?php
/**
 * DatabaseObjectProfile.php
 * 11/18/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;
use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;


/**
 * Class DatabaseObjectProfile
 *
 * DatabaseObjectProfile contains information about the relationship between
 * a class and its database table.
 *
 * In practice (in DatabaseBroker) this information is used to map
 * DatabaseValueMap properties to database table column names. However, for
 * identification purposes, we use the name of the class being represented by
 * the DatabaseValueMap.
 *
 * @package EEEApply\API\V1\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseObjectProfile
{
    /**
     * The name of the class being represented
     *
     * @var string
     */
    protected $className;

    /**
     * The name of the database table
     *
     * @var string
     */
    protected $tableName;

    /**
     * An array mapping property names to their matching column names
     *
     * @var array
     */
    protected $propertyToColumnMap;

    /**
     * Sets the name of the class that this profile is bound to.
     *
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * Returns the name of the class that this profile is bound to.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Sets the name of the database table that this profile is bound to.
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Returns the name of the database table that this profile is bound to.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Sets a map of property to column names.
     *
     * @param array $propertyToColumnMap
     */
    public function setPropertyToColumnMap(array $propertyToColumnMap)
    {
        $this->propertyToColumnMap = $propertyToColumnMap;
    }

    /**
     * Returns a map of property to column names.
     *
     * @return array
     */
    public function getPropertyToColumnMap()
    {
        return $this->propertyToColumnMap;
    }

    /**
     * Returns the name of a property's corresponding column.
     *
     * @param string $property_name
     * @return string
     * @throws DatabaseBrokerException if the property is not defined
     */
    public function getColumnFromProperty($property_name)
    {
        if(!isset($this->propertyToColumnMap[$property_name])) {
            throw new DatabaseBrokerException(
                "Property $property_name not found for class {$this->className}"
            );
        }
        return $this->propertyToColumnMap[$property_name];
    }

    /**
     * Returns the name of a column's corresponding property.
     *
     * @param string $column_name
     * @return string
     * @throws DatabaseBrokerException if the column is not defined
     */
    public function getPropertyFromColumn($column_name)
    {
        $key = array_search($column_name, $this->propertyToColumnMap);
        if($key === false) {
            throw new DatabaseBrokerException(
                "Column $column_name not found for class {$this->className}"
            );
        }
        return $key;
    }
} 