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
        // Required
        do {
            $host_name = $this->getDialog()->ask($output, sprintf('<question>Host Name</question> <comment>(default: %s)</comment>: ', $input->getOption('host_name')), $input->getOption('host_name'));
        }
        while (!$host_name);

        do {
            $alias = $this->getDialog()->ask($output, sprintf('<question>Alias</question> <comment>(default: %s)</comment>: ', $input->getOption('alias')), $input->getOption('alias'));
        }
        while (!$alias);

        do {
            $address = $this->getDialog()->ask($output, sprintf('<question>Address</question> <comment>(default: %s)</comment>: ', $input->getOption('address')), $input->getOption('address'));
        }
        while (!$address);

        do {
            $max_check_attempts = $this->getDialog()->ask($output, sprintf('<question>Max Check Attempts</question> <comment>(default: %s)</comment>: ', $input->getOption('max_check_attempts')), $input->getOption('max_check_attempts'));
        }
        while (!$max_check_attempts);

        do {
            $check_period = $this->getDialog()->ask($output, sprintf('<question>Check Period</question> <comment>(defualt: %s)</comment>: ', $input->getOption('check_period')), $input->getOption('check_period'));
        }
        while (!$check_period);

        do {
            $contacts = $this->getDialog()->ask($output, sprintf('<question>Contacts</question> <comment>(default: %s)</comment>: ', $input->getOption('contacts')), $input->getOption('contacts'));
        }
        while (!$contacts);

        do {
            $contact_groups = $this->getDialog()->ask($output, sprintf('<question>Contact Groups</question> <comment>(default: %s)</comment>: ', $input->getOption('contact_groups')), $input->getOption('contact_groups'));
        }
        while (!$contact_groups);

        do {
            $notification_interval = $this->getDialog()->ask($output, sprintf('<question>Notification Interval</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_interval')), $input->getOption('notification_interval'));
        }
        while (!is_numeric($notification_interval));

        do {
            $notification_period = $this->getDialog()->ask($output, sprintf('<question>Notification Period</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_period')), $input->getOption('notification_period'));
        }
        while (!$notification_period);

        // Optional
        $display_nameDefault = $input->getOption('display_name') ? $input->getOption('display_name') : $host_name;
        $display_name = $this->getDialog()->ask($output, sprintf('<question>Display Name</question> <comment>(default: %s)</comment>: ', $display_nameDefault), $display_nameDefault);
        $parents = $this->getDialog()->ask($output, sprintf('<question>Parents</question> <comment>(default: null)</comment>: '), null);
        $hostgroups = $this->getDialog()->ask($output, sprintf('<question>Hostgroups</question> <comment>(default: null)</comment>: '), null);
        $check_command = $this->getDialog()->ask($output, sprintf('<question>Check Command</question> <comment>(default: check-host-alive)</comment>: '), 'check-host-alive');
        $initial_state = $this->getDialog()->ask($output, sprintf('<question>Initial State</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null);
        $check_interval = $this->getDialog()->ask($output, sprintf('<question>Check Interval</question> <comment>(default: null)</comment>: '), null);
        $retry_interval = $this->getDialog()->ask($output, sprintf('<question>Retry Interval</question> <comment>(default: null)</comment>: '), null);
        $active_checks_enabled = $this->getDialog()->ask($output, sprintf('<question>Active Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $passive_check_enabled = $this->getDialog()->ask($output, sprintf('<question>Passive Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $obsess_over_host = $this->getDialog()->ask($output, sprintf('<question>Obsess Over Host</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $check_freshness = $this->getDialog()->ask($output, sprintf('<question>Check Freshness</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $freshness_threshold = $this->getDialog()->ask($output, sprintf('<question>Freshness Threshold</question> <comment>(default: null)</comment>: '), null);
        $event_handler = $this->getDialog()->ask($output, sprintf('<question>Event Handler</question> <comment>(default: null)</comment>: '), null);
        $event_handler_enabled = $this->getDialog()->ask($output, sprintf('<question>Event Handler Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $low_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>Low Flap Threshold</question> <comment>(default: null)</comment>: '), null);
        $high_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>High Flap Threshold</question> <comment>(default: null)</comment>: '), null);
        $flap_detection_enabled = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), null);
        $flap_detection_options = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Options</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null);
        $process_pref_data = $this->getDialog()->ask($output, sprintf('<question>Process Pref Data</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $retain_status_information = $this->getDialog()->ask($output, sprintf('<question>Retain Status Information</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $retain_nonstatus_information = $this->getDialog()->ask($output, sprintf('<question>Retain Nonstatus Information</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
        $first_notification_delay = $this->getDialog()->ask($output, sprintf('<question>First Notification Delay</question> <comment>(default: null)</comment>: '), null);
        $notification_options = $this->getDialog()->ask($output, sprintf('<question>Notification Options</question> <info>[d,u,r,f,s]</info> <comment>(default: d,u,r)</comment>: '), 'd,u,r');
        $notifications_enabled = $this->getDialog()->ask($output, sprintf('<question>Notifications Enabled</question> <info>[0/1]</info><comment>(default: 1)</comment>: '), '1');
        $stalking_options = $this->getDialog()->ask($output, sprintf('<question>Stalking Options</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null);
        $notes = $this->getDialog()->ask($output, sprintf('<question>Notes</question> <comment>(default: null)</comment>: '), null);
        $notes_url = $this->getDialog()->ask($output, sprintf('<question>Notes URL</question> <comment>(default: null)</comment>: '), null);
        $action_url = $this->getDialog()->ask($output, sprintf('<question>Action URL</question> <comment>(default: null)</comment>: '), null);
        $icon_image = $this->getDialog()->ask($output, sprintf('<question>Icon Image</question> <comment>(default: null)</comment>: '), null);
        $icon_image_alt = $this->getDialog()->ask($output, sprintf('<question>Icon Image Alt</question> <comment>(default: null)</comment>: '), null);
        $vrml_image = $this->getDialog()->ask($output, sprintf('<question>VRML Image</question> <comment>(default: null)</comment>: '), null);
        $statusmap_image = $this->getDialog()->ask($output, sprintf('<question>Statusmap Image</question> <comment>(default: null)</comment>: '), null);
        $twod_coords = $this->getDialog()->ask($output, sprintf('<question>2D Coords</question> <comment>(default: null)</comment>: '), null);
        $threed_coords = $this->getDialog()->ask($output, sprintf('<question>3D Coords</question> <comment>(default: null)</comment>: '), null);

        // Create the file
        $hostDefinitionTemplate = <<<EOF
define host{
  host_name                    %host_name%
  alias                        %alias%
  address                      %address%
  max_check_atempts            %max_check_attempts%
  check_period                 %check_period%
  contacts                     %contacts%
  contact_groups               %contact_groups%
  notification_interval        %notification_interval%
  notification_period          %notification_period%
  display_name                 %display_name%
  parents                      %parents%
  hostgroups                   %hostgroups%
  check_command                %check_command%
  initial_state                %initial_state%
  check_interval               %check_interval%
  retry_interval               %retry_interval%
  active_checks_enabled        %active_checks_enabled%
  passive_checks_enabled       %passive_checks_enabled%
  obsess_over_host             %obsess_over_host%
  check_fresness               %check_freshness%
  freshness_threshold          %freshness_threshold%
  event_handler                %event_handler%
  event_handler_enabled        %event_handler_enabled%
  low_flap_threshold           %low_flap_threshold%
  high_flap_threshold          %high_flap_threshold%
  flap_detection_enabled       %flap_detection_enabled%
  flap_detection_options       %flap_detection_options%
  process_pref_data            %process_pref_data%
  retain_status_information    %retain_status_information%
  retain_nonstatus_information %retain_nonstatus_information%
  first_notification_delay     %first_notification_delay%
  notification_options         %notification_options%
  notifications_enabled        %notifications_enabled%
  stalking_options             %stalking_options%
  notes                        %notes%
  notes_url                    %notes_url%
  action_url                   %action_url%
  icon_image                   %icon_image%
  icon_image_alt               %icon_image_alt%
  vrml_image                   %vrml_image%
  statusmap_image              %statusmap_image%
  2d_coords                    %2d_coords%
  3d_coords                    %3d_coords%
  }
EOF;

        $hostDefinition = strtr($hostDefinitionTemplate, array(
                '%host_name%' => $host_name,
                '%alias%' => $alias,
                '%address%' => $address,
                '%max_check_attempts%' => $max_check_attempts,
                '%check_period%' => $check_period,
                '%contacts%' => $contacts,
                '%contact_groups%' => $contact_groups,
                '%notification_interval%' => $notification_interval,
                '%notification_period%' => $notification_period,
                '%display_name%' => $display_name,
                '%parents%' => $parents,
                '%hostgroups%' => $hostgroups,
                '%check_command%' => $check_command,
                '%initial_state%' => $initial_state,
                '%check_interval%' => $check_interval,
                '%retry_interval%' => $retry_interval,
                '%active_checks_enabled%' => $active_checks_enabled,
                '%passive_checks_enabled%' => $passive_check_enabled,
                '%obsess_over_host%' => $obsess_over_host,
                '%check_freshness%' => $check_freshness,
                '%freshness_threshold%' => $freshness_threshold,
                '%event_handler%' => $event_handler,
                '%event_handler_enabled%' => $event_handler_enabled,
                '%low_flap_threshold%' => $low_flap_threshold,
                '%high_flap_threshold%' => $high_flap_threshold,
                '%flap_detection_enabled%' => $flap_detection_enabled,
                '%flap_detection_options%' => $flap_detection_options,
                '%process_pref_data%' => $process_pref_data,
                '%retain_status_information%' => $retain_status_information,
                '%retain_nonstatus_information%' => $retain_status_information,
                '%first_notification_delay%' => $first_notification_delay,
                '%notification_options%' => $notification_options,
                '%notifications_enabled%' => $notifications_enabled,
                '%stalking_options%' => $stalking_options,
                '%notes%' => $notes,
                '%notes_url%' => $notes_url,
                '%action_url%' => $action_url,
                '%icon_image%' => $icon_image,
                '%icon_image_alt%' => $icon_image_alt,
                '%vrml_image%' => $vrml_image,
                '%statusmap_image%' => $statusmap_image,
                '%2d_coords%' => $twod_coords,
                '%3d_coords%' => $threed_coords,
            ));

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
            if ($this->getDialog()->askConfirmation($outputput, '<question>Would you like to RESTART nagios</question> <comment>(deafult: yes)</comment>: ', true)) {
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
