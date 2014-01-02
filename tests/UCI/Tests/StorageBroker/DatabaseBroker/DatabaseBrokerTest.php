<?php
/**
 * DatabaseBrokerTest.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\DatabaseBroker\DatabaseBroker;
use UCI\StorageBroker\DatabaseBroker\Query;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Factories\QueryFactory;
use UCI\StorageBroker\DatabaseBroker\Constraints\BoundDatabaseConstraintFactory;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintFactoryBinder;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use UCI\TypeConverter\TypeConverter;
use Mockery;

class DatabaseBrokerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Query
     */
    protected $query;

    /**
     * @var QueryFactory (mock)
     */
    protected $queryFactory;

    /**
     * @var DatabaseConstraintFactoryBinder (mock)
     */
    protected $constraintFactoryBinder;

    /**
     * @var BoundDatabaseConstraintFactory (mock)
     */
    protected $constraintFactory;

    /**
     * @var TypeConverter (mock)
     */
    protected $typeConverter;

    /**
     * @var DatabaseConstraintInterface (mock)
     */
    protected $constraints;

    /**
     * @var \stdClass
     */
    protected $object;

    /**
     * @var DatabaseValueMap
     */
    protected $dvm;

    /**
     * @var string
     */
    protected $className = 'SomeNamespace\SomeClass';

    /**
     * @var DatabaseBroker
     */
    protected $databaseBroker;

    /**
     * Sets up our many mocks, and our test object
     */
    public function setUp()
    {
        $this->query = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Query'
        );

        $this->queryFactory = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Factories\QueryFactory'
        );

        $this->constraintFactoryBinder = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintFactoryBinder'
        );

        $this->constraintFactory = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\BoundDatabaseConstraintFactory'
        );

        $this->typeConverter = Mockery::mock(
            'UCI\TypeConverter\TypeConverter'
        );

        $this->constraints = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface'
        );

        $this->object = Mockery::mock(
            'SomeObject'
        );

        $this->dvm = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            ['getClassName' => $this->className]
        );

        // Prepare the test object
        $this->databaseBroker = new DatabaseBroker(
            $this->queryFactory,
            $this->constraintFactoryBinder,
            $this->typeConverter
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testGet()
    {
        $result_count = 3;

        $dvms = [];
        for($i = 0; $i < $result_count; $i++) {
            $dvms[] = Mockery::mock(
                'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
                ['getClassName' => $this->className]
            );
        }

        $objects = [];
        for($i = 0; $i < $result_count; $i++) {
            $objects[] = Mockery::mock(
                'SomeObject'
            );
        }

        $this->queryFactory
            ->shouldReceive(['select' => $this->query])
            ->once();
        $this->query
            ->shouldReceive(['where' => $this->query])
            ->with($this->constraints)
            ->once();
        $this->query
            ->shouldReceive(['run' => $dvms])
            ->once();
        $this->query
            ->shouldReceive('values')
            ->withAnyArgs()
            ->never();

        for($i = 0; $i < $result_count; $i++) {
            $this->typeConverter
                ->shouldReceive(['convert' => $objects[$i]])
                ->with($dvms[$i], $this->className)
                ->once();
        }

        $this->assertSame($objects, $this->databaseBroker->get($this->constraints));
    }

    public function testSaveInsert()
    {
        $output_dvm = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            ['getClassName' => $this->className]
        );
        $output_object = Mockery::mock('SomeObject');

        $this->typeConverter
            ->shouldReceive(['convert' => $this->dvm])
            ->with($this->object, 'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap');
        $this->typeConverter
            ->shouldReceive(['convert' => $output_object])
            ->with($output_dvm, $this->className);

        $this->dvm
            ->shouldReceive(['hasProperty' => false])
            ->with('id');

        $this->queryFactory
            ->shouldReceive(['insert' => $this->query])
            ->once();
        $this->query
            ->shouldReceive(['values' => $this->query])
            ->with($this->dvm)
            ->once();
        $this->query
            ->shouldReceive(['run' => $output_dvm])
            ->once();
        $this->query
            ->shouldReceive('where')
            ->withAnyArgs()
            ->never();

        $this->assertSame($output_object, $this->databaseBroker->save($this->object));
    }

    public function testSaveUpdate()
    {
        $output_dvm = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            ['getClassName' => $this->className]
        );
        $output_object = Mockery::mock('SomeObject');

        $this->typeConverter
            ->shouldReceive(['convert' => $this->dvm])
            ->with($this->object, 'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap');
        $this->typeConverter
            ->shouldReceive(['convert' => $output_object])
            ->with($output_dvm, $this->className);

        $this->dvm
            ->shouldReceive(['hasProperty' => true])
            ->with('id');
        $this->dvm->shouldReceive(['getPropertyToValueMap' => ['id' => 123]]);
        $this->constraintFactoryBinder
            ->shouldReceive(['getBound' => $this->constraintFactory])
            ->with($this->className);
        $this->constraintFactory
            ->shouldReceive(['equals' => $this->constraints])
            ->with('id', 123);

        $this->queryFactory
            ->shouldReceive(['update' => $this->query])
            ->once();
        $this->query
            ->shouldReceive(['values' => $this->query])
            ->with($this->dvm)
            ->once();
        $this->query
            ->shouldReceive(['where' => $this->query])
            ->with($this->constraints)
            ->once();
        $this->query
            ->shouldReceive(['run' => $output_dvm])
            ->once();

        $this->assertSame($output_object, $this->databaseBroker->save($this->object));
    }

    public function testDeleteSuccess()
    {
        $this->queryFactory
            ->shouldReceive(['delete' => $this->query])
            ->once();
        $this->query
            ->shouldReceive(['where' => $this->query])
            ->with($this->constraints)
            ->once();
        $this->query
            ->shouldReceive(['run' => true])
            ->once();
        $this->query
            ->shouldReceive('values')
            ->withAnyArgs()
            ->never();

        $this->databaseBroker->delete($this->constraints);
    }

    /**
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testDeleteFail()
    {
        $this->queryFactory
            ->shouldReceive(['delete' => $this->query])
            ->once();
        $this->query
            ->shouldReceive(['where' => $this->query])
            ->with($this->constraints)
            ->once();
        $this->query
            ->shouldReceive(['run' => false])
            ->once();
        $this->query
            ->shouldReceive('values')
            ->withAnyArgs()
            ->never();

        $this->databaseBroker->delete($this->constraints);
    }
} 