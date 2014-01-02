<?php
/**
 * DatabaseObjectProfileFactory.php
 * 12/9/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Factories;

use UCI\StorageBroker\DatabaseBroker\DatabaseObjectProfile;
use UCI\StorageBroker\DatabaseBroker\DatabaseBrokerException;

/**
 * Class DatabaseObjectProfileFactory
 *
 * Creates new DatabaseObjectProfile instances, given a class name to load
 * the database table configuration for.
 *
 * A factory class is needed because some classes need to be able to
 * produce DatabaseObjectProfile instances.
 *
 * @package UCI\StorageBroker\DatabaseBroker\Factories
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseObjectProfileFactory
{
    /**
     * An array of object table configurations.
     *
     * @var array
     */
    protected $tableConfig;

    /**
     * Accepts global object to table configuration.
     *
     * It should take the form:
     *      $table_config = [
     *          {Fully-qualified class name} => [
     *              'tableName' => {table name},
     *              'propertyToColumnMap' => [
     *                  {property} => {value},
     *                  ...
     *              ]
     *          ],
     *          ...
     *      ];
     *
     * @param array $table_config
     */
    public function __construct(array $table_config)
    {
        $this->tableConfig = $table_config;
    }

    /**
     * Returns a new DatabaseObjectProfile for the provided class name.
     *
     * @param $class_name
     * @return DatabaseObjectProfile
     * @throws DatabaseBrokerException If any configuration data is missing
     */
    public function build($class_name)
    {
        /**
         * Validate the config array for the class to be loaded
         */
        if(!array_key_exists($class_name, $this->tableConfig)) {
            throw new DatabaseBrokerException(
                "Database table configuration not found for class $class_name"
            );
        }

        $config = $this->tableConfig[$class_name];

        if(!array_key_exists('tableName', $config)) {
            throw new DatabaseBrokerException(
                "Database table configuration for class $class_name is " .
                "missing required field 'tableName'"
            );
        }
        if(!array_key_exists('propertyToColumnMap', $config)) {
            throw new DatabaseBrokerException(
                "Database table configuration for class $class_name is " .
                "missing required field 'propertyToColumnMap'"
            );
        }

        /**
         * Build the object
         */
        $dop = new DatabaseObjectProfile();
        $dop->setClassName($class_name);
        $dop->setTableName($config['tableName']);
        $dop->setPropertyToColumnMap($config['propertyToColumnMap']);
        return $dop;
    }
} 