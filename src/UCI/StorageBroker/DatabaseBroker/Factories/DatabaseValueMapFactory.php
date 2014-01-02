<?php
/**
 * DatabaseValueMapFactory.php
 * 12/9/13
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

namespace UCI\StorageBroker\DatabaseBroker\Factories;

use UCI\StorageBroker\DatabaseBroker\DatabaseValueMap;
use UCI\StorageBroker\DatabaseBroker\Factories\DatabaseObjectProfileFactory;

class DatabaseValueMapFactory
{
    /**
     * @var DatabaseObjectProfileFactory
     */
    protected $dbObjectProfileFactory;

    /**
     * Each DatabaseValueMap requires a unique string so that it has
     * unique placeholders. An integer that we increment for each instance
     * should fill this role well.
     *
     * @var int
     */
    protected static $nextUniqueNumber = 1;

    /**
     * Accepts dependencies.
     *
     * @param DatabaseObjectProfileFactory $dopf
     */
    public function __construct(DatabaseObjectProfileFactory $dopf)
    {
        $this->dbObjectProfileFactory = $dopf;
    }

    /**
     * Returns a new (empty) DatabaseValueMap bound to the class provided.
     *
     * The new map will have unique placeholder names from any other map.
     *
     * @param $class_name
     * @return DatabaseValueMap
     */
    public function build($class_name)
    {
        // Pull the unique number for our new instance, and increment the number
        // for the next one
        $unique_string = self::$nextUniqueNumber;
        self::$nextUniqueNumber++;

        // Fetch the DatabaseObjectProfile for the requested class
        $dop = $this->dbObjectProfileFactory->build($class_name);

        // Build it and return it
        return new DatabaseValueMap($this, $dop, $unique_string);
    }
} 