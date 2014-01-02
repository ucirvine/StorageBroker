<?php
/**
 * AbstractStatement.php
 * 11/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;

/**
 * Class AbstractStatement
 *
 * Base class for SQL statements. All statement classes produce a plain text
 * SQL query that is intended for use with PDO (it contains placeholders
 * instead of actual values)
 *
 * Each child class represents a basic
 * SQL statement type (SELECT, INSERT, UPDATE, or DELETE). They all
 * have the same input methods, but output different statements
 * given the values provided.
 *
 * This base class provides shared functions, while leaving the children
 * to implement methods that are specific to their statement type.
 *
 * In general, using this class will looking something like this:
 *    $pdoStmt = new UpdateStatement; // or whatever type
 *    $stmt_text = $pdoStmt
 *      ->setValues($database_value_map) // if relevant
 *      ->setConstraints($database_constraint) // if relevant
 *      ->getStatementText();
 *
 * In practice, it's recommended that DatabaseStatementBuilder be used to
 * build these.
 *
 * @package UCI\StorageBroker\DatabaseBroker\Statements
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
abstract class AbstractStatement
{
    /**
     * Values that will be populated into the setter portion of the query
     *
     * @var DatabaseValueMap
     */
    protected $values = null;

    /**
     * Constraints that will translate into the WHERE portion of the query
     *
     * @var DatabaseConstraintInterface
     */
    protected $constraints = null;

    /**
     * Returns the action clause for this statement.
     *
     * This will likely be
     * "INSERT INTO", "UPDATE", "SELECT * FROM", or "DELETE FROM"
     *
     * @return string
     */
    abstract protected function getActionClause();

    /**
     * Returns the values clause for this statement.
     *
     * This only applies to INSERTS and UPDATES. Other query types should
     * return null.
     *
     * @return string
     */
    abstract protected function getValuesClause();

    /**
     * Returns true if all required information has been provided
     * (values and or constraints). Returns false otherwise.
     *
     * @return bool
     */
    abstract protected function statementReady();

    /**
     * Sets the DatabaseValueMap that will provide the value clause of the
     * resulting statement.
     *
     * Returns this instance, so this method can be chained.
     *
     * @param DatabaseValueMap $dvm
     * @return $this
     * @throws DatabaseBrokerException if data is provided that is not compatible
     *                                 with existing constraints
     */
    public function setValues(DatabaseValueMap $dvm)
    {
        $this->values = $dvm;
        $this->checkValueAndConstraintCompatibility();

        return $this;
    }

    /**
     * Returns this statement's values
     *
     * @return DatabaseValueMap
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Sets the constraints that will provide the WHERE clause of the resulting
     * statement.
     *
     * It's worth noting that the constraints may be compound
     * (ie. date<=2013-12-05 AND date>=2013-12-01), in which case they
     * will still be passed in as a single AND constraints that contains
     * both sub-constraints.
     *
     * Returns this instance, so this method can be chained.
     *
     * @param DatabaseConstraintInterface $constraints
     * @return $this
     * @throws DatabaseBrokerException if constraints are provided that are
     *                                 not compatible with existing values data
     */
    public function setConstraints(DatabaseConstraintInterface $constraints)
    {
        $this->constraints = $constraints;
        $this->checkValueAndConstraintCompatibility();

        return $this;
    }

    /**
     * Returns this statement's constraints.
     *
     * @return DatabaseConstraintInterface
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Returns the full text of the SQL statement, with PDO placeholders.
     *
     * @return string
     * @throws DatabaseBrokerException
     */
    public function getStatementText()
    {
        if(!$this->statementReady()) {
            throw new DatabaseBrokerException(
                'Cannot create statement text. Required values are not set.'
            );
        }

        // put each clause of the statement in order in an array
        // (some of them may be null)
        $clauses = [
            $this->getActionClause(),
            $this->getTableName(),
            $this->getValuesClause(),
            $this->getWhereClause()
        ];
        // remove empty clauses
        $clauses = array_filter($clauses);

        // combine the clauses, delimited by spaces, and add a semicolon
        $text = implode(' ', $clauses) . ';';

        // ding! fries are done!
        return $text;
    }

    /**
     * Returns a map (array) of placeholder names and their values for this
     * statement.
     *
     * The elements in the map will be a combination of all placeholders from
     * the $values and $constraints properties.
     *
     * @return array
     * @throws \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function getPlaceholderToValueMap()
    {
        if(!$this->statementReady()) {
            throw new DatabaseBrokerException(
                'Cannot create placehoder to value map. Required values are not set.'
            );
        }

        // Figure out what our cumulative DatabaseValueMap is based on whether
        // values and/or constraints were set
        if(!is_null($this->values) && !is_null($this->constraints)) {
            // If we have both values and constraints, merge their maps
            $db_value_map = $this->values
                ->merge($this->constraints->getDatabaseValueMap());
        }
        elseif(!is_null($this->values)) {
            $db_value_map = $this->values;
        }
        elseif(!is_null($this->constraints)) {
            $db_value_map = $this->constraints->getDatabaseValueMap();
        }
        else {
            $class = get_class($this);
            throw new DatabaseBrokerException(
                "Uh oh! Values and constrains are both unset! " .
                "Check your {$class}::statementReady function!"
            );
        }

        return $db_value_map->getPlaceholderToValueMap();
    }

    /**
     * Returns the name of the table that this statement will act upon.
     *
     * The table name can be derived from either information provided to
     * setValues() or to setConstraints(), since each type of statement will
     * have at least one. If neither has been provided, an exception is thrown.
     *
     * @return string
     * @throws DatabaseBrokerException
     */
    protected function getTableName()
    {
        if(!is_null($this->values)) {
            return $this->values
                ->getTableName();
        }
        elseif(!is_null($this->constraints)) {
            return $this->constraints
                ->getDatabaseValueMap()
                ->getTableName();
        }
        else {
            throw new DatabaseBrokerException(
                'setValues() or setConstraints() must be called before getTableName()'
            );
        }
    }

    /**
     * Returns the text of the WHERE clause. If no constraints were
     * provided, returns null.
     *
     * @return string
     */
    protected function getWhereClause()
    {
        if(is_null($this->constraints)) {
            return null;
        }

        $clause = $this->constraints->getSqlWithPlaceholders();
        return "WHERE $clause";
    }

    /**
     * Ensures that if both a values and constraints have been set, they
     * are compatible with eachother (based on the same DatabaseObjectProfile).
     * If they're not, it will throw an exception.
     *
     * @returns void
     * @throws \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    private function checkValueAndConstraintCompatibility()
    {
        // If data and constraints haven't both been set, don't worry about it
        if(!is_null($this->values) && !is_null($this->constraints)) {
            $constraints_db_value_map = $this->constraints->getDatabaseValueMap();

            // See if the DatabaseValueMaps are compatible with eachother
            if(!$this->values->isCompatible($constraints_db_value_map)) {
                throw new DatabaseBrokerException(
                    'Provided values and constraints are not compatible'
                );
            }
        }
    }
} 