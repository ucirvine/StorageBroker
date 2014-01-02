<?php
/**
 * UpdateResultProcessor.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors;


use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use PDO;
use PDOStatement;

/**
 * Class UpdateResultProcessor
 *
 * For the time being, just returns the element that was used to generate
 * a PDO update query.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class UpdateResultProcessor implements ResultProcessorInterface
{
    /**
     * Returns a copy of the DatabaseValueMap that was updated in the database.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdo_stmt
     * @param AbstractStatement $statement
     * @return DatabaseValueMap
     */
    public function process($pdo, $pdo_stmt, AbstractStatement $statement)
    {
        $dvm = $statement->getValues();
        return clone $dvm;
    }

} 