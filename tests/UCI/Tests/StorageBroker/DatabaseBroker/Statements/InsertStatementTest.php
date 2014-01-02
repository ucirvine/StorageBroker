<?php
/**
 * InsertStatementTest.php
 * 12/2/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Statements\InsertStatement;

class InsertStatementTest extends StatementTestBase
{
    /**
     * @var InsertStatement (mock)
     */
    protected $stmt;

    public function setUp()
    {
        parent::setUp();

        $this->stmt = new InsertStatement();
    }

    public function testGetStatementTextSingleValue()
    {
        $this->values
            ->shouldReceive('getPlaceholderToColumnMap')
            ->andReturn([
                ':val_one' => 'col_one'
            ]);
        $this->values
            ->shouldReceive('hasProperty')
            ->with('id')
            ->andReturn(false);
        $this->values
            ->shouldReceive('hasProperty')
            ->with(\Mockery::any())
            ->andReturn(true);

        $this->stmt->setValues($this->values);
        $this->assertEquals(
            'INSERT INTO my_table (col_one) VALUES (:val_one);',
            $this->stmt->getStatementText()
        );
    }

    public function testGetStatementTextMultipleValues()
    {
        $this->values
            ->shouldReceive('getPlaceholderToColumnMap')
            ->andReturn([
                ':val_one' => 'col_one',
                ':val_two' => 'col_two',
                ':val_three' => 'col_three'
            ]);
        $this->values
            ->shouldReceive('hasProperty')
            ->with('id')
            ->andReturn(false);
        $this->values
            ->shouldReceive('hasProperty')
            ->with(\Mockery::any())
            ->andReturn(true);

        $this->stmt->setValues($this->values);
        $this->assertEquals(
            'INSERT INTO my_table (col_one, col_two, col_three) VALUES (:val_one, :val_two, :val_three);',
            $this->stmt->getStatementText()
        );
    }

    /**
     * An exception should be thrown if getStatementText() is called without
     * having set values.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextNoValuesException()
    {
        $this->stmt->getStatementText();
    }

    /**
     * An exception should be thrown if an ID is included in the values
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testStatementTextIdException()
    {
        $this->values
            ->shouldReceive('getPlaceholderToColumnMap')
            ->andReturn([
                ':id' => 'id',
                ':val_one' => 'col_one',
                ':val_two' => 'col_two',
                ':val_three' => 'col_three'
            ]);
        $this->values
            ->shouldReceive('hasProperty')
            ->with('id')
            ->andReturn(true);
        $this->values
            ->shouldReceive('hasProperty')
            ->with(\Mockery::any())
            ->andReturn(true);

        $this->stmt->setValues($this->values);
        $this->stmt->getStatementText();
    }

    /**
     * An exception should be thrown if you attempt to call setConstraints()
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testSetConstraintsException()
    {
        $this->stmt->setConstraints($this->constraints);
    }
} 