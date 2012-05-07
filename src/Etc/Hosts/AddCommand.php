<?php

namespace Etc\Hosts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('etc:hosts:add')
            ->setDescription('add an entry in your hosts file')
            ->addArgument('hostname', InputArgument::REQUIRED, "Hostname(s) seperated by spaces")
            ->addArgument('ip', InputArgument::OPTIONAL, "IP Address, example 127.0.0.1", '127.0.0.1')
            ->addOption('hosts-file', null, InputOption::VALUE_REQUIRED, 'Location of your hosts file', '/etc/hosts')
            ->setHelp("
Usage:

    st etc:hosts:add example.local

This will add the line '127.0.0.1 example.local' to your hosts file.

If you need to change the IP address, then use:

    st etc:hosts:add example.local 127.1.1.1

To change to location of your hosts file, use the --hosts-file option. Example:

    st etc:hosts:add example.local --hosts-file=\"/private/etc/hosts\"");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $ip = $input->getArgument('ip');
        $hostname = $input->getArgument('hostname');
        $hosts_file = $input->getOption('hosts-file');

        $process = new Process(sprintf('echo "%s %s" | sudo tee -a %s >/dev/null', $ip, $hostname, $hosts_file));
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
