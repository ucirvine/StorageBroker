<?php
/**
 * UpdateResultProcessorTest.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\ResultProcessors;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\UpdateResultProcessor;


class UpdateResultProcessorTest extends AbstractResultProcessorTestBase
{
    /**
     * @var UpdateResultProcessor
     */
    protected $processor;

    public function setUp()
    {
        parent::setUp();

        $this->processor = new UpdateResultProcessor();
    }

    public function testProcess()
    {
        $this->assertEquals($this->dvm, $this->processor->process($this->pdo, $this->pdoStmt, $this->statement));
    }
} 