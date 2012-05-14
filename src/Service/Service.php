<?php

namespace Service;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Service
 * @version
 */
use Symfony\Component\Console\Output\ConsoleOutput;
use Service\ServiceInterface;

abstract class Service implements ServiceInterface
{

  /**
   * @var Symfony\Component\Console\Output\OutputInterface
   */
  protected $output;

  /**
   *
   * @param Symfony\Component\Console\Output\OutputInterface $output
   */
  public function __construct($output = null)
  {
    if (null === $output)
    {
      $this->output = new ConsoleOutput();
    }
    else
    {
      $this->output = $output;
    }
  }

}