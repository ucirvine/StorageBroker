<?php
/**
 * InsertResultProcessorTest.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\ResultProcessors;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\InsertResultProcessor;

class InsertResultProcessorTest extends AbstractResultProcessorTestBase
{
    /**
     * @var InsertResultProcessor
     */
    protected $processor;

    public function setUp()
    {
        parent::setUp();

        $this->processor = new InsertResultProcessor();
    }

    public function testProcess()
    {
        $this->pdo
            ->shouldReceive('lastInsertId')
            ->andReturn(99);
        $this->dvm
            ->shouldReceive('addProperty')
            ->with('id', 99)
            ->once();

        $this->assertEquals($this->dvm, $this->processor->process($this->pdo, $this->pdoStmt, $this->statement));
    }
} 