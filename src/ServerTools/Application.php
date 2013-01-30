<?php

namespace ServerTools;

/**
 * Description
 *
 * @author      Joshua Estes
 * @copyright
 * @package
 * @subpackage
 * @version
 */
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;

define('ST_ROOT_DIR', realpath(dirname(__FILE__) . '/../..'));
define('ST_DOCS_DIR', ST_ROOT_DIR . '/docs');
define('ST_SRC_DIR', ST_ROOT_DIR . '/src');
define('ST_VENDOR_DIR', ST_ROOT_DIR . '/vendor');

class Application extends BaseApplication
{

  const VERSION = '2.0.0';

  public function __construct()
  {
    parent::__construct('ServerTools', self::VERSION);
    $finder = new Finder();
    $iterator = $finder
      ->files()
      ->name('*Command.php')
      ->in(array(ST_SRC_DIR, ST_VENDOR_DIR))
      ->exclude('symfony');
    // Might be able to place the commands into a config
    // file to load them instead of searching through the
    // directories to find them
    foreach ($iterator as $file)
    {
      // whatever, too lazy to fix auto loading
      //require_once $file->getPathname();
      /* @var $file \Symfony\Component\Finder\SplFileInfo */
      $class = str_replace(".php", "", $file->getRelativePathname());
      $class = str_replace("/", '\\', $class);
      // Some systems the first $file in the iterator was returning
      // some funky result so make sure that the class exists before
      // we try to create it
      if (class_exists($class))
      {
        $this->add(new $class());
      }
    }

    $this->getHelperSet()->set(new \Helper\ApacheHelper(new \Service\Apache()));
  }

}
