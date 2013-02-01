<?php

namespace JoshuaEstes\Bundle\ServerToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manage your hosts file.
 *
 * @author Joshua Estes
 */
class HostsCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('hosts')
            ->setDescription('Manage your /etc/hosts file')
            ->setDefinition(array(
                new InputOption('file', 'f', InputOption::VALUE_REQUIRED, 'Location of your hosts file', '/etc/hosts'),
                new InputOption('list', 'l', InputOption::VALUE_NONE, 'List all of the known hosts in your hosts file'),
                new InputOption('edit', 'E', InputOption::VALUE_NONE, 'Open up the hosts file in your editor'),
                new InputOption('add', 'a', InputOption::VALUE_NONE, 'Add an entry to your hosts file'),
                new InputOption('delete', 'D', InputOption::VALUE_NONE, 'Remove an entry in your hosts file'),
                new InputOption('enable', 'e', InputOption::VALUE_NONE, 'Enable a host'),
                new InputOption('disable', 'd', InputOption::VALUE_NONE, 'Disable a host'),
                new InputOption('ip', null, InputOption::VALUE_REQUIRED, 'IP Address', '127.0.0.1'),
                new InputArgument('host', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'hostname or list of hostnames'),
            ))
            ->setHelp(<<<EOF
EOF
            )
        ;
    }

    /**
     * Find the location of the hosts file based on the users
     * platform
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hostsFile = $input->getOption('file');

        /**
         * List entries in the hosts file
         */
        if ($input->getOption('list')) {
            $handle = fopen($hostsFile, 'r');
            while(false !== ($buffer = fgets($handle, 1024))) {
                $buffer = trim($buffer);
                if (empty($buffer)) { continue; }
                // split based on tabs, spaces
                $buffer    = preg_split('/\s/', $buffer);
                $ip        = $buffer[0];
                $enabled   = true;
                $hosts     = array();
                $hostsLine = '';
                if (preg_match('/^#/', $ip)) {
                    $enabled = false;
                    $ip      = preg_replace('/^#/', '', $ip);
                }

                for($i=1;$i<count($buffer);$i++) {
                    $hosts[] = $buffer[$i];
                    $hostsLine .= $buffer[$i] . ($i<count($buffer)-1 ? ', ' : '');
                }
                $output->writeln(array(
                    sprintf('IP Address: %s', $ip),
                    sprintf('Enabled:    %s', ($enabled ? 'Yes' : 'No')),
                    sprintf('Hosts:      %s', $hostsLine),
                    '',
                ));
            }
            fclose($handle);
        }

        /**
         * Add an entry in the hosts file
         */
        if ($input->getOption('add')) {
            // Make sure we have an ip address and at least one host
            if (!$input->getOption('ip') || count($input->getArgument('host')) <= 0) {
                throw new \Exception('Need both IP address and at least one host');
            }
            // check to see if we can write to the host file
            if (!is_writable($hostsFile)) {
                throw new \Exception(sprintf('Can not write to file "%s". Try `sudo !!`', $hostsFile));
            }
            $line = $input->getOption('ip') . "\t";
            foreach ($input->getArgument('host') as $v) {
                $line .= $v . "\t";
            }

            if ($input->isInteractive()) {
                $dialog = $this->getHelperSet()->get('dialog');
                $output->writeln(array(
                    $line,
                ));
                if (!$dialog->askConfirmation($output, 'Do you confirm? [Y/n]', true)) {
                    $output->writeln('Command Aborted');
                    return 1;
                }
            }

            $handler = fopen($hostsFile, 'a');
            fwrite($handler, trim($line) . "\n");
            fclose($handler);
        }
    }

    /**
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

}
