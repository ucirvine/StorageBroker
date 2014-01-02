<?php
/**
 * SelectStatement.php
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;

/**
 * Class SelectStatement
 *
 * Used to produce Select statements for PDO.
 *
 * Usage:
 *      $pdoStmt = new SelectStatement();
 *      $pdo_query = $pdoStmt
 *                      ->where($constraints)
 *                      ->getStatementText();
 *
 * @package UCI\StorageBroker\DatabaseBroker\Statements
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class SelectStatement extends AbstractStatement
{
    /**
     * Throws a DatabaseBrokerException
     *
     * There's no need to set values on a SelectStatement, so warn the
     * programmer with an exception if they try to.
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
     * Returns the Select action clause, "SELECT * FROM"
     *
     * @return string
     */
    protected function getActionClause()
    {
        return 'SELECT * FROM';
    }

    /**
     * Select statements don't have a values clause, so return null.
     *
     * @return null
     */
    protected function getValuesClause()
    {
        return null;
    }

    /**
     * Returns true if constraints have been set. False otherwise.
     *
     * @return bool
     */
    protected function statementReady()
    {
        return !is_null($this->constraints);
    }
} 