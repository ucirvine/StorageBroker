<?php
/**
 * ResultProcessorInterface.phpp * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors;

use PDO;
use PDOStatement;
use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;

/**
 * Interface ResultProcessorInterface
 *
 * A ResultProcessor collects query inputs and results, and returns a
 * higher level response (such as class instances rather than arrays).
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors
 */
interface ResultProcessorInterface
{
    /**
     * Formats and returns the result of a database operation.
     *
     * It's up to the implementing class how the result should be formatted.
     *
     * Note that the \PDO and \PDOStatement aren't type-hinted, for the sake of
     * unit testing. PDO and PDOStatement are evil to mock, so omitting
     * type hinting allows us to pass in an arbitrary test class.
     *
     * @param PDO $pdo
     * @param PDOStatement $pdo_stmt
     * @param AbstractStatement $statement
     * @return mixed
     */
    public function process($pdo, $pdo_stmt, AbstractStatement $statement);
} 