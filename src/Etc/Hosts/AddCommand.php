<?php

namespace Etc\Hosts;

/**
 * Description
 *
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Etc
 * @version
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;

class AddCommand extends Command
{

  protected function configure()
  {
    $this
      ->setName('etc:hosts:add')
      ->setDescription('add an entry in your hosts file')
      ->addOption('hostname', null, InputOption::VALUE_REQUIRED, "Hostname")
      ->addOption('ip', null, InputOption::VALUE_REQUIRED, "IP Address, example 127.0.0.1", '127.0.0.1')
      ->addOption('hosts-file', null, InputOption::VALUE_REQUIRED, 'Location of your hosts file', '/etc/hosts')
    ;
  }

  /**
   * Check and make sure that we can write to the hosts file
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function initialize(InputInterface $input, OutputInterface $output)
  {
    if (!\is_file($input->getOption('hosts-file')))
    {
      throw new \LogicException(sprintf('Could not find the hosts file located at "%s"', $input->getOption('hosts-file')));
    }
  }

  /**
   * Only runs in interactive mode
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function interact(InputInterface $input, OutputInterface $output)
  {
    do
    {
      $hostname = $this->getDialog()->ask($output, \sprintf('<question>Hostname</question> (default: %s): ', $input->getOption('hostname')), $input->getOption('hostname'));
    }
    while (!$hostname);
    $input->setOption('hostname', $hostname);

    do
    {
      $ip = $this->getDialog()->ask($output, \sprintf('<question>IP</question> (default: %s): ', $input->getOption('ip')), $input->getOption('ip'));
    }
    while (!$ip);
    $input->setOption('ip', $ip);
  }

  /**
   * Write contents to hosts file
   *
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /**
     * Make sure the hostname is set
     */
    if (null === $input->getOption('hostname'))
    {
      throw new \LogicException('You must either run this command interactive or pass the hostname option.');
    }

    $process = new Process(sprintf('echo "%s %s" | sudo tee --append %s', $input->getOption('ip'), $input->getOption('hostname'), $input->getOption('hosts-file')));
    $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
  }

  /**
   *
   * @return Symfony\Component\Console\Helper\DialogHelper
   */
  protected function getDialog()
  {
    return $this->getHelperSet()->get('dialog');
  }

}
