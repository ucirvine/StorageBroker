<?php
/**
 * SelectResultProcessorFactory.php
 * 12/16/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories;

use UCI\StorageBroker\DatabaseBroker\ResultProcessors\SelectResultProcessor;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;

/**
 * Class SelectResultProcessorFactory
 *
 * Produces SelectResultProcessor instances.
 *
 * @package UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class SelectResultProcessorFactory
{
    /**
     * @var DatabaseValueMapFactory
     */
    protected $dvmFactory;

    /**
     * Accepts dependencies.
     *
     * @param DatabaseValueMapFactory $dvm_factory
     */
    public function __construct(DatabaseValueMapFactory $dvm_factory)
    {
        $this->dvmFactory = $dvm_factory;
    }

    /**
     * Returns a new SelectResultProcessor instance.
     *
     * @return SelectResultProcessor
     */
    public function build()
    {
        return new SelectResultProcessor($this->dvmFactory);
    }
} 