#!/usr/bin/env php
<?php

// Make sure that script will continue to run
set_time_limit(0);

// Let's get our footing so we know where we are at
define('ST_ROOT_DIR', dirname(__FILE__));
define('ST_DOCS_DIR', ST_ROOT_DIR . '/docs');
define('ST_SRC_DIR', ST_ROOT_DIR . '/src');
define('ST_VENDOR_DIR', ST_ROOT_DIR . '/vendor');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

// Need to find all the files that are commands, prolly a better way to do this
$finder = new Finder();
$iterator = $finder
        ->files()
        ->name('*Command.php')
        ->in(array(ST_SRC_DIR, ST_VENDOR_DIR))
        ->exclude('symfony');

// Might be able to place the commands into a config
// file to load them instead of searching through the
// directories to find them
$commands = array();
foreach ($iterator as $file) {
    // whatever, too lazy to fix auto loading
    //require_once $file->getPathname();
    /* @var $file \Symfony\Component\Finder\SplFileInfo */
    $class = str_replace(".php","",$file->getRelativePathname());
    $class = str_replace("/", '\\', $class);
    // Some systems the first $file in the iterator was returning
    // some funky result so make sure that the class exists before
    // we try to create it
    if (class_exists($class)){
      $commands[] = new $class();
    }
}

$console = new Application("Server Tools", "0.1.0");
$console->getHelperSet()->set(new Helper\ApacheHelper(new Service\Apache()));
$console->addCommands($commands);
$console->run();
