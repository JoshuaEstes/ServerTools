<?php

namespace Nagios;

/**
 * Description
 *
 * @package
 * @subpackage
 * @author     Joshua Estes
 * @copyright  2012
 * @version    0.1.0
 * @category
 * @license
 *
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RestartCommand extends Command {

    protected function configure() {
        $this
            ->setName('nagios:restart')
            ->setDescription('Restart Nagios');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $process = new Process('sudo /etc/init.d/nagios restart');
        $process->run(function($type, $buffer) use($output) {
                $output->writeln($buffer);
            });
    }

}