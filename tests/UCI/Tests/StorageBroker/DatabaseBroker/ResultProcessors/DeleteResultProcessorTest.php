<?php
/**
 * DeleteResultProcessorTest.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\ResultProcessors;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\DeleteResultProcessor;
use Mockery;

class DeleteResultProcessorTest extends AbstractResultProcessorTestBase
{
    /**
     * @var DeleteResultProcessor
     */
    protected $processor;

    public function setUp()
    {
        parent::setUp();

        $this->processor = new DeleteResultProcessor();
    }

    public function testProcessFalse()
    {
        $this->pdoStmt
            ->shouldReceive('rowCount')
            ->andReturn(0);
        $this->assertFalse(
            $this->processor->process($this->pdo, $this->pdoStmt, $this->statement)
        );
    }

    public function testProcessTrue()
    {
        $this->pdoStmt
            ->shouldReceive('rowCount')
            ->andReturnValues([1, 2, 5]);
        for($i = 0; $i < 3; $i++) {
            $this->assertTrue(
                $this->processor->process($this->pdo, $this->pdoStmt, $this->statement)
            );
        }
    }
} 