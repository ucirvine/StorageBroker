<?php
/**
 * DeleteResultProcessor.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors;


use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use PDO;
use PDOStatement;

/**
 * Class DeleteResultProcessor
 *
 * Used to determine whether any rows were actually deleted after a PDO delete
 * query.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DeleteResultProcessor implements ResultProcessorInterface
{
    /**
     * Returns true if at least one element was successfully deleted.
     * Returns false otherwise.
     *
     * Note that 'false' suggests that the query successfully executed, but didn't
     * find any matching rows to delete.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdo_stmt
     * @param AbstractStatement $statement
     * @return bool
     */
    public function process($pdo, $pdo_stmt, AbstractStatement $statement)
    {
        return ($pdo_stmt->rowCount() > 0);
    }
} 