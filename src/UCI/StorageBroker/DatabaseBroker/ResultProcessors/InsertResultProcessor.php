<?php
/**
 * InsertResultProcessor.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors;

use PDO;
use PDOStatement;
use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;

/**
 * Class InsertResultProcessor
 *
 * Used to acquire and apply an element's new ID after a PDO insert query.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class InsertResultProcessor implements ResultProcessorInterface
{
    /**
     * Returns a copy of the original DatabaseValueMap that was inserted into
     * the database, with its new ID added.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdo_stmt
     * @param AbstractStatement $statement
     * @return DatabaseValueMap
     */
    public function process($pdo, $pdo_stmt, AbstractStatement $statement)
    {
        $dvm = $statement->getValues();
        $update_dvm = clone $dvm;
        $insert_id = $pdo->lastInsertId();
        $update_dvm->addProperty('id', $insert_id);
        return $update_dvm;
    }
} 