<?php
/**
 * TestBootstrap.php
 * 1/2/14
 *
 * Sets up our autoloader
 *
 * @author: Jeremy Thacker <thackerj@uci.edu>
 */

$autoloader = require(__DIR__ . '/../vendor/autoload.php');
// Add the test namespace
$autoloader->add('UCI\\Tests\\StorageBroker', 'tests');