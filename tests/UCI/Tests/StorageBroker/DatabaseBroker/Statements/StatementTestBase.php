<?php
/**
 * StatementTestBase.php
 * 12/2/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use Mockery;

abstract class StatementTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $whereSql = 'id=:id';

    /**
     * @var string
     */
    protected $tableName = 'my_table';

    /**
     * @var string
     */
    protected $className = 'MyClass';

    /**
     * @var array
     */
    protected $valuesPlaceholderToValueMap = [':val_one' => '1'];

    /**
     * @var array
     */
    protected $constraintsPlaceholderToValueMap = [':id' => '123'];

    /**
     * @var array
     */
    protected $mergedPlaceholderToValueMap = [
        ':val_one' => '1',
        ':id' => '123'
    ];

    /**
     * @var DatabaseValueMap (mock)
     */
    protected $values;

    /**
     * @var DatabaseConstraintInterface (mock)
     */
    protected $constraints;

    /**
     * Sets up mock data
     */
    public function setUp()
    {
        // Set up a DatabaseValueMap for our constraints
        // Other stuff won't need access to this, so we can make it local
        $constraints_db_value_map = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            [
                'getTableName' => $this->tableName,
                'getClassName' => $this->className,
                'getPlaceholderToValueMap' => $this->constraintsPlaceholderToValueMap
            ]
        );

        // Likewise, nothing will need access to the full merged map
        $merged_db_value_map = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            [
                'getTableName' => $this->tableName,
                'getClassName' => $this->className,
                'getPlaceholderToValueMap' => $this->mergedPlaceholderToValueMap
            ]
        );

        $this->values = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            [
                'getTableName' => $this->tableName,
                'getClassName' => $this->className,
                'getPlaceholderToValueMap' => $this->valuesPlaceholderToValueMap
            ]
        );
        $this->values
            ->shouldReceive('isCompatible')
            ->with(\Mockery::anyOf($this->values, $constraints_db_value_map))
            ->andReturn(true);
        $this->values
            ->shouldReceive('isCompatible')
            ->with(Mockery::any())
            ->andReturn(false);
        $this->values
            ->shouldReceive('merge')
            ->with($constraints_db_value_map)
            ->andReturn($merged_db_value_map);

        $constraints_db_value_map
            ->shouldReceive('isCompatible')
            ->with(\Mockery::anyOf($this->values, $constraints_db_value_map))
            ->andReturn(true);
        $constraints_db_value_map
            ->shouldReceive('isCompatible')
            ->with(Mockery::any())
            ->andReturn(false);
        $constraints_db_value_map
            ->shouldReceive('merge')
            ->with($this->values)
            ->andReturn($merged_db_value_map);
        $this->constraints = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface',
            [
                'getSqlWithPlaceholders' => $this->whereSql,
                'getDatabaseValueMap' => $constraints_db_value_map
            ]
        );
    }

    public function tearDown()
    {
        Mockery::close();
    }
} 