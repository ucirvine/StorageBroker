<?php
/**
 * DatabaseBroker.php
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\StorageBrokerInterface;
use UCI\StorageBroker\DatabaseBroker\Factories\QueryFactory;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintFactoryBinder;
use UCI\TypeConverter\TypeConverter;

/**
 * Class DatabaseBroker
 *
 * DatabaseBroker is an implementation of StorageBroker, which is used to
 * store, fetch, and modify objects stored in the database. It is specifically
 * intended to be used in cases where classes are mapped to single database
 * tables. (ie. It is *not* suitable where JOINs are required.)
 *
 * Requirements:
 *   - All tables must have an auto-incremented primary key `id`.
 *   - All classes must have a TypeConverter conversions defined that convert
 *     to UCI\StorageBroker\DatabaseBroker\DatabaseValueMap and back.
 *
 * Examples:
 *   ==GET==
 *   * Fetch an array all Users created before July 4th, 2013
 *
 *   $cf = $boundConstraintFactoryFactory->build('MyApplication\Models\User')
 *   $constraints = $cf->lessThan('created', '2013-07-4');
 *   $users = $databaseBroker->get($constraints);
 *
 *   ==SAVE==
 *   * Save a new User
 *
 *   assert($user->getId() == null);
 *   $user = $databaseBroker->save($user);
 *   $user_id = $user->getId();
 *
 *   * Update an existing user
 *
 *   assert($user->getId() != null);
 *   $user = $databaseBroker->save($user);
 *
 *   ==DELETE==
 *   * Delete a user
 *
 *   $databaseBroker->delete($user);
 *
 * @package UCI\StorageBroker\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseBroker implements StorageBrokerInterface
{
    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var DatabaseConstraintFactoryBinder
     */
    protected $constraintFactoryBinder;

    /**
     * @var TypeConverter
     */
    protected $typeConverter;

    /**
     * Accepts class dependencies.
     *
     * @param QueryFactory $query_factory
     * @param DatabaseConstraintFactoryBinder $constraint_factory_binder
     * @param TypeConverter $type_converter
     */
    public function __construct(
        QueryFactory $query_factory,
        DatabaseConstraintFactoryBinder $constraint_factory_binder,
        TypeConverter $type_converter
    ) {
        $this->queryFactory = $query_factory;
        $this->constraintFactoryBinder = $constraint_factory_binder;
        $this->typeConverter = $type_converter;
    }

    /**
     * Returns an array of objects that match the provided constraints.
     *
     * If no matches are found, an empty array is returned.
     *
     * @param $constraints
     * @return array
     */
    public function get($constraints)
    {
        // Run a select query, which will return an array of DatabaseValueMaps
        // (or an empty array if nothing is found)
        $dvms = $this->queryFactory
            ->select()
            ->where($constraints)
            ->run();

        // Convert our array of DatabaseValueMaps into an array of class instances
        $type_converter = $this->typeConverter;
        $convert = function($dvm) use ($type_converter) {
            return $type_converter->convert($dvm, $dvm->getClassName());
        };
        $a = array_map($convert, $dvms);

        return $a;
    }

    /**
     * Accepts an object and saves it to its corresponding database table.
     *
     * If the object has an ID defined (in its corresponding DatabaseValueMap),
     * then an update is performed. Otherwise, an insert is preformed.
     *
     * A post-save copy of the object is returned. In the case of an insert, it
     * will now contain the object's ID.
     *
     * @param $object
     * @return mixed
     */
    public function save($object)
    {
        // convert our object into a DatabaseValueMap
        $dvm_in = $this->typeConverter
            ->convert(
                $object,
                'UCI\StorageBroker\DatabaseBroker\DatabaseValueMap'
            );

        // See whether there's an ID to determine whether this is an
        // insert or an update
        if($dvm_in->hasProperty('id')) {
            $dvm_out = $this->update($dvm_in);
        } else {
            $dvm_out = $this->insert($dvm_in);
        }

        $obj = $this->typeConverter->convert($dvm_out, $dvm_out->getClassName());
        return $obj;
    }

    /**
     * Deletes the entity (or entities) from the database that match the provided
     * constraints.
     *
     * Throws a DatabaseBrokerException if no matching rows are found.
     *
     * @param $constraints
     * @return void
     * @throws DatabaseBrokerException
     */
    public function delete($constraints)
    {
        $success = $this->queryFactory
            ->delete()
            ->where($constraints)
            ->run();

        if(!$success) {
            throw new DatabaseBrokerException(
                "Item(s) could not be deleted. No matching elements found."
            );
        }
    }

    /**
     * Inserts the provided DatabaseValueMap into the database. Returns a copy
     * of the DatabaseValueMap that has had an ID added.
     *
     * @param DatabaseValueMap $dvm
     * @return DatabaseValueMap
     */
    protected function insert(DatabaseValueMap $dvm)
    {
        // Run the insert query. The query will return a new DatabaseValueMap
        // for the element that now includes an ID
        $dvm = $this->queryFactory
            ->insert()
            ->values($dvm)
            ->run();

        return $dvm;
    }

    /**
     * Updates the provided DatabaseValueMap in the database. Returns the same
     * DatabaseValueMap.
     *
     * @param DatabaseValueMap $dvm
     * @return DatabaseValueMap
     */
    protected function update(DatabaseValueMap $dvm)
    {
        // Get a constraint that binds our update to the row with the matching ID
        $map = $dvm->getPropertyToValueMap();
        $constraints = $this->constraintFactoryBinder
            ->getBound($dvm->getClassName())
            ->equals('id', $map['id']);

        $dvm = $this->queryFactory
            ->update()
            ->values($dvm)
            ->where($constraints)
            ->run();

        return $dvm;
    }
} 