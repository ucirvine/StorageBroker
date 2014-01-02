<?php
/**
 * DatabaseBrokerServiceProvider.php
 * 12/13/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Pimple;
use UCI\StorageBroker\DatabaseBroker\DatabaseBroker;
use UCI\StorageBroker\DatabaseBroker\Constraints\DatabaseConstraintFactoryBinder;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseValueMapFactory;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseObjectProfileFactory;
use UCI\StorageBroker\DatabaseBroker\Factories\QueryFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\SelectResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\InsertResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\UpdateResultProcessorFactory;
use UCI\StorageBroker\DatabaseBroker\ResultProcessors\Factories\DeleteResultProcessorFactory;

/**
 * Class DatabaseBrokerServiceProvider
 *
 * A Silex-based service provider for the DatabaseBroker ORM.
 *
 * DatabaseBroker is an implementation of StorageBroker, which is used to
 * store, fetch, and modify objects stored in the database. It is specifically
 * intended to be used in cases where classes are mapped to single database
 * tables. (ie. It is *not* suitable where JOINs are required.)
 *
 * The DatabaseBrokerServiceProvider has a dependency on the
 * TypeConverterServiceProvider, which should also be registered with the app.
 *
 * Requirements:
 *   - All tables must have an auto-incremented primary key `id`.
 *   - All classes must have a TypeConverter conversions defined that convert
 *     to UCI\StorageBroker\DatabaseBroker\DatabaseValueMap and back.
 *
 * Configuration:
 *   $app['database_broker.pdo_config'] = [
 *      'dsn'       => '{DSN}',
 *      'username'  => '{username}',
 *      'password'  => '{password}'
 *   ]
 *
 *   $app['database_broker.table_config'] = [
 *       '{Fully-qualified class name}' => [
 *           'tableName' => '{table name}',
 *           'propertyToColumnMap' => [
 *               '{property name}' => '{column name}',
 *               ...
 *           ]
 *       ]
 *       ...
 *   ]
 *
 *   * Note that for the table configuration, the property name should align
 *     with the properties in a given class' DatabaseValueMap. When converting
 *     between the actual class and the DatabaseValueMap, you can change around
 *     the properties however you like. This allows you to omit or combine
 *     properties for the database representation.
 *
 * Usage:
 *   $constraint_factory_binder = $app['database_broker.constraint_factory_binder']
 *   $database_broker = $app['database_broker.broker']
 *
 *   $cf = $constraint_factory_binder->getBound('SomeNamespace\User');
 *   $constraints = $cf->equals('id', 123);
 *   $my_object = $database_broker->get($constraints)[0];
 *
 *   $my_object->setFirstName('Jim');
 *   $database_broker->save($my_object);
 *
 *   $constraints = $cf->equals('id', 456);
 *   $database_broker->delete($constraints);
 *
 * @package UCI\StorageBroker\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseBrokerServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['database_broker.pdo'] = $app->share(function($c) {
            try {
                $pdo = new \PDO(
                    $c['database_broker.pdo_config']['dsn'],
                    $c['database_broker.pdo_config']['username'],
                    $c['database_broker.pdo_config']['password']
                );
            }
            catch(\PDOException $pdoe) {
                throw new DatabaseBrokerException(
                    'Unable to connect to database: ' . $pdoe->getMessage()
                );
            }

            // Throw errors as exceptions
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;
        });

        $app['database_broker.dop_factory'] = $app->share(function($c) {
            return new DatabaseObjectProfileFactory($c['database_broker.table_config']);
        });

        $app['database_broker.database_value_map_factory'] = $app->share(function($c) {
            return new DatabaseValueMapFactory($c['database_broker.dop_factory']);
        });

        $app['database_broker.srp_factory'] = $app->share(function($c) {
            return new SelectResultProcessorFactory($c['database_broker.database_value_map_factory']);
        });

        $app['database_broker.irp_factory'] = $app->share(function($c) {
            return new InsertResultProcessorFactory();
        });

        $app['database_broker.urp_factory'] = $app->share(function($c) {
            return new UpdateResultProcessorFactory();
        });

        $app['database_broker.drp_factory'] = $app->share(function($c) {
            return new DeleteResultProcessorFactory();
        });

        $app['database_broker.query_factory'] = $app->share(function($c) {
            return new QueryFactory(
                $c['database_broker.pdo'],
                $c['database_broker.srp_factory'],
                $c['database_broker.irp_factory'],
                $c['database_broker.urp_factory'],
                $c['database_broker.drp_factory']
            );
        });

        $app['database_broker.constraint_factory_binder'] =
            $app->share(function($c) {
                return new DatabaseConstraintFactoryBinder($c['database_broker.database_value_map_factory']);
            });

        $app['database_broker.database_broker'] = $app->share(function($c) {
            return new DatabaseBroker(
                $c['database_broker.query_factory'],
                $c['database_broker.constraint_factory_binder'],
                $c['type_converter.converter']
            );
        });
    }

    public function boot(Application $app)
    {
        // *crickets*...
    }
} 