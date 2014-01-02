<?php
/**
 * AbstractStatementTest.php
 * 11/26/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\Tests\StorageBroker\DatabaseBroker\Statements;

use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use Mockery;

class AbstractStatementTest extends StatementTestBase
{
    /**
     * @var string
     */
    protected $actionClause = 'ACTION';

    /**
     * @var string
     */
    protected $valuesClause = 'SET col_one=:val_one';

    /**
     * @var AbstractStatement (mock)
     */
    protected $stmt;

    public function setUp()
    {
        parent::setUp();

        $this->stmt = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement'
        )
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();
        $this->stmt
            ->shouldReceive('getActionClause')
            ->andReturn($this->actionClause);
        $this->stmt
            ->shouldReceive('getValuesClause')
            ->andReturn($this->valuesClause);
        $this->stmt
            ->shouldReceive('statementReady')
            ->andReturn(true);
    }

    public function getValuesTest()
    {
        $this->stmt->setValues($this->values);
        $this->assertSame($this->values, $this->stmt->getValues());
    }

    public function getConstraints()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->assertSame($this->constraints, $this->stmt->getConstraints());
    }

    public function getStatementTextFromValues()
    {
        $this->stmt->setValues($this->values);
        $this->assertEquals(
            'ACTION my_table SET col_one=:val_one;',
            $this->stmt->getStatementText()
        );
    }

    public function testGetStatementTextFromConstraints()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'ACTION my_table SET col_one=:val_one WHERE id=:id;',
            $this->stmt->getStatementText()
        );
    }

    public function getStatementTextFromBoth()
    {
        $this->stmt->setValues($this->values);
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            'ACTION my_table SET col_one=:val_one WHERE id=:id;',
            $this->stmt->getStatementText()
        );
    }

    /**
     * An exception should be thrown if the statement is not ready and
     * getStatementText() is called.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetStatementTextException()
    {
        $stmt = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement'
        )
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();
        $stmt
            ->shouldReceive('getActionClause')
            ->andReturn($this->actionClause);
        $stmt
            ->shouldReceive('getValuesClause')
            ->andReturn($this->valuesClause);
        $stmt
            ->shouldReceive('statementReady')
            ->andReturn(false);

        $stmt->getStatementText();
    }

    public function testGetPlaceholderToValueMapFromValues()
    {
        $this->stmt->setValues($this->values);
        $this->assertEquals(
            $this->valuesPlaceholderToValueMap,
            $this->stmt->getPlaceholderToValueMap()
        );
    }

    public function testGetPlaceholderToValueMapFromConstraints()
    {
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            $this->constraintsPlaceholderToValueMap,
            $this->stmt->getPlaceholderToValueMap()
        );
    }

    public function testGetPlaceholderToValueMapFromBoth()
    {
        $this->stmt->setValues($this->values);
        $this->stmt->setConstraints($this->constraints);
        $this->assertEquals(
            $this->mergedPlaceholderToValueMap,
            $this->stmt->getPlaceholderToValueMap()
        );
    }

    /**
     * An exception should be thrown if getPlaceholderToValueMap() is called
     * before the statement has all required data set.
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testGetPlaceholderToValueMapException()
    {
        $stmt = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement'
        )
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();
        $stmt
            ->shouldReceive('getActionClause')
            ->andReturn($this->actionClause);
        $stmt
            ->shouldReceive('getValuesClause')
            ->andReturn($this->valuesClause);
        $stmt
            ->shouldReceive('statementReady')
            ->andReturn(true);

        $stmt->getPlaceholderToValueMap();
    }

    /**
     * An exception should be thrown if the data and constrains aren't
     * compatible with eachother (based on the same DatabaseObjectProfile)
     *
     * @expectedException \UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException
     */
    public function testSetDataAndConstraintsException()
    {
        $my_data = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap',
            [
                'getTableName' => 'foo',
                'getClassName' => 'bar',
            ]
        );
        $my_data
            ->shouldReceive('isCompatible')
            ->with($my_data)
            ->andReturn(true);
        $my_data
            ->shouldReceive('isCompatible')
            ->with(Mockery::any())
            ->andReturn(false);

        $my_constraint = Mockery::mock(
            'UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface',
            [
                'getSqlWithPlaceholders' => 'foo=:bar',
                'getDatabaseValueMap' => $my_data
            ]
        );

        $this->stmt->setValues($my_data);
        $this->stmt->setConstraints($this->constraints);
    }
} 