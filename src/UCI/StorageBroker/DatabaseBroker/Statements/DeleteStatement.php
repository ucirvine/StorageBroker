<?php
/**
 * DeleteStatement.php
 * 11/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;

/**
 * Class DeleteStatement
 *
 * Used to produce Delete statements for PDO.
 *
 * Usage:
 *      $pdoStmt = new DeleteStatement();
 *      $pdo_query = $pdoStmt
 *                      ->where($constraints)
 *                      ->getStatementText();
 *
 * @package UCI\StorageBroker\DatabaseBroker\Statements
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DeleteStatement extends AbstractStatement
{
    /**
     * Throws a DatabaseBrokerException
     *
     * There's no need to set values on a DeleteStatement, so warn the
     * programmer with an exception if they try to set them.
     *
     * @param DatabaseValueMap $dvm
     * @return void
     * @throws \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function setValues(DatabaseValueMap $dvm)
    {
        throw new DatabaseBrokerException(
            'SelectStatement does not require values'
        );
    }

    /**
     * Returns the action clause for a Delete statement.
     *
     * @return string
     */
    protected function getActionClause()
    {
        return 'DELETE FROM';
    }

    /**
     * Returns the values clause for a Delete statement, which doesn't exist,
     * so it returns null.
     *
     * @return null
     */
    protected function getValuesClause()
    {
        return null;
    }

    /**
     * Returns true if setConstraints() has been called, indicating that the
     * statement is ready to be turned into text. Returns false otherwise.
     *
     * @return bool
     */
    protected function statementReady()
    {
        return !is_null($this->constraints);
    }
} 