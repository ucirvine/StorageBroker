<?php
/**
 * DatabaseValueMapTest.php
 * 11/21/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker;

use Mockery;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;

class DatabaseValueMapTest extends \PHPUnit_Framework_TestCase
{
    private $propertyToColumnMap = [
        'propOne'   => 'col_one',
        'propTwo'   => 'col_two',
        'propThree' => 'col_three',
        'propFour'  => 'col_four'
    ];

    private $propertyToValueMapOne = [
        'propOne'    => 'A',
        'propTwo'    => 'B',
        'propThree'  => 'C',
        'propFour'   => 'D'
    ];

    private $columnToValueMapOne = [
        'col_one'   => 'A',
        'col_two'    => 'B',
        'col_three'  => 'C',
        'col_four'   => 'D'
    ];

    private $propertyToValueMapTwo = [
        'propOne' => 'A',
        'propTwo' => 'E'
    ];

    private $className = 'MyNamespace\MyClass';

    private $tableName = 'my_table';

    /**
     * @var DatabaseObjectProfile (mock)
     */
    private $mockDbObjectProfile;

    /**
     * @var DatabaseValueMapFactory (mock)
     */
    private $mockDbValueMapFactory;

    public function setUp()
    {
        $this->mockDbObjectProfile = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile',
            [
                'getClassName' => $this->className,
                'getTableName' => $this->tableName
            ]
        );

        // set up responses for property to column requests, and vice versa
        foreach($this->propertyToColumnMap as $prop => $col) {
            $this->mockDbObjectProfile
                ->shouldReceive('getColumnFromProperty')
                ->with($prop)
                ->andReturn($col);
            $this->mockDbObjectProfile
                ->shouldReceive('getPropertyFromColumn')
                ->with($col)
                ->andReturn($prop);
        }

        // Create a stand-in factory
        $this->mockDbValueMapFactory = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory'
        );
        $dvmf = $this->mockDbValueMapFactory;
        $dop = $this->mockDbObjectProfile;
        $this->mockDbValueMapFactory
            ->shouldReceive('build')
            ->with($this->className)
            ->andReturnUsing(function() use($dvmf, $dop) {
                static $i = 0;
                $i++;
                return new DatabaseValueMap($dvmf, $dop, $i);
            });
    }

    private function getDatabaseValueMap()
    {
        return $this->mockDbValueMapFactory->build($this->className);
    }

    /**
     * Each instance of DatabaseValueMap should produce unique placeholder
     * names, even given the same DatabaseObjectProfile and properties/values.
     */
    public function testConstructor()
    {
        // Create two identical DatabaseValueMaps
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperty('propOne', 'A');

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperty('propOne', 'A');

        // The column to value maps should be the same
        $this->assertEquals(
            $dvm_one->getColumnToValueMap(),
            $dvm_two->getColumnToValueMap()
        );

        // The placeholder to value maps should be different
        $this->assertNotEquals(
            $dvm_one->getPlaceholderToValueMap(),
            $dvm_two->getPlaceholderToValueMap()
        );
    }

    public function testGetDatabaseObjectProfile()
    {
        $dvm = $this->getDatabaseValueMap();
        $this->assertEquals(
            $this->mockDbObjectProfile,
            $dvm->getDatabaseObjectProfile()
        );
    }

    public function testGetTableName()
    {
        $dvm = $this->getDatabaseValueMap();
        $this->assertEquals($this->tableName, $dvm->getTableName());
    }

    public function testGetClassName()
    {
        $dvm = $this->getDatabaseValueMap();
        $this->assertEquals($this->className, $dvm->getClassName());
    }

    public function testAddProperty()
    {
        $dvm = $this->getDatabaseValueMap();

        $this->assertEquals(0, count($dvm->getPropertyToColumnMap()));

        $dvm->addProperty('propOne', 'myValOne');
        $this->assertEquals(1, count($dvm->getPropertyToColumnMap()));

        $dvm->addProperty('propTwo', 'myValTwo');
        $this->assertEquals(
            ['col_one' => 'myValOne', 'col_two' => 'myValTwo'],
            $dvm->getColumnToValueMap()
        );
    }

    public function testAddProperties()
    {
        $dvm = $this->getDatabaseValueMap();

        $this->assertEquals(0, count($dvm->getPropertyToColumnMap()));

        $dvm->addProperties($this->propertyToValueMapOne);
        $this->assertEquals($this->columnToValueMapOne, $dvm->getColumnToValueMap());
    }

    public function testHasProperty()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);

        $this->assertTrue($dvm->hasProperty('propOne'));
        $this->assertTrue($dvm->hasProperty('propTwo'));
        $this->assertFalse($dvm->hasProperty('propThree'));
    }

    public function testRemoveProperty()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapOne);

        // Perform a sanity check to ensure that everything is as it should be
        $this->assertEquals(4, count($dvm->getPropertyToValueMap()));

        // Remove a center element (not the first or last)
        $dvm->removeProperty('propTwo');
        $this->assertEquals(
            ['propOne' => 'A', 'propThree' => 'C', 'propFour' => 'D'],
            $dvm->getPropertyToValueMap()
        );

        // Remove the first element
        $dvm->removeProperty('propOne');
        $this->assertEquals(
            ['propThree' => 'C', 'propFour' => 'D'],
            $dvm->getPropertyToValueMap()
        );

        // Remove the tail element
        $dvm->removeProperty('propFour');
        $this->assertEquals(
            ['propThree' => 'C'],
            $dvm->getPropertyToValueMap()
        );

        // Remove the only remaining element
        $dvm->removeProperty('propThree');
        $this->assertEquals([], $dvm->getPropertyToValueMap());

        // Make sure the we haven't broken the object
        $dvm->addProperty('propTwo', 'foo');
        $this->assertTrue($dvm->hasProperty('propTwo'));
    }

    /**
     * Make sure that trying to remove a property that hasn't been set throws
     * an exception.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testRemovePropertyException()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);

        $dvm->removeProperty('propThree');
    }

    public function testAddColumn()
    {
        $dvm = $this->getDatabaseValueMap();

        $this->assertEquals(0, count($dvm->getPropertyToColumnMap()));

        $dvm->addColumn('col_one', 'myValOne');
        $this->assertEquals(1, count($dvm->getPropertyToColumnMap()));

        $dvm->addColumn('col_two', 'myValTwo');
        $this->assertEquals(
            ['propOne' => 'myValOne', 'propTwo' => 'myValTwo'],
            $dvm->getPropertyToValueMap()
        );
    }

    public function testAddColumns()
    {
        $dvm = $this->getDatabaseValueMap();

        $this->assertEquals(0, count($dvm->getPropertyToColumnMap()));

        $dvm->addColumns($this->columnToValueMapOne);
        $this->assertEquals($this->propertyToValueMapOne, $dvm->getPropertyToValueMap());
    }

    public function testIsCompatible()
    {
        $mock_db_object_profile_two = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile',
            ['getClassName' => 'MyNamespace\SomeOtherClass']
        );

        $dvm = $this->getDatabaseValueMap();
        $dvm_compatible = $this->getDatabaseValueMap();
        $dvm_incompatible = new DatabaseValueMap(
            $this->mockDbValueMapFactory,
            $mock_db_object_profile_two,
            'a'
        );

        $this->assertTrue($dvm->isCompatible($dvm_compatible));
        $this->assertFalse($dvm->isCompatible($dvm_incompatible));
    }

    public function testMerge()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        // Ensure that there are 6 elements in the merged DVM
        $placeholder_to_value_map = $merged->getPlaceholderToValueMap();
        $this->assertEquals(6, count($placeholder_to_value_map));

        // There should be six unique placeholders
        $unique_placeholders = array_unique(array_keys($placeholder_to_value_map));
        $this->assertEquals(6, count($unique_placeholders));

        // All values should be preserved, including A repeated
        $sorted_values = array_values($placeholder_to_value_map);
        sort($sorted_values);
        $this->assertEquals(['A', 'A', 'B', 'C', 'D', 'E'], $sorted_values);

        // Make sure tha the original maps are unchanged
        $this->assertEquals(4, count($dvm_one->getPlaceholderToValueMap()));
        $this->assertEquals(2, count($dvm_two->getPlaceholderToValueMap()));
    }

    /**
     * Merges involving empty maps shouldn't be a problem
     */
    public function testMergeEmpty()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_two = $this->getDatabaseValueMap();

        // We should be able to merge two empty maps without things exploding
        $merged = $dvm_one->merge($dvm_two);
        $this->assertEmpty($merged->getPlaceholderToValueMap());

        $dvm_one->addProperties($this->propertyToValueMapTwo);

        // We should be able to merge an empty map into a populated map
        $merged = $dvm_one->merge($dvm_two);
        $this->assertEquals(2, count($merged->getPlaceholderToValueMap()));

        // We should be able to merge a populated map into an empty map
        $merged = $dvm_two->merge($dvm_one);
        $this->assertEquals(2, count($merged->getPlaceholderToValueMap()));
    }

    /**
     * You should not be able to merge DatabaseValueMaps that are based on
     * different DatabaseObjectProfiles.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testMergeException()
    {
        $mock_db_object_profile_two = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile',
            ['getClassName' => 'MyNamespace\SomeOtherClass']
        );

        $dvm_one = $this->getDatabaseValueMap();
        $dvm_two = new DatabaseValueMap(
            $this->mockDbValueMapFactory,
            $mock_db_object_profile_two,
            'b'
        );

        $merged = $dvm_one->merge($dvm_two);
    }

    public function testGetPropertyToColumnMap()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);
        $map = $dvm->getPropertyToColumnMap();
        $this->assertEquals(['propOne' => 'col_one', 'propTwo' => 'col_two'], $map);
    }

    /**
     * Merged DatabaseValueMaps SHOULD be able to return a property to
     * column map
     */
    public function testGetPropertyToColumnMapMerged()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        $merged->getPropertyToColumnMap();
    }

    public function testGetPropertyToValueMap()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);
        $map = $dvm->getPropertyToValueMap();
        $this->assertEquals(['propOne' => 'A', 'propTwo' => 'E'], $map);
    }

    /**
     * Merged DatabaseValueMaps should NOT be able to return a property to
     * value map
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetPropertyToValueMapMergeException()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        $merged->getPropertyToValueMap();
    }

    public function testGetColumnToValueMap()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);
        $map = $dvm->getColumnToValueMap();
        $this->assertEquals(['col_one' => 'A', 'col_two' => 'E'], $map);
    }

    /**
     * Merged DatabaseValueMaps should NOT be able to return a column to
     * value map
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetColumnToValueMapMergeException()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        $merged->getColumnToValueMap();
    }

    public function testGetPlaceholderToColumnMap()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);
        $map = $dvm->getPlaceholderToColumnMap();
        $columns = array_values($map);
        $this->assertEquals(['col_one', 'col_two'], $columns);
    }

    /**
     * Merged DatabaseValueMaps SHOULD be able to return a placeholder to
     * column map
     */
    public function testGetPlaceholderToColumnMapMerged()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        $merged->getPlaceholderToColumnMap();
    }

    public function testGetPlaceholderToValueMap()
    {
        $dvm = $this->getDatabaseValueMap();
        $dvm->addProperties($this->propertyToValueMapTwo);
        $map = $dvm->getPlaceholderToValueMap();
        $values = array_values($map);
        $this->assertEquals(['A', 'E'], $values);
    }

    /**
     * Merged DatabaseValueMaps SHOULD be able to return a placeholder to
     * value map
     */
    public function testGetPlaceholderToValueMapMerged()
    {
        $dvm_one = $this->getDatabaseValueMap();
        $dvm_one->addProperties($this->propertyToValueMapOne);

        $dvm_two = $this->getDatabaseValueMap();
        $dvm_two->addProperties($this->propertyToValueMapTwo);

        $merged = $dvm_one->merge($dvm_two);

        $merged->getPlaceholderToValueMap();
    }
} 