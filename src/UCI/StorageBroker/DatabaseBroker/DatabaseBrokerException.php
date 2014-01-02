<?php
/**
 * DatabaseBrokerException.php
 * 11/19/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker;

use UCI\StorageBroker\StorageBrokerException;

/**
 * Class DatabaseBrokerException
 *
 * Used to throw Exceptions that are generated within any of the DatabaseBroker
 * classes. This allows us to catch ORM-related issues.
 *
 * @package UCI\StorageBroker\DatabaseBroker
 * @author Jeremy Thacker <thackerj@uci.edu>
 */
class DatabaseBrokerException extends StorageBrokerException
{
    // A pretty standard exception class. No customization needed.
} 