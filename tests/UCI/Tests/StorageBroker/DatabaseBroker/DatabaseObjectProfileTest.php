<?php
/**
 * DatabaseObjectProfileTest.php
 * 11/19/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker;


use UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile;

class DatabaseObjectProfileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $className = 'MyNamespace\MyClass';

    /**
     * @var string
     */
    private $tableName = 'my_table';

    /**
     * @var array
     */
    private $propertyToColumnMap = [
        'propOne'   =>  'valOne',
        'propTwo'   =>  'valTwo',
        'propThree' =>  'valThree',
        'propFour'  =>  'valFour'
    ];

    /**
     * @var \UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile
     */
    private $dop;

    public function setUp()
    {
        $this->dop = new DatabaseObjectProfile();
        $this->dop->setClassName($this->className);
        $this->dop->setTableName($this->tableName);
        $this->dop->setPropertyToColumnMap($this->propertyToColumnMap);
    }

    public function testClassName()
    {
        $this->assertEquals($this->className, $this->dop->getClassName());
    }

    public function testTableName()
    {
        $this->assertEquals($this->tableName, $this->dop->getTableName());
    }

    public function testPropertyToColumnMap()
    {
        $this->assertEquals($this->propertyToColumnMap, $this->dop->getPropertyToColumnMap());
    }

    public function testGetColumnFromProperty()
    {
        foreach($this->propertyToColumnMap as $property => $column) {
            $this->assertEquals($column, $this->dop->getColumnFromProperty($property));
        }
    }

    /**
     * @expectedException UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetColumnFromPropertyException()
    {
        $this->dop->getColumnFromProperty('foo');
    }

    public function testGetPropertyFromColumn()
    {
        foreach($this->propertyToColumnMap as $property => $column) {
            $this->assertEquals($property, $this->dop->getPropertyFromColumn($column));
        }
    }

    /**
     * @expectedException UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetPropertyFromColumnException()
    {
        $this->dop->getPropertyFromColumn('foo');
    }
} 