<?php
/**
 * QueryTest.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\DatabaseBroker\Query;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\ResultProcessorInterface;
use Mockery;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PDO (mock)
     */
    protected $pdo;

    /**
     * @var AbstractStatement (mock)
     */
    protected $statement;

    /**
     * @var ResultProcessorInterface (mock)
     */
    protected $resultProcessor;

    /**
     * @var DatabaseValueMap (mock)
     */
    protected $dvm;

    /**
     * @var DatabaseConstraintInterface
     */
    protected $constraints;

    /**
     * @var Query
     */
    protected $query;

    public function setUp()
    {
        $this->dvm = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'
        );
        $this->constraints = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface'
        );

        // Don't extend the actual \PDO because it's a pain the butt to mock
        $this->pdo = Mockery::mock('PDOMock');
        $this->statement = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement'
        );
        $this->resultProcessor = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\ResultProcessors\ResultProcessorInterface'
        );

        $this->query = new Query($this->pdo, $this->statement, $this->resultProcessor);
    }

    public function testValues()
    {
        $this->statement
            ->shouldReceive('setValues')
            ->with($this->dvm)
            ->once();

        $this->query->values($this->dvm);
    }

    public function testWhere()
    {
        $this->statement
            ->shouldReceive('setConstraints')
            ->with($this->constraints)
            ->once();

        $this->query->where($this->constraints);
    }

    public function testRun()
    {
        // Set up test constants
        $statement_text = "INSERT INTO some_table (col) VALUES ('value');";
        $map = [':col' => 'value'];
        // Don't mock the actual \PDOStatement, because it's a pain in the butt
        $pdo_stmt = Mockery::mock('PDOStatementMock');
        $result = ['winning!'];

        // We need to set the values and constraints, so make sure that our mocks are ready
        $this->statement->shouldReceive('setValues');
        $this->statement->shouldReceive('setConstraints');


        // Set up expectations for the test
        $this->statement
            ->shouldReceive('getStatementText')
            ->andReturn($statement_text);
        $this->statement
            ->shouldReceive('getPlaceholderToValueMap')
            ->andReturn($map);
        $this->pdo
            ->shouldReceive('prepare')
            ->with($statement_text)
            ->andReturn($pdo_stmt);
        $pdo_stmt
            ->shouldReceive('execute')
            ->with($map)
            ->once();
        $this->resultProcessor
            ->shouldReceive('process')
            ->with($this->pdo, $pdo_stmt, $this->statement)
            ->andReturn($result);

        $this->query
            ->values($this->dvm)
            ->where($this->constraints);
        $this->assertEquals($result, $this->query->run());
    }
} 