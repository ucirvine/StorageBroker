<?php
/**
 * Equals.php
 * 11/18/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;

/**
 * Class Equals
 *
 * Represents a property equating to a value.
 *
 * @package EEEApply\API\V1\OldStorageBroker\DatabaseBroker\Constraints
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class Equals implements DatabaseConstraintInterface
{
    /**
     * @var DatabaseValueMap
     */
    protected $dbValueMap;

    /**
     * The constructor accepts the DatabaseValueMap that this contraint is
     * bound to, as well as the property and value that should be equal.
     *
     * @param DatabaseValueMap $dvm
     * @param $property
     * @param $value
     */
    public function __construct(DatabaseValueMap $dvm, $property, $value)
    {
        $this->dbValueMap = $dvm;
        $this->dbValueMap->addProperty($property, $value);
    }

    /**
     * Returns the string representation for equality, in PDO-friendly format.
     *
     * Ex: "id=:val3_id"
     *
     * @return string
     */
    public function getSqlWithPlaceholders()
    {
        $placeholder_to_column_map = $this->dbValueMap->getPlaceholderToColumnMap();
        $column = reset($placeholder_to_column_map);
        $placeholder = key($placeholder_to_column_map);

        return "$column=$placeholder";
    }

    /**
     * Returns a DatabaseValueMap
     * @return DatabaseValueMap|DatabaseValueMap
     */
    public function getDatabaseValueMap()
    {
        return $this->dbValueMap;
    }
}