<?php
/**
 * Query.php2/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintInterface;
use UCI\StorageBroker\DatabaseBroker\Statements\AbstractStatement;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\ResultProcessorInterface;
use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;

/**
 * Class Query
 *
 * Aggregates information and methods required for a database query, executes
 * the query, and returns the result.
 *
 * Query operates on DatabaseValueMaps and Constraints. Both of these types
 * contain all necessary information about the class and table being worked with.
 *
 * This class can be intuitively used in conjunction with its factory class,
 * QueryFactory, through chaining. (See QueryFactory docs)
 *
 * Not all methods are used for all query types. An exception will be thrown
 * if an incompatible setter is called. (For example calling values() for a
 * SELECT statement.)
 *
 * @see UCI\StorageBroker\DatabaseBroker\Factories\QueryFactory
 *
 * @package UCI\StorageBroker\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class Query
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var DatabaseValueMap
     */
    protected $values;

    /**
     * @var DatabaseConstraintInterface
     */
    protected $constraints;

    /**
     * @var AbstractStatement
     */
    protected $statement;

    /**
     * @var ResultProcessorInterface
     */
    protected $resultProcessor;

    /**
     * Accepts PDO as a dependency and an AbstractStatement and a
     * ResultProcessorInterface as immutable parameters.
     *
     * The AbstractStatement is used to produce a particular type of query
     * (SELECT, INSERT, UPDATE or DELETE). The ResultProcessorInterface
     * formats the query's response and *must* match up to the AbstractStatement
     * type. In practice, is enforced in QueryFactory, so use caution if you're
     * not using QueryFactory.
     *
     * @param $pdo
     * @param AbstractStatement $statement
     * @param ResultProcessorInterface $result_processor
     */
    public function __construct(
        $pdo,
        AbstractStatement $statement,
        ResultProcessorInterface $result_processor
    ) {
        $this->pdo = $pdo;
        $this->statement = $statement;
        $this->resultProcessor = $result_processor;
    }

    /**
     * Sets the values to be saved to the database.
     *
     * Returns $this for chaining
     *
     * @param DatabaseValueMap $values
     * @return $this
     * @throws DatabaseBrokerException if values are not needed for this query type
     */
    public function values(DatabaseValueMap $values)
    {
        // Keep a local copy of the values, then pass them along to the Statement
        $this->values = $values;
        $this->statement->setValues($this->values);

        return $this;
    }

    /**
     * Sets the constraints to be used in selecting particular rows in the database.
     * (Equivalent to the WHERE clause.)
     *
     * Returns $this for chaining.
     *
     * @param DatabaseConstraintInterface $constraints
     * @return $this
     * @throws DatabaseBrokerException if constraints are not needed for this query type
     */
    public function where(DatabaseConstraintInterface $constraints)
    {
        // Keep a local copy of the Constraints, then pass them along to the Statement
        $this->constraints = $constraints;
        $this->statement->setConstraints($this->constraints);

        return $this;
    }

    /**
     * Executes the query and returns the result. The result will vary with
     * the query type.
     *
     * Select - returns an array of DatabaseValueMaps
     * Insert - returns a DatabaseValueMap for the object, with an ID added
     * Update - returns a DatabaseValueMap matching the object that was updated
     * Delete - returns void, throws an exception if no rows are deleted
     *
     * @return mixed
     * @throws DatabaseBrokerException
     */
    public function run()
    {
        // Get the info that we will need for PDO
        $statement_text = $this->statement->getStatementText();
        $placeholder_to_value_map = $this->statement->getPlaceholderToValueMap();

        // Create and execute a PDO query
        $stmt = $this->pdo->prepare($statement_text);
        try {
            $stmt->execute($placeholder_to_value_map);
        }
        catch(\PDOException $pdoe) {
            throw new DatabaseBrokerException(
                'Error executing query: ' . $pdoe->getMessage()
            );
        }

        // Process the result, as directed by the ResultProcessor
        $return = $this->resultProcessor->process($this->pdo, $stmt, $this->statement);

        return $return;
    }
} 