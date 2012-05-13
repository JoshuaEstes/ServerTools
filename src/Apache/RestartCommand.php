<?php

namespace Apache;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Apache
 * @version
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RestartCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('apache:restart')
      ->setDescription('Restart apache');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->getHelper('apache')->restart();
  }

}