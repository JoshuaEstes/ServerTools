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
        ;
    }

}
