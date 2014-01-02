<?php
/**
 * SelectStatementTest.php
 * 12/2/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Statements\SelectStatement;

class SelectStatementTest extends StatementTestBase
{
    /**
     * @var SelectStatement (mock)
     */
    protected $stmt;

    public function setUp()
    {
        parent::setUp();

        $this->stmt = new SelectStatement();
    }

    public function testGetStatementText()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'SELECT * FROM my_table WHERE id=:id;',
            $this->stmt->getStatementText()
        );
    }

    /**
     * An exception should be thrown if constraints haven't been set before
     * calling getStatementText()
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextNoConstraintsException()
    {
        $this->stmt->getStatementText();
    }

    /**
     * An exception should be thrown if you attempt to call setValues()
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testSetValuesException()
    {
        $this->stmt->setValues($this->values);
    }
}