<?php

namespace Apache;

/**
 * Description
 * 
 * @author      Joshua Estes
 * @copyright
 * @package
 * @subpackage
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

    /**
     * @todo Check and see if apachectl is installed, check /etc/init.d/apache2,
     *       and check other ways we can restart apache and not worry about the
     *       OS
     */
    if (PHP_OS == 'Linux' && \is_file('/etc/init.d/apache2'))
    {
      $process = new Process('/etc/init.d/apache2 restart');
    }
    else
    {
      $p = new Process('which apachectl');
      $p->run();
      if (\strlen($p->getOutput()) > 0)
      {
        $process = new Process('$(which apachectl) -k restart');
      }
    }

    if (isset($process))
    {
      $process->run(function($type, $buffer) use($output)
        {
          $output->writeln($buffer);
        });
    }
    else
    {
      $output->writeln('<error>I have no idea how to restart your apache =(</error>');
    }
  }

}