<?php
/**
 * InsertStatement.php13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Statements;

/**
 * Class UpdateStatement
 *
 * Used to produce Update statements for PDO.
 *
 * Usage:
 *      $pdoStmt = new UpdateStatement();
 *      $pdo_query = $pdoStmt
 *                      ->values($database_value_map)
 *                      ->where($constraints)
 *                      ->getStatementText();
 *
 * @package UCI\StorageBroker\DatabaseBroker\Statements
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class UpdateStatement extends AbstractStatement
{
    /**
     * Returns the action clause for an update statement.
     *
     * @return string
     */
    protected function getActionClause()
    {
        return 'UPDATE';
    }

    /**
     * Returns the values clause for an update statement.
     *
     * ex: (col_one, col_two) VALUES (:val_one, :val_two)
     *
     * @return string
     * @throws DatabaseStorageException if setValues() has not been called
     */
    protected function getValuesClause()
    {
        if(is_null($this->values)) {
            throw new DatabaseStorageException(
                'setValues() must be called before getValuesClause()'
            );
        }

        $placeholder_to_column_map = $this->values->getPlaceholderToColumnMap();

        $pairs = [];
        foreach($placeholder_to_column_map as $placeholder => $column) {
            $pairs[] = "$column=$placeholder";
        }

        $pair_list = implode(', ', $pairs);

        return "SET $pair_list";
    }

    /**
     * Returns true if both values and constraints have been set.
     * Returns false otherwise.
     *
     * @return bool
     */
    protected function statementReady()
    {
        return !is_null($this->values) && !is_null($this->constraints);
    }
} 