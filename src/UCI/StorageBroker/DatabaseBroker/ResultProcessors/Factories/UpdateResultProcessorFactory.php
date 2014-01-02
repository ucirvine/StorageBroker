<?php
/**
 * UpdateResultProcessorFactory.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\UpdateResultProcessor;

/**
 * Class UpdateResultProcessorFactory
 *
 * Produces UpdateResultProcessor instances.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class UpdateResultProcessorFactory
{
    /**
     * Returns a new UpdateResultProcessor instance.
     *
     * @return UpdateResultProcessor
     */
    public function build()
    {
        return new UpdateResultProcessor();
    }
} 