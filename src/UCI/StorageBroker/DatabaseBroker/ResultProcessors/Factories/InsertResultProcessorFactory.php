<?php
/**
 * InsertResultProcessorFactory.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\InsertResultProcessor;

/**
 * Class InsertResultProcessorFactory
 *
 * Produces InsertResultProcessor instances.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class InsertResultProcessorFactory
{
    /**
     * Returns a new InsertResultProcessor instance.
     *
     * @return InsertResultProcessor
     */
    public function build()
    {
        return new InsertResultProcessor();
    }
} 