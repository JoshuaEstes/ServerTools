<?php

namespace JoshuaEstes\Bundle\ServerToolsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    }

}
