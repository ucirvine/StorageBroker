<?php
/**
 * DeleteStatementTest.php
 * 12/3/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Statements\DeleteStatement;

class DeleteStatementTest extends StatementTestBase
{
    /**
     * @var DeleteStatement (mock)
     */
    protected $stmt;

    public function setUp()
    {
        parent::setUp();

        $this->stmt = new DeleteStatement();
    }

    public function testGetStatementText()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'DELETE FROM my_table WHERE id=:id;',
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