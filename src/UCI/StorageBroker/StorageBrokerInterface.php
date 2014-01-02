<?php
/**
 * StorageBrokerInterface.php
 * 11/18/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker;

/**
 * Interface StorageBrokerInterface
 *
 * StorageBroker is a high-level interface for saving, retrieving, and deleting
 * objects from persistent storage. Having an interface allows the idea of
 * a persistent storage "broker" to be platform agnostic. By implementing different
 * types of StorageBrokers, it is possible to change out the underlying storage
 * engine for an application.
 *
 * @package UCI\StorageBroker
 */
interface StorageBrokerInterface
{
    /**
     * Returns an array of objects from storage.
     *
     * The provided constraints must be bound to a specific class. The returned
     * array will contain instances of that class.
     *
     * If no objects matching the constraints are found, an empty array is returned.
     *
     * @param $constraints
     * @return array
     */
    public function get($constraints);

    /**
     * Saves the provided object and returns a copy of it.
     *
     * If the element's state changed while being saved (for example, a new
     * element might obtain an ID) the returned instance will reflect
     * those changes.
     *
     * @param mixed $object
     * @return mixed
     */
    public function save($object);

    /**
     * Deletes one or more elements that match the provided constraints.
     *
     * If no items are deleted (presumably because the constraints had no
     * matches) a StorageBrokerException is thrown.
     *
     * @param $constraints
     * @return mixed
     * @throws \UCI\StorageBroker\StorageBrokerException
     */
    public function delete($constraints);
} 