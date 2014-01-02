<?php
/**
 * EqualsTest.php
 * 11/26/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\DatabaseBroker\Constraints\Equals;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use Mockery;

class EqualsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $placeholderToColumnMap = [':my_placeholder' => 'my_column'];

    /**
     * @var DatabaseValueMap (mock)
     */
    private $mockDatabaseValueMap;

    /**
     * @var Equals
     */
    private $equals;

    public function setUp()
    {
        $this->mockDatabaseValueMap = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            [
                'getPlaceholderToColumnMap' => $this->placeholderToColumnMap,
                'addProperty' => null
            ]
        );

        $this->equals = new Equals($this->mockDatabaseValueMap, 'MyProperty', 'foo');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testConstruct()
    {
        $property = 'MyProperty';
        $value = 'MyValue';

        $mock_db_value_map = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'
        );
        $mock_db_value_map
            ->shouldReceive('addProperty')
            ->with($property, $value)
            ->once();

        $equals = new Equals($mock_db_value_map, $property, $value);
    }

    public function testGetSqlWithPlaceholders()
    {
        $this->assertEquals(
            'my_column=:my_placeholder',
            $this->equals->getSqlWithPlaceholders()
        );
    }

    public function testGetDatabaseValueMap()
    {
        $this->assertSame(
            $this->mockDatabaseValueMap,
            $this->equals->getDatabaseValueMap()
        );
    }
} 