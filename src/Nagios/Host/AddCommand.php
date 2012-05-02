<?php

namespace Nagios\Host;

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
            ->setName('nagios:host:add')
            ->setDescription('Create a host definition')
            ->addOption('hosts-cfg-path', null, InputOption::VALUE_REQUIRED, 'Path to location where you want to store the hosts files', '/usr/local/nagios/etc/objects/hosts')
            ->addOption('host_name', null, InputOption::VALUE_REQUIRED, 'host_name')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'alias')
            ->addOption('display_name', null, InputOption::VALUE_REQUIRED, 'display_name')
            ->addOption('address', null, InputOption::VALUE_REQUIRED, 'address')
            ->addOption('parents', null, InputOption::VALUE_REQUIRED, 'host_names')
            ->addOption('hostgroups', null, InputOption::VALUE_REQUIRED, 'hostgroup_names')
            ->addOption('check_command', null, InputOption::VALUE_REQUIRED, 'command_name', 'check-host-alive')
            ->addOption('initial_state', null, InputOption::VALUE_REQUIRED, '[o,d,u]')
            ->addOption('max_check_attempts', null, InputOption::VALUE_REQUIRED, '#', 10)
            ->addOption('check_interval', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('retry_interval', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('active_checks_enabled', null, InputOption::VALUE_REQUIRED, '[0/1]')
            ->addOption('passive_checks_enabled', null, InputOption::VALUE_REQUIRED, '[0/1]')
            ->addOption('check_period', null, InputOption::VALUE_REQUIRED, 'timeperiod_name', '24x7')
            ->addOption('obsess_over_host', null, InputOption::VALUE_REQUIRED, '[0/1]')
            ->addOption('check_freshness', null, InputOption::VALUE_REQUIRED, '[0/1]')
            ->addOption('freshness_threshold', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('event_handler', null, InputOption::VALUE_REQUIRED, 'command_name')
            ->addOption('event_handler_enabled', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('low_flap_threshold', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('high_flap_threshhold', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('flap_detection_enabled', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('flap_detections_options', null, InputOption::VALUE_REQUIRED, '[o,d,u]')
            ->addOption('process_pref_data', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('retain_status_information', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('retain_nonstatus_information', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('contacts', null, InputOption::VALUE_REQUIRED, 'contacts')
            ->addOption('contact_groups', null, InputOption::VALUE_REQUIRED, 'contact_groups', 'admins')
            ->addOption('notification_interval', null, InputOption::VALUE_REQUIRED, '#', 0)
            ->addOption('first_notification_delay', null, InputOption::VALUE_REQUIRED, '#')
            ->addOption('notification_period', null, InputOption::VALUE_REQUIRED, 'timeperiod_name', '24x7')
            ->addOption('notification_options', null, InputOption::VALUE_REQUIRED, '[d,u,r,f,s]', 'd,u,r')
            ->addOption('notifications_enabled', null, InputOption::VALUE_REQUIRED, '[0/1]', 1)
            ->addOption('stalking_options', null, InputOption::VALUE_REQUIRED, '[o,d,u]')
            ->addOption('notes', null, InputOption::VALUE_REQUIRED, 'note_string')
            ->addOption('notes_url', null, InputOption::VALUE_REQUIRED, 'url')
            ->addOption('action_url', null, InputOption::VALUE_REQUIRED, 'url')
            ->addOption('icon_image', null, InputOption::VALUE_REQUIRED, 'image_file')
            ->addOption('icon_image_alt', null, InputOption::VALUE_REQUIRED, 'alt_string')
            ->addOption('vrml_image', null, InputOption::VALUE_REQUIRED, 'image_file')
            ->addOption('statusmap_image', null, InputOption::VALUE_REQUIRED, 'image_file')
            ->addOption('2d_coords', null, InputOption::VALUE_REQUIRED, 'x_coord,y_coord')
            ->addOption('3d_coords', null, InputOption::VALUE_REQUIRED, 'x_coord,y_coord.z+coord')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cfg_file = array('define host{');
        // Required
        do {
            $host_name = $this->getDialog()->ask($output, sprintf('<question>Host Name</question> <comment>(default: %s)</comment>: ', $input->getOption('host_name')), $input->getOption('host_name'));
        }
        while (!$host_name);
        $cfg_file[] = sprintf('host_name %s', $host_name);

        do {
            $alias = $this->getDialog()->ask($output, sprintf('<question>Alias</question> <comment>(default: %s)</comment>: ', $input->getOption('alias')), $input->getOption('alias'));
        }
        while (!$alias);
        $cfg_file[] = sprintf('alias %s', $alias);

        do {
            $address = $this->getDialog()->ask($output, sprintf('<question>Address</question> <comment>(default: %s)</comment>: ', $input->getOption('address')), $input->getOption('address'));
        }
        while (!$address);
        $cfg_file[] = sprintf('address %s', $address);

        do {
            $max_check_attempts = $this->getDialog()->ask($output, sprintf('<question>Max Check Attempts</question> <comment>(default: %s)</comment>: ', $input->getOption('max_check_attempts')), $input->getOption('max_check_attempts'));
        }
        while (!$max_check_attempts);
        $cfg_file[] = sprintf('max_check_attempts %s', $max_check_attempts);

        do {
            $check_period = $this->getDialog()->ask($output, sprintf('<question>Check Period</question> <comment>(defualt: %s)</comment>: ', $input->getOption('check_period')), $input->getOption('check_period'));
        }
        while (!$check_period);
        $cfg_file[] = sprintf('check_period %s', $check_period);

        do {
            $contacts = $this->getDialog()->ask($output, sprintf('<question>Contacts</question> <comment>(default: %s)</comment>: ', $input->getOption('contacts')), $input->getOption('contacts'));
        }
        while (!$contacts);
        $cfg_file[] = sprintf('contacts %s', $contacts);

        do {
            $contact_groups = $this->getDialog()->ask($output, sprintf('<question>Contact Groups</question> <comment>(default: %s)</comment>: ', $input->getOption('contact_groups')), $input->getOption('contact_groups'));
        }
        while (!$contact_groups);
        $cfg_file[] = sprintf('contact_groups %s', $contact_groups);

        do {
            $notification_interval = $this->getDialog()->ask($output, sprintf('<question>Notification Interval</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_interval')), $input->getOption('notification_interval'));
        }
        while (!is_numeric($notification_interval));
        $cfg_file[] = sprintf('notification_interval %s', $notification_interval);

        do {
            $notification_period = $this->getDialog()->ask($output, sprintf('<question>Notification Period</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_period')), $input->getOption('notification_period'));
        }
        while (!$notification_period);
        $cfg_file[] = sprintf('notification_period %s', $notification_period);

        // Optional
        $display_nameDefault = $input->getOption('display_name') ? $input->getOption('display_name') : $host_name;
        if ($display_name = $this->getDialog()->ask($output, sprintf('<question>Display Name</question> <comment>(default: %s)</comment>: ', $display_nameDefault), $display_nameDefault)) {
            $cfg_file[] = sprintf('display_name %s', $display_name);
        }
        if ($parents = $this->getDialog()->ask($output, sprintf('<question>Parents</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('parents %s', $parents);
        }
        if ($hostgroups = $this->getDialog()->ask($output, sprintf('<question>Hostgroups</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('hostgroups %s', $hostgroups);
        }
        if ($check_command = $this->getDialog()->ask($output, sprintf('<question>Check Command</question> <comment>(default: check-host-alive)</comment>: '), 'check-host-alive')) {
            $cfg_file[] = sprintf('check_command %s', $check_command);
        }
        if ($initial_state = $this->getDialog()->ask($output, sprintf('<question>Initial State</question> <info>[o,d,u]</info> <comment>(default: o)</comment>: '), 'o')) {
            $cfg_file[] = sprintf('initial_state %s', $initial_state);
        }
        if ($check_interval = $this->getDialog()->ask($output, sprintf('<question>Check Interval</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('check_interval %s', $check_interval);
        }
        if ($retry_interval = $this->getDialog()->ask($output, sprintf('<question>Retry Interval</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('retry_interval %s', $retry_interval);
        }
        if ($active_checks_enabled = $this->getDialog()->ask($output, sprintf('<question>Active Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('active_checks_enabled %s', $active_checks_enabled);
        }
        if ($passive_check_enabled = $this->getDialog()->ask($output, sprintf('<question>Passive Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('passive_check_enabled %s', $passive_check_enabled);
        }
        if ($obsess_over_host = $this->getDialog()->ask($output, sprintf('<question>Obsess Over Host</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('obsess_over_host %s', $obsess_over_host);
        }
        if ($check_freshness = $this->getDialog()->ask($output, sprintf('<question>Check Freshness</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('check_freshness %s', $check_freshness);
        }
        if ($freshness_threshold = $this->getDialog()->ask($output, sprintf('<question>Freshness Threshold</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('freshness_threshold %s', $freshness_threshold);
        }
        if ($event_handler = $this->getDialog()->ask($output, sprintf('<question>Event Handler</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('event_handler %s', $event_handler);
        }
        if ($event_handler_enabled = $this->getDialog()->ask($output, sprintf('<question>Event Handler Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('event_handler_enabled %s', $event_handler_enabled);
        }
        if ($low_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>Low Flap Threshold</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('low_flap_threshold %s', $low_flap_threshold);
        }
        if ($high_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>High Flap Threshold</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('high_flap_threshold %s', $high_flap_threshold);
        }
        if ($flap_detection_enabled = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), null)) {
            $cfg_file[] = sprintf('flap_detection_enabled %s', $flap_detection_enabled);
        }
        if ($flap_detection_options = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Options</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('flap_detection_options %s', $flap_detection_options);
        }
        if ($process_perf_data = $this->getDialog()->ask($output, sprintf('<question>Process Pref Data</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('process_perf_data %s', $process_perf_data);
        }
        if ($retain_status_information = $this->getDialog()->ask($output, sprintf('<question>Retain Status Information</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('retain_status_information %s', $retain_status_information);
        }
        if ($retain_nonstatus_information = $this->getDialog()->ask($output, sprintf('<question>Retain Nonstatus Information</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('retain_nonstatus_information %s', $retain_nonstatus_information);
        }
        if ($first_notification_delay = $this->getDialog()->ask($output, sprintf('<question>First Notification Delay</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('first_notification_delay %s', $first_notification_delay);
        }
        if ($notification_options = $this->getDialog()->ask($output, sprintf('<question>Notification Options</question> <info>[d,u,r,f,s]</info> <comment>(default: d,u,r)</comment>: '), 'd,u,r')) {
            $cfg_file[] = sprintf('notification_options %s', $notification_options);
        }
        if ($notifications_enabled = $this->getDialog()->ask($output, sprintf('<question>Notifications Enabled</question> <info>[0/1]</info><comment>(default: 1)</comment>: '), '1')) {
            $cfg_file[] = sprintf('notifications_enabled %s', $notifications_enabled);
        }
        if ($stalking_options = $this->getDialog()->ask($output, sprintf('<question>Stalking Options</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('stalking_options %s', $stalking_options);
        }
        if ($notes = $this->getDialog()->ask($output, sprintf('<question>Notes</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('notes %s', $notes);
        }
        if ($notes_url = $this->getDialog()->ask($output, sprintf('<question>Notes URL</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('notes_url %s', $notes_url);
        }
        if ($action_url = $this->getDialog()->ask($output, sprintf('<question>Action URL</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('action_url %s', $action_url);
        }
        if ($icon_image = $this->getDialog()->ask($output, sprintf('<question>Icon Image</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('icon_image %s', $icon_image);
        }
        if ($icon_image_alt = $this->getDialog()->ask($output, sprintf('<question>Icon Image Alt</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('icon_image_alt %s', $icon_image_alt);
        }
        if ($vrml_image = $this->getDialog()->ask($output, sprintf('<question>VRML Image</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('vrml_image %s', $vrml_image);
        }
        if ($statusmap_image = $this->getDialog()->ask($output, sprintf('<question>Statusmap Image</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('statusmap_image %s', $statusmap_image);
        }
        if ($twod_coords = $this->getDialog()->ask($output, sprintf('<question>2D Coords</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('twod_coords %s', $twod_coords);
        }
        if ($threed_coords = $this->getDialog()->ask($output, sprintf('<question>3D Coords</question> <comment>(default: null)</comment>: '), null)) {
            $cfg_file[] = sprintf('threed_coords %s', $threed_coords);
        }

        $cfg_file[] = '}';

        // Create the file
        $hostDefinition = \implode("\n", $cfg_file);

        $output->writeln($hostDefinition);
        if (!$this->getDialog()->askConfirmation($output, '<question>Is the information correct?</question> <comment>(deafult: yes)</comment>: ', true)) {
            return(0);
        }

        /**
         * Place file where it needs to go
         */
        $file = $input->getOption('hosts-cfg-path') . '/' . $host_name . '.cfg';
        if ($this->getDialog()->askConfirmation($output, sprintf('<question>Would you like to write to file "%s"</question> <comment>(deafult: yes)</comment>: ', $file), true)) {
            $tmpFile = '/tmp/' . $host_name . '.cfg';
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
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}
