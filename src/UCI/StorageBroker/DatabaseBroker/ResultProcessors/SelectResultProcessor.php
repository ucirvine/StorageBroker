<?php
/**
 * SelectResultProcessor.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors;


use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;
use PDO;
use PDOStatement;

/**
 * Class SelectResultProcessor
 *
 * Used to convert the result of a PDO select query into an array of DatabaseValueMaps.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class SelectResultProcessor implements ResultProcessorInterface
{
    /**
     * Used to create new DatabaseValueMaps to place results into.
     *
     * @var DatabaseValueMapFactory
     */
    protected $dvmFactory;

    /**
     * Accepts dependencies.
     *
     * @param DatabaseValueMapFactory $dvm_factory
     */
    public function __construct(DatabaseValueMapFactory $dvm_factory)
    {
        $this->dvmFactory = $dvm_factory;
    }

    /**
     * Returns an array of DatabaseValueMaps, one for each row in the query
     * result. Results with one row will return an array containing one
     * DatabaseValueMap and empty results will return an empty array.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdo_stmt
     * @param AbstractStatement $statement
     * @return array
     */
    public function process($pdo, $pdo_stmt, AbstractStatement $statement)
    {
        $class_name = $statement->getConstraints()->getDatabaseValueMap()->getClassName();

        $a = [];
        while($row = $pdo_stmt->fetch(PDO::FETCH_ASSOC)) {
            $row_dvm = $this->dvmFactory->build($class_name);
            $row_dvm->addColumns($row);
            $a[] = $row_dvm;
        }
        return $a;
    }

} 