<?php
/**
 * AnyTest.php
 * 1/27/14
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\DatabaseBroker\Constraints\Any;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use Mockery;

class AnyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DatabaseValueMap (mock)
     */
    private $mockDatabaseValueMap;

    /**
     * @var Any
     */
    private $any;

    public function setUp()
    {
        $this->mockDatabaseValueMap = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'
        );

        $this->any = new Any($this->mockDatabaseValueMap);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testGetSqlWithPlaceholders()
    {
        $this->assertEquals('1', $this->any->getSqlWithPlaceholders());
    }

    public function testGetDatabaseValueMap()
    {
        $this->assertSame(
            $this->mockDatabaseValueMap,
            $this->any->getDatabaseValueMap()
        );
    }
} 