<?php

namespace Helper;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Service
 * @version
 */
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Helper;
use Service\ServiceInterface;

class ApacheHelper extends Helper
{

  protected $service;

  public function __construct(ServiceInterface $service)
  {
    $this->service = $service;
  }

  public function __call($method, $arguments)
  {
    return \call_user_method($method, $this->service, $arguments);
  }

  public function getName()
  {
    return 'apache';
  }

}