<?php
/**
 * DatabaseConstraintFactoryBinder.php/17/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;

/**
 * Class DatabaseConstraintFactoryBinder
 *
 * Creates BoundDatabaseConstraintFactories that produce Constraints that
 * are tied to a particular class.
 *
 * This class is actually a factory factory, but *shhh* don't tell anyone
 * and hopefully they won't notice...
 *
 * Usage:
 *   $constraint_factory =
 *       $database_constraint_factory_binder->getBound('MyNamespace\MyClass');
 *
 * @package UCI\StorageBroker\DatabaseBroker\Constraints
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseConstraintFactoryBinder
{
    /**
     * @var DatabaseValueMapFactory
     */
    protected $dvmFactory;

    /**
     * Accepts a DatabaseValueMapFactory that will be provided to all
     * BoundDatabaseConstraintFactories that it creates.
     *
     * @param DatabaseValueMapFactory $dvm_factory
     */
    public function __construct(DatabaseValueMapFactory $dvm_factory)
    {
        $this->dvmFactory = $dvm_factory;
    }

    /**
     * Returns a new BoundDatabaseConstraintFactory that will produce
     * Constraints that are bound to the class $class_name.
     *
     * @param string $class_name
     * @return BoundDatabaseConstraintFactory
     */
    public function getBound($class_name)
    {
        return new BoundDatabaseConstraintFactory($this->dvmFactory, $class_name);
    }
} 