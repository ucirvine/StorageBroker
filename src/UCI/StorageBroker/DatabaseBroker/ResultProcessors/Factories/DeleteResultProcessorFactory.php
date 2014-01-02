<?php
/**
 * DeleteResultProcessorFactory.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\DeleteResultProcessor;

/**
 * Class DeleteResultProcessorFactory
 *
 * Produces DeleteResultProcessor instances.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DeleteResultProcessorFactory
{
    /**
     * Returns a new DeleteResultProcessor instance.
     *
     * @return DeleteResultProcessor
     */
    public function build()
    {
        return new DeleteResultProcessor();
    }
} 