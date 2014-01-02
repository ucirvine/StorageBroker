<?php
/**
 * DatabaseConstraintInterface.php
 * 11/26/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;

/**
 * Interface DatabaseConstraintInterface
 *
 * Database constraints are translatable to the "WHERE" clause of a SQL
 * statement. All constraints that implement this interface should be able
 * to return both their textual representation (as they would appear in
 * a SQL statement) and a DatabaseValueMap of the values contained.
 *
 * Database constraints are technically nodes, allowing them to be chained
 * together in trees. For example, to represent "AND", you might have an
 * 'And' constraints that contains two other constraints and combines them.
 *
 * @package EEEApply\API\V1\OldStorageBroker\DatabaseBroker\Constraints
 */
interface DatabaseConstraintInterface
{
    /**
     * Returns the text format of this constraints. The value portion of the
     * constraints should be represented as a PDO placeholder.
     *
     * Ex: $equals_constraint->getSqlWithPlaceholders() = "foo=:bar"
     *
     * @return string
     */
    public function getSqlWithPlaceholders();

    /**
     * Returns a DatabaseValueMap containing all value mappings from this
     * constraints. Since the text form of this constraints contains placeholders,
     * the mapping is needed to get the actual values that belong in the
     * placeholders.
     *
     * @return DatabaseValueMap
     */
    public function getDatabaseValueMap();
} 