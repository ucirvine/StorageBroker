<?php
/**
 * AbstractResultProcessorTestBase.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\ResultProcessors;

use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use Mockery;

abstract class AbstractResultProcessorTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var \PDOStatement
     */
    protected $pdoStmt;

    /**
     * @var DatabaseValueMap
     */
    protected $dvm;

    /**
     * @var DatabaseConstraintInterface
     */
    protected $constraints;

    /**
     * @var AbstractStatement
     */
    protected $statement;

    /**
     * @var string
     */
    protected $className = 'MyNamespace\MyClass';

    public function setUp()
    {
        $this->pdo = Mockery::mock('PDOMock');
        $this->pdoStmt = Mockery::mock('PDOStatementMock');
        $this->dvm = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            ['getClassName' => $this->className]
        );
        $this->constraints = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface',
            ['getDatabaseValueMap' => $this->dvm]
        );
        $this->statement = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement',
            [
                'getValues' => $this->dvm,
                'getConstraints' => $this->constraints
            ]
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }
}