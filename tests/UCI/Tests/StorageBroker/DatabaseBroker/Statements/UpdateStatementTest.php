<?php
/**
 * UpdateStatementTest.php
 * 12/3/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Statements\UpdateStatement;

class UpdateStatementTest extends StatementTestBase
{
    /**
     * @var UpdateStatement (mock)
     */
    protected $stmt;

    public function setUp()
    {
        parent::setUp();

        $this->stmt = new UpdateStatement();
    }

    public function testGetStatementTextSingleValue()
    {
        $this->values
            ->shouldReceive('getPlaceholderToColumnMap')
            ->andReturn([
                ':val_one' => 'col_one'
            ]);

        $this->stmt->setValues($this->values);
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'UPDATE my_table SET col_one=:val_one WHERE id=:id;',
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

        $this->stmt->setValues($this->values);
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'UPDATE my_table SET col_one=:val_one, col_two=:val_two, col_three=:val_three WHERE id=:id;',
            $this->stmt->getStatementText()
        );
    }

    /**
     * An exception should be thrown if getStatementText() is called without
     * having set values and constraints.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextNothingSetException()
    {
        $this->stmt->getStatementText();
    }

    /**
     * An exception should be thrown if getStatementText() is called without
     * having set values and constraints.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextNoValuesException()
    {
        $this->stmt->setValues($this->values);
        $this->stmt->getStatementText();
    }

    /**
     * An exception should be thrown if getStatementText() is called without
     * having set values and constraints.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextNoConstraintsException()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->stmt->getStatementText();
    }
} 