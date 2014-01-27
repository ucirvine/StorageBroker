<?php
/**
 * Any.php
 * 1/27/14
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Constraints;


use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;

/**
 * Class Equals
 *
 * Allows selection of all rows in a table. Combining this constraint with
 * others is ill-advised.
 *
 * @package EEEApply\API\V1\OldStorageBroker\DatabaseBroker\Constraints
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class Any implements DatabaseConstraintInterface
{
    /**
     * @var DatabaseValueMap
     */
    protected $dbValueMap;

    public function __construct(DatabaseValueMap $dvm)
    {
        $this->dbValueMap = $dvm;
    }

    /**
     * Returns the string "1=1". This, when placed in the WHERE clause of
     * a SQL query, will select all rows.
     *
     * @return string
     */
    public function getSqlWithPlaceholders()
    {
        return '1=1';
    }

    /**
     * Returns a DatabaseValueMap containing all value mappings from this
     * constraints. Since the text form of this constraints contains placeholders,
     * the mapping is needed to get the actual values that belong in the
     * placeholders.
     *
     * @return DatabaseValueMap
     */
    public function getDatabaseValueMap()
    {
        return $this->dbValueMap;
    }

} 