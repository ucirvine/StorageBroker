<?php
/**
 * QueryFactory.php13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Factories;

use UCI\StorageBroker\DatabaseBroker\Query;
use UCI\StorageBroker\DatabaseBroker\Statements\SelectStatement;
use UCI\StorageBroker\DatabaseBroker\Statements\InsertStatement;
use UCI\StorageBroker\DatabaseBroker\Statements\UpdateStatement;
use UCI\StorageBroker\DatabaseBroker\Statements\DeleteStatement;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\SelectResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\InsertResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\UpdateResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\DeleteResultProcessorFactory;
use PDO;

/**
 * Class QueryFactory
 *
 * Creates new Query objects of different types (SELECT, INSERT, UPDATE, DELETE)
 * by wiring together the appropriate Statement and ResultProcessor types.
 *
 * @package UCI\StorageBroker\DatabaseBroker\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class QueryFactory {
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var SelectResultProcessorFactory
     */
    protected $selectResultProcessorFactory;

    /**
     * @var InsertResultProcessorFactory
     */
    protected $insertResultProcessorFactory;

    /**
     * @var UpdateResultProcessorFactory
     */
    protected $updateResultProcessorFactory;

    /**
     * @var DeleteResultProcessorFactory
     */
    protected $deleteResultProcessorFactory;

    /**
     * Accepts dependencies.
     *
     * @param PDO $pdo
     * @param SelectResultProcessorFactory $srp_factory
     * @param InsertResultProcessorFactory $irp_factory
     * @param UpdateResultProcessorFactory $urp_factory
     * @param DeleteResultProcessorFactory $drp_factory
     */
    public function __construct(
        PDO $pdo,
        SelectResultProcessorFactory $srp_factory,
        InsertResultProcessorFactory $irp_factory,
        UpdateResultProcessorFactory $urp_factory,
        DeleteResultProcessorFactory $drp_factory
    ) {
        $this->pdo = $pdo;
        $this->selectResultProcessorFactory = $srp_factory;
        $this->insertResultProcessorFactory = $irp_factory;
        $this->updateResultProcessorFactory = $urp_factory;
        $this->deleteResultProcessorFactory = $drp_factory;
    }

    /**
     * Returns a new Query for running SELECT queries.
     *
     * @return Query
     */
    public function select()
    {
        $statement = new SelectStatement();
        $result_processor = $this->selectResultProcessorFactory->build();
        return new Query($this->pdo, $statement, $result_processor);
    }

    /**
     * Returns a new Query for running INSERT statements.
     *
     * @return Query
     */
    public function insert()
    {
        $statement = new InsertStatement();
        $result_processor = $this->insertResultProcessorFactory->build();
        return new Query($this->pdo, $statement, $result_processor);
    }

    /**
     * Returns a new Query for running UPDATE statements.
     *
     * @return Query
     */
    public function update()
    {
        $statement = new UpdateStatement();
        $result_processor = $this->updateResultProcessorFactory->build();
        return new Query($this->pdo, $statement, $result_processor);
    }

    /**
     * Returns a new query for running DELETE statements.
     *
     * @return Query
     */
    public function delete()
    {
        $statement = new DeleteStatement();
        $result_processor = $this->deleteResultProcessorFactory->build();
        return new Query($this->pdo, $statement, $result_processor);
    }
} 