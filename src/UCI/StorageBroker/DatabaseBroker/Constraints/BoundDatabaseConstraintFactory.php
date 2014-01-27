<?php
/**
 * BoundDatabaseConstraintFactoryctory.php
 * 11/26/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Constraints;

use UCI\StorageBroker\BoundConstraintFactoryInterface;
use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;

/**
 * Class BoundDatabaseConstraintFactory
 *
 * Contains constructor methods for various Database Constraints.
 *
 * Having an instantiable factory class allows us to bind the factory
 * to a DatabaseValueMap and have all constraints constructed from
 * that factory be bound to the same map, and therefor to the same class.
 *
 * @package EEEApplyStorageBroker\DatabaseBroker\Constraints
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class BoundDatabaseConstraintFactory implements BoundConstraintFactoryInterface
{
    /**
     * A factory for producing DatabaseValueMaps
     *
     * @var callable
     */
    protected $dvmFactory;

    /**
     * The name of the class that all constraints built by this factory
     * should be bound to.
     *
     * @var string
     */
    protected $className;

    /**
     * Accepts class dependencies.
     *
     * The class name is a constructor parameter because it should be immutable.
     *
     * @param DatabaseValueMapFactory $dvm_factory
     * @param string $class_name
     */
    public function __construct(DatabaseValueMapFactory $dvm_factory, $class_name)
    {
        $this->dvmFactory = $dvm_factory;
        $this->className = $class_name;
    }

    /**
     * Matches any row. The rough equivalent of "fetch all".
     *
     * Combine with other constraints at your peril!
     *
     * @return mixed
     */
    public function any()
    {
        $dvm = $this->getDatabaseValueMap();
        return new Any($dvm);
    }

    /**
     * Returns a new Equals constraint
     *
     * @param $property
     * @param $value
     * @return Equals
     */
    public function equals($property, $value)
    {
        $dvm = $this->getDatabaseValueMap();
        return new Equals($dvm, $property, $value);
    }

    /**
     * A helper function that simply returns a new DatabaseValueMap bound to
     * the appropriate class.
     *
     * @return DatabaseValueMap
     */
    protected function getDatabaseValueMap()
    {
        return $this->dvmFactory->build($this->className);
    }
} 