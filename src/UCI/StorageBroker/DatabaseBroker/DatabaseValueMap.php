<?php
/**
 * DatabaseValueMap.php
 * 11/18/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;
use UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;

/**
 * Class DatabaseValueMap
 *
 * DatabaseValueMap stores a mapping between four elements:
 *   - property name
 *   - column name
 *   - placeholder name
 *   - value
 *
 * It is able to return one-to-one mappings between several pairings elements
 * as arrays.
 *
 * @package UCI\StorageBroker\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseValueMap
{
    /**
     * A string unique to this instance for the creation of unique placeholder
     * names.
     *
     * The type is a string for flexibility, but something as simple as an
     * integer incremented for each instance can do the job.
     *
     * @var string
     */
    protected $uniquePlaceholderString;

    /**
     * Creates new DatabaseValueMap instances. This is needed in the merge()
     * method, which returns a new instance.
     *
     * @var DatabaseValueMapFactory
     */
    protected $dbValueMapFactory;

    /**
     * Needed to map property names to column names.
     * The profile cannot be changed after construction, since the effect on
     * existing mappings would be undefined.
     *
     * @var DatabaseObjectProfile
     */
    protected $dbObjectProfile;

    /**
     * A two-dimensional array storing a map of values for each property.
     * This map takes the form of:
     *  $map =  [
     *              ['property'      => property_name,
     *               'column'        => column_name,
     *               'placeholder'   => placeholder_name,
     *               'value'         => mysql-ready_value],
     *              ...
     *          ]
     *
     * The map initializes to an empty array.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Indicates whether this DatabaseValueMap was the result of a merge
     * of two or more maps.
     *
     * This is significant because merged maps can't return certain information.
     * (See method comments below.)
     *
     * @var bool
     */
    protected $merged = false;

    /**
     * Accepts class dependencies.
     *
     * @param DatabaseValueMapFactory $db_value_map_factory
     * @param DatabaseObjectProfile $db_object_profile
     * @param string $unique_string
     */
    public function __construct(
        DatabaseValueMapFactory $db_value_map_factory,
        DatabaseObjectProfile $db_object_profile,
        $unique_string
    ) {
        $this->dbValueMapFactory = $db_value_map_factory;
        $this->dbObjectProfile = $db_object_profile;
        $this->uniquePlaceholderString = $unique_string;
    }

    /**
     * Returns the DatabaseObjectProfile that this map is bound to.
     *
     * @return DatabaseObjectProfile
     */
    public function getDatabaseObjectProfile()
    {
        return $this->dbObjectProfile;
    }

    /**
     * Returns the name of the table that this map is bound to.
     *
     * This is a proxy for DatabaseObjectProfile::getTableName
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->dbObjectProfile->getTableName();
    }

    /**
     * Returns the name of the class that this map is bound to.
     *
     * This is a proxy for DatabaseObjectProfile::getTableName
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->dbObjectProfile->getClassName();
    }

    /**
     * Adds a property and its value to the map.
     *
     * @param string $property_name
     * @param mixed $value
     * @return void
     * @throws DatabaseBrokerException if the property name is not defined
     *                                      in the associated DatabaseObjectProfile
     */
    public function addProperty($property_name, $value)
    {
        $column_name = $this->dbObjectProfile->getColumnFromProperty($property_name);
        $this->addToMap($property_name, $column_name, $value);
    }

    /**
     * Adds an array of properties and their values to the map.
     *
     * The expected format for $property_to_value_map is ['property_name' => value, ...]
     *
     * @param array $property_to_value_map
     * @return void
     * @throws DatabaseBrokerException if any of the property name keys is
     *                                      not defined in the associated
     *                                      DatabaseObjectProfile
     */
    public function addProperties(array $property_to_value_map)
    {
        foreach($property_to_value_map as $property => $value) {
            $this->addProperty($property, $value);
        }
    }

    /**
     * Searches the map to see if a value has been set for the provided property.
     *
     * @param string $property_name
     * @return bool
     */
    public function hasProperty($property_name)
    {
        // If an index search returns false, the property has not been set
        return $this->getIndex('property', $property_name) !== false;
    }

    /**
     * @param string $property_name
     * @throws DatabaseBrokerException if a property by the provided name
     *                                      has not been set
     */
    public function removeProperty($property_name)
    {
        if(!$this->hasProperty($property_name)) {
            throw new DatabaseBrokerException(
                "Cannot remove property $property_name. Property not set."
            );
        }

        $index = $this->getIndex('property', $property_name);
        array_splice($this->map, $index, 1);
    }

    /**
     * Adds a column and its value to the map.
     *
     * @param string $column_name
     * @param mixed $value
     * @return void
     * @throws DatabaseBrokerException if the column name is not defined
     *                                      in the associated DatabaseObjectProfile
     */
    public function addColumn($column_name, $value)
    {
        $property_name = $this->dbObjectProfile->getPropertyFromColumn($column_name);
        $this->addToMap($property_name, $column_name, $value);
    }

    /**
     * Adds an array of columns and their values to the map.
     *
     * The expected format for $column_to_value_map is ['column_name' => value, ...]
     *
     * @param array $column_to_value_map
     * @return void
     * @throws DatabaseBrokerException if any of the column name keys is
     *                                      not defined in the associated
     *                                      DatabaseObjectProfile
     */
    public function addColumns(array $column_to_value_map)
    {
        foreach($column_to_value_map as $column => $value) {
            $this->addColumn($column, $value);
        }
    }

    /**
     * Returns true if the provided DatabaseValueMap and this one are based
     * on the same DatabaseObjectProfile.
     *
     * If two DatabaseValueMaps are compatible, they can be merged.
     *
     * @param DatabaseValueMap $dvm
     * @return bool
     */
    public function isCompatible(DatabaseValueMap $dvm)
    {
        return $this->dbObjectProfile == $dvm->dbObjectProfile;
    }

    /**
     * Returns a new DatabaseValueMap who's internal map represents the union
     * of this instance's map and the provided instance's. Both DatabaseValueMaps
     * must be based on the same DatabaseValueMap.
     *
     * The unique placeholder names of the original maps are preserved.
     * This is handy because it means that separate maps can create different
     * parts of the same SQL statement and then be merged to produce the values
     * required to run the full query.
     *
     * Any properties added to the new resulting map will have placeholders
     * unique to the new map.
     *
     * @param DatabaseValueMap $dvm
     * @return DatabaseValueMap
     * @throws DatabaseBrokerException if the provided DatabaseValueMap is not
     *                                      built on the same DatabaseObjectProfile
     *                                      as this instance
     */
    public function merge(DatabaseValueMap $dvm)
    {
        // Make sure that the provided DatabaseValueMap can be merged with
        // this one
        if(!$this->isCompatible($dvm)) {
            throw new DatabaseBrokerException(
                "DatabaseValueMaps must be based on the same class " .
                "DatabaseObjectProfile to be merged."
            );
        }

        // combine this instance's map with the other instance's to get the union
        $merged_map = array_merge($this->map, $dvm->map);
        // request a new DatabaseValueMap built around the same class
        $new_dvm = $this->dbValueMapFactory->build($this->getClassName());
        // stick the new merged map into our new object
        $new_dvm->map = $merged_map;
        $new_dvm->merged = true;

        return $new_dvm;
    }

    /**
     * Returns an associative array that maps property names to their
     * corresponding column names.
     *
     * This map will only include properties that have had values explicitly
     * set, even if those values are null. ie. It does not include properties
     * that belong to the database table or object, but that haven't had
     * setProperty called on this instance.
     *
     * @return array
     */
    public function getPropertyToColumnMap()
    {
        return $this->buildSubMap('property', 'column');
    }

    /**
     * Returns an associative array that maps properties to their values.
     *
     * This method is NOT safe to use after a merge, since duplicate property
     * entries will overwrite each other.
     *
     * @return array
     * @throws DatabaseBrokerException if this map resulted from a merge
     *                                      operation
     */
    public function getPropertyToValueMap()
    {
        if($this->merged) {
            throw new DatabaseBrokerException(
                "Cannot call getColumnToValueMap() on a merged DatabaseValueMap"
            );
        }

        return $this->buildSubMap('property', 'value');
    }

    /**
     * Returns an associative array that maps column names to their values.
     *
     * This method is NOT safe to use after a merge, since duplicate column
     * entries will overwrite each other.
     *
     * @return array
     * @throws DatabaseBrokerException if this map resulted from a merge
     *                                      operation
     */
    public function getColumnToValueMap()
    {
        if($this->merged) {
            throw new DatabaseBrokerException(
                "Cannot call getColumnToValueMap() on a merged DatabaseValueMap"
            );
        }

        return $this->buildSubMap('column', 'value');
    }

    /**
     * Returns an associative array that maps column names to their value
     * placeholders.
     *
     * This method is NOT safe to use after a merge, since duplicate column
     * entries will overwrite each other.
     *
     * @return array
     * @throws DatabaseBrokerException if this map resulted from a merge
     *                                      operation
     */
    public function getColumnToPlaceholderMap()
    {
        if($this->merged) {
            throw new DatabaseBrokerException(
                "Cannot call getColumnToPlaceholderMap() on a merged DatabaseValueMap"
            );
        }

        return $this->buildSubMap('placeholder', 'column');
    }

    /**
     * Returns an associative array that maps placeholders to column names.
     *
     * @return array
     */
    public function getPlaceholderToColumnMap()
    {
        return $this->buildSubMap('placeholder', 'column');
    }

    /**
     * Returns an associative array that maps placeholders to their values.
     *
     * @return array
     */
    public function getPlaceholderToValueMap()
    {
        return $this->buildSubMap('placeholder', 'value');
    }

    /**
     * Returns the unique placeholder prefix. This prefix should be
     * prepended to all placeholders.
     *
     * @return string
     */
    protected function getPlaceholderPrefix()
    {
        return ":val{$this->uniquePlaceholderString}_";
    }

    /**
     * Returns the index of a given element in the map. Since the map is
     * two-dimensional, you must specify which key of the property array
     * you would like to match on.
     *
     * If a match is not found, returns false.
     *
     * In the case that there are multiple matches (which can occur in
     * merged DatabaseValueMaps), the first match's index is returned.
     *
     * @param string $key the key of the property array on which to match
     * @param mixed $value the value to match against
     * @return bool|int
     */
    protected function getIndex($key, $value)
    {
        for($i = 0; $i < count($this->map); $i++) {
            if($this->map[$i][$key] == $value) {
                return $i;
            }
        }
        return false;
    }

    /**
     * Accepts two keys (A and B) that exist in the master map and returns an
     * associative array in which the value A is the key and B is the value.
     * This can be used to generate sub-maps that represent the relationship
     * between only two of the entries in each row of the master map.
     *
     * For example:
     *   $map = [
     *              ['A' => 'A1', 'B' => 'B1', 'C' => 'C1', 'D' => 'D1'],
     *              ['A' => 'A2', 'B' => 'B2', 'C' => 'C2', 'D' => 'D2'],
     *              ['A' => 'A3', 'B' => 'B3', 'C' => 'C3', 'D' => 'D3']
     *          ]
     *   buildSubMap('C', 'A') returns [ 'C1' => 'A1', 'C2' => 'A2', 'C3' => 'A3' ]
     *
     * @param $key_a
     * @param $key_b
     * @return array
     */
    protected function buildSubMap($key_a, $key_b)
    {
        $a = [];
        foreach($this->map as $row) {
            $key = $row[$key_a];
            $value = $row[$key_b];
            $a[$key] = $value;
        }
        return $a;
    }

    /**
     * Adds a (property, column, value) grouping to the internal map.
     *
     * @param string $property_name
     * @param string $column_name
     * @param mixed $value
     */
    private function addToMap($property_name, $column_name, $value)
    {
        $placeholder_name = $this->getPlaceholderPrefix() . $column_name;

        $this->map[] = [
            'property'      => $property_name,
            'column'        => $column_name,
            'placeholder'   => $placeholder_name,
            'value'         => $value
        ];
    }
} 