#!/usr/bin/env php
<?php
require 'vendor/.composer/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

// Need to find all the files that are commands, prolly a better way to do this
$finder = new Finder();
$iterator = $finder
        ->files()
        ->name('*Command.php')
        ->in(__DIR__ . '/vendor')
        ->exclude('symfony');

$commands = array();
foreach ($iterator as $file) {
    // whatever, too lazy to fix auto loading
    require_once $file->getPathname();
    /* @var $file \Symfony\Component\Finder\SplFileInfo */
    $class = str_replace(".php","",$file->getRelativePathname());
    $class = str_replace("/", '\\', $class);
    $commands[] = new $class();
}

$console = new Application("Server Tools", "0.1.0");
$console->addCommands($commands);
$console->run();
