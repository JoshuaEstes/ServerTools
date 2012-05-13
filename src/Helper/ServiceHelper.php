<?php

namespace Helper;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package
 * @subpackage
 * @version
 */
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Helper;

class ServiceHelper extends Helper
{

  public function getName()
  {
    return 'service';
  }

}