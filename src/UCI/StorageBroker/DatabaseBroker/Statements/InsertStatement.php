<?php
/**
 * InsertStatement.php13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;
use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;

/**
 * Class InsertStatement
 *
 * Used to produce Insert statements for PDO.
 *
 * Usage:
 *      $pdoStmt = new InsertStatement();
 *      $pdo_query = $pdoStmt
 *                      ->values($database_value_map)
 *                      ->getStatementText();
 *
 * @package UCI\StorageBroker\DatabaseBroker\Statements
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class InsertStatement extends AbstractStatement
{
    /**
     * Throws a DatabaseBrokerException.
     *
     * InsertStatements don't use constraints, so warn the programmer with
     * an exception if they try to set them.
     *
     * @param DatabaseConstraintInterface $constraint
     * @return $this|void
     * @throws \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function setConstraints(DatabaseConstraintInterface $constraint)
    {
        throw new DatabaseBrokerException(
            "InsertStatement doesn't support constraints"
        );
    }

    /**
     * Returns the INSERT action clause, "INSERT INTO"
     *
     * @return string
     */
    protected function getActionClause()
    {
        return 'INSERT INTO';
    }

    /**
     * Returns the values clause for this statement formatted for PDO.
     *
     * ex: (col_one, col_two) VALUES (:val_one, :val_two)
     *
     * @return string
     * @throws \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    protected function getValuesClause()
    {
        if(is_null($this->values)) {
            throw new DatabaseBrokerException(
                "setValues() must be called before getValuesClause()"
            );
        }

        if($this->values->hasProperty('id')) {
            throw new DatabaseBrokerException(
                "Insert values include 'id' column. 'id' not allowed for inserts."
            );
        }

        $placeholder_to_column_map = $this->values->getPlaceholderToColumnMap();

        $columns = array_values($placeholder_to_column_map);
        $placeholders = array_keys($placeholder_to_column_map);

        $column_list = implode(', ', $columns);
        $placeholder_list = implode(', ', $placeholders);

        return "($column_list) VALUES ($placeholder_list)";
    }

    /**
     * Insert statements don't have WHERE clauses, so return null.
     *
     * @return null|string
     */
    protected function getWhereClause()
    {
        return null;
    }

    /**
     * Returns true if values have been set. Returns false otherwise.
     *
     * @return bool
     */
    protected function statementReady()
    {
        return !is_null($this->values);
    }
} 