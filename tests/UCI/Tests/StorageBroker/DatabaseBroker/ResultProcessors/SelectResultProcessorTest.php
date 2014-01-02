<?php
/**
 * SelectResultProcessorTest.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\ResultProcessors;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\SelectResultProcessor;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;
use Mockery;

class SelectResultProcessorTest extends AbstractResultProcessorTestBase
{
    /**
     * @var SelectResultProcessor
     */
    protected $processor;

    /**
     * @var DatabaseValueMapFactory (mock)
     */
    protected $dvmFactory;

    public function setUp()
    {
        parent::setUp();

        $this->dvmFactory = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory'
        );

        $this->processor = new SelectResultProcessor($this->dvmFactory);
    }

    public function testProcessEmpty()
    {
        $this->pdoStmt
            ->shouldReceive('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn(null);

        $this->assertEquals([], $this->processor->process($this->pdo, $this->pdoStmt, $this->statement));
    }

    public function testProcess()
    {
        $this->dvm
            ->shouldReceive('getClassName')
            ->andReturn('MyClass');
        // The last row needs to be null, or fetch() will be called forever
        $rows = [
            ['col1' => 'val1', 'col2' => 'val2', 'col3' => 'val3'],
            ['col1' => 'val4', 'col2' => 'val5', 'col3' => 'val6'],
            ['col1' => 'val7', 'col2' => 'val8', 'col3' => 'val9'],
            null
        ];
        $this->pdoStmt
            ->shouldReceive('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->andReturnValues($rows);
        $dvms = [
            Mockery::mock('UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'),
            Mockery::mock('UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'),
            Mockery::mock('UCI\StorageBroker\DatabaseBroker\DatabaseValueMap')
        ];
        for($i = 0; $i < 3; $i++) {
            $dvms[$i]
                ->shouldReceive('addColumns')
                ->with($rows[$i])
                ->once();
        }
        $this->dvmFactory
            ->shouldReceive('build')
            ->with('MyClass')
            ->andReturnValues($dvms);

        $this->assertSame($dvms, $this->processor->process($this->pdo, $this->pdoStmt, $this->statement));
    }
} 