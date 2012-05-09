<?php

namespace Plesk\Database;

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

if (!defined('PLESK_BIN')) {
    define('PLESK_BIN', '/usr/local/psa/bin');
}

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('plesk:database:add')
            ->setDescription('Create a new database');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cmd = array();

        /**
         * Required parameters that need to be filled in by user
         */
        do {
            $database_name = $this->getDialog()->ask($output, '<question>Database Name </question>: ');
        }
        while (!$database_name);
        $cmd[] = sprintf('--create "%s"', $database_name);

        do {
            $domain = $this->getDialog()->ask($output, '<question>Domain (ie: beta.example.com)</question>: ');
        }
        while (!$domain);
        $cmd[] = sprintf('-domain "%s"', $domain);

        do {
            $user = $this->getDialog()->ask($output, '<question>Database User</question>: ');
        }
        while (!$user);
        $cmd[] = sprintf('-add_user "%s"', $user);

        do {
            $passwd = $this->getDialog()->ask($output, '<question>Database User Password</question>: ');
        }
        while (!$passwd);
        $cmd[] = sprintf('-passwd "%s"', $passwd);


        /**
         * Optional stuff
         */
        $type = $this->getDialog()->ask($output, '<question>Type (default: mysql)</question>: ', 'mysql');
        $cmd[] = sprintf('-type "%s"', $type);

        $command = \implode(" ", $cmd);
        $process = new Process(sprintf('%s/database %s', \PLESK_BIN, $command));
        $process->run(function($type, $buffer) use($output) {
                $output->writeln($buffer);
            }
        );
    }

    /**
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}
