<?php

namespace Nagios\HostGroup;

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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AddCommand extends Command {

    protected function configure() {

        $this
            ->setName('nagios:hostgroup:add')
            ->setDescription('Create a host definition')
            ->addOption('hostgroup-cfg-path', null, InputOption::VALUE_REQUIRED, 'Path to location where you want to store the hosts files', '/usr/local/nagios/etc/objects/hostgroup')
            ->addOption('hostgroup_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a short name used to identify the host group.')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'This directive is used to define is a longer name or description used to identify the host group. It is provided in order to allow you to more easily identify a particular host group.')
            ->addOption('members', null, InputOption::VALUE_REQUIRED, 'This is a list of the short names of hosts that should be included in this group. Multiple host names should be separated by commas. This directive may be used as an alternative to (or in addition to) the hostgroups directive in host definitions.')
            ->addOption('hostgroup_members', null, InputOption::VALUE_REQUIRED, 'This optional directive can be used to include hosts from other "sub" host groups in this host group. Specify a comma-delimited list of short names of other host groups whose members should be included in this group.')
            ->addOption('notes', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional string of notes pertaining to the host. If you specify a note here, you will see the it in the extended information CGI (when you are viewing information about the specified host).')
            ->addOption('notes_url', null, InputOption::VALUE_REQUIRED, 'This variable is used to define an optional URL that can be used to provide more information about the host group. If you specify an URL, you will see a red folder icon in the CGIs (when you are viewing hostgroup information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/). This can be very useful if you want to make detailed information on the host group, emergency contact methods, etc. available to other support staff.')
            ->addOption('action_url', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional URL that can be used to provide more actions to be performed on the host group. If you specify an URL, you will see a red "splat" icon in the CGIs (when you are viewing hostgroup information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/).')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cfg_file = array('define hostgroup {');

        // Required
        do {
            $hostgroup_name = $this->getDialog()->ask($output, \sprintf('<question>Hostgroup Name</question> <comment>(default: %s)</comment>: ', $input->getOption('hostgroup_name')), $input->getOption('hostgroup_name'));
        }
        while (!$hostgroup_name);
        $cfg_file[] = sprintf('hostgroup_name %s', $hostgroup_name);
        do {
            $alias = $this->getDialog()->ask($output, \sprintf('<question>Alias</question> <comment>(default: %s)</comment>: ', $input->getOption('alias')), $input->getOption('alias'));
        }
        while (!$alias);
        $cfg_file[] = sprintf('alias %s', $alias);

        // Optional
        if ($members = $this->getDialog()->ask($output, \sprintf('<question>Members</question> <comment>(default: %s)</comment>: ', $input->getOption('members')), $input->getOption('members'))) {
            $cfg_file[] = sprintf('members %s', $members);
        }
        if ($hostgroup_members = $this->getDialog()->ask($output, \sprintf('<question>Hostgroup Members</question> <comment>(default: %s)</comment>: ', $input->getOption('hostgroup_members')), $input->getOption('hostgroup_members'))) {
            $cfg_file[] = sprintf('hostgroup_members %s', $hostgroup_members);
        }
        if ($notes = $this->getDialog()->ask($output, \sprintf('<question>notes</question> <comment>(default: %s)</comment>: ', $input->getOption('notes')), $input->getOption('notes'))) {
            $cfg_file[] = sprintf('notes %s', $notes);
        }
        if ($notes_url = $this->getDialog()->ask($output, \sprintf('<question>notes_url</question> <comment>(default: %s)</comment>: ', $input->getOption('notes_url')), $input->getOption('notes_url'))) {
            $cfg_file[] = sprintf('notes_url %s', $notes_url);
        }
        if ($action_url = $this->getDialog()->ask($output, \sprintf('<question>action_url</question> <comment>(default: %s)</comment>: ', $input->getOption('action_url')), $input->getOption('action_url'))) {
            $cfg_file[] = sprintf('action_url %s', $action_url);
        }

        $cfg_file[] = '}';
        // Create the file
        $hostgroupDefinition = \implode("\n", $cfg_file);
        $output->writeln($hostgroupDefinition);
        if (!$this->getDialog()->askConfirmation($output, '<question>Is the information correct?</question> <comment>(deafult: yes)</comment>: ', true)) {
            return(0);
        }


        /**
         * Place file where it needs to go
         */
        $file = $input->getOption('hostgroup-cfg-path') . '/' . $hostgroup_name . '.cfg';
        if ($this->getDialog()->askConfirmation($output, sprintf('<question>Would you like to write to file "%s"</question> <comment>(deafult: yes)</comment>: ', $file), true)) {
            $tmpFile = '/tmp/' . $hostgroup_name . '.cfg';
            \file_put_contents($tmpFile, $hostDefinition);
            $process = new Process(sprintf('sudo cp %s %s', $tmpFile, $file));
            $process->run(function($type, $buffer) use($output) {
                    $output->writeln($buffer);
                }
            );

            /**
             * Restart nagios since we copied this file to the directory it belongs
             * in
             */
            if ($this->getDialog()->askConfirmation($output, '<question>Would you like to RESTART nagios</question> <comment>(deafult: yes)</comment>: ', true)) {
                $command = $this->getApplication()->find('nagios:restart');
                $returnCode = $command->run(new ArrayInput(array('command' => 'nagios:restart')), $output);
            }
        }
    }

    /**
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}