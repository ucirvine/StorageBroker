<?php
/**
 * BoundConstraintFactoryInterfacerface.php
 * 11/26/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker;

/**
 * Interface BoundConstraintFactoryInterface
 *
 * Defines what builder methods a BoundConstraintFactory should have.
 *
 * All Constraints produced by the builder methods should be bound to the same
 * class. BoundConstraintFactoryInterface is probably best implemented as an
 * instantiated factory that has the class binding as a property.
 *
 * Constraints define relationships between class properties and values.
 * For example, suppose you have an User class and you would like to find
 * all users named Jane who created their account later than January 1st, 2013.
 * To built this joint constraints for an imaginary class using this interface,
 * you might do something like this:
 *
 * $cf = new ConstraintFactory('MyApplication\Models\User');
 * $constraints = $cf->and(
 *      $cf->equals('firstName', 'Jane'),
 *      $cf->greaterThan('creationDate', '2013-01-01')
 * );
 *
 * While the constraints will be evaluated in different ways on different platforms,
 * the basic types of comparison ("A is equal to B", "C is less than D", etc.)
 * should be the same. Hence this interface specifies the types of comparisons
 * supported. It's up to the StorageBroker implementation to determine how
 * each type of comparison is implemented for the target storage platform.
 *
 * @package UCI\StorageBroker
 */
interface BoundConstraintFactoryInterface
{
    /**
     * Matches any row. The rough equivalent of "fetch all".
     *
     * Combine with other constraints at your peril!
     *
     * @return mixed
     */
    public function any();

    /**
     * Specifies that a class' property should be equal to the provided value
     *
     * @param $property
     * @param $value
     * @return mixed
     */
    public function equals($property, $value);
} 