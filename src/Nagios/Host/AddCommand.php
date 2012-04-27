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
            ->setDescription('Create a host definition');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      // Required
      do {
        $host_name = $this->getDialog()->ask($output, '<question>Host Name</question>: ', null);
      } while(!$host_name);
      do {
        $alias = $this->getDialog()->ask($output, '<question>Alias</question>: ', null);
      } while(!$alias);
      do {
        $address = $this->getDialog()->ask($output, '<question>Address</question>: ', null);
      } while(!$address);
      do {
        $max_check_attempts = $this->getDialog()->ask($output, '<question>Max Check Attempts</question> <comment>(default: 10)</comment>: ', 10);
      } while(!$max_check_attempts);
      do {
        $check_period = $this->getDialog()->ask($output, '<question>Check Period</question> <comment>(defualt: 24x7)</comment>: ', '24x7');
      } while(!$check_period);
      do {
        $contacts = $this->getDialog()->ask($output, '<question>Contacts</question>: ', null);
      } while(!$contacts);
      do {
        $contact_group = $this->getDialog()->ask($output, '<question>Contact Group</question> <comment>(default: admins)</comment>: ', 'admins');
      } while(!$contact_group);
      do {
        $notification_interval = $this->getDialog()->ask($output, '<question>Notification Interval</question> <comment>(default: 0)</comment>: ', '0');
      } while(!ctype_digit($notification_interval));
      do {
        $notification_period = $this->getDialog()->ask($output, '<question>Notification Period</question> <comment>(default: 24x7)</comment>: ', '24x7');
      } while(!$notification_period);

      // Optional
      $display_name = $this->getDialog()->ask($output, sprintf('<question>Display Name</question> <comment>(default: %s)</comment>: ',$host_name), $host_name);
      $parents = $this->getDialog()->ask($output, sprintf('<question>Parents</question> <comment>(default: null)</comment>: '), null);
      $hostgroups = $this->getDialog()->ask($output, sprintf('<question>Hostgroups</question> <comment>(default: null)</comment>: '), null);
      $check_command = $this->getDialog()->ask($output, sprintf('<question>Check Command</question> <comment>(default: check-host-alive)</comment>: '), 'check-host-alive');
      $initial_state = $this->getDialog()->ask($output, sprintf('<question>Initial State</question> <info>[o,d,u]</info> <comment>(default: null)</comment>: '), null);
      $check_interval = $this->getDialog()->ask($output, sprintf('<question>Check Interval</question> <comment>(default: null)</comment>: '), null);
      $retry_interval = $this->getDialog()->ask($output, sprintf('<question>Retry Interval</question> <comment>(default: null)</comment>: '), null);
      $active_checks_enabled = $this->getDialog()->ask($output, sprintf('<question>Active Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
      $passive_check_enabled = $this->getDialog()->ask($output, sprintf('<question>Passive Checks Enabled</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
      $obsess_over_host = $this->getDialog()->ask($output, sprintf('<question>Obsess Over Host</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '), '1');
      $check_freshness = $this->getDialog()->ask($output, sprintf('<question>Check Freshness</question> <info>[0/1]</info> <comment>(default: 1)</comment>: '),'1');
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
  contact_group                %contact_group%
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

      $hostDefinition = strtr($hostDefinitionTemplate,array(
        '%host_name%'                    => $host_name,
        '%alias%'                        => $alias,
        '%address%'                      => $address,
        '%max_check_attempts%'           => $max_check_attempts,
        '%check_period%'                 => $check_period,
        '%contacts%'                     => $contacts,
        '%contact_group%'                => $contact_group,
        '%notification_interval%'        => $notification_interval,
        '%notification_period%'          => $notification_period,
        '%display_name%'                 => $display_name,
        '%parents%'                      => $parents,
        '%hostgroups%'                   => $hostgroups,
        '%check_command%'                => $check_command,
        '%initial_state%'                => $initial_state,
        '%check_interval%'               => $check_interval,
        '%retry_interval%'               => $retry_interval,
        '%active_checks_enabled%'        => $active_checks_enabled,
        '%passive_checks_enabled%'       => $passive_check_enabled,
        '%obsess_over_host%'             => $obsess_over_host,
        '%check_freshness%'              => $check_freshness,
        '%freshness_threshold%'          => $freshness_threshold,
        '%event_handler%'                => $event_handler,
        '%event_handler_enabled%'        => $event_handler_enabled,
        '%low_flap_threshold%'           => $low_flap_threshold,
        '%high_flap_threshold%'          => $high_flap_threshold,
        '%flap_detection_enabled%'       => $flap_detection_enabled,
        '%flap_detection_options%'       => $flap_detection_options,
        '%process_pref_data%'            => $process_pref_data,
        '%retain_status_information%'    => $retain_status_information,
        '%retain_nonstatus_information%' => $retain_status_information,
        '%first_notification_delay%'     => $first_notification_delay,
        '%notification_options%'         => $notification_options,
        '%notifications_enabled%'        => $notifications_enabled,
        '%stalking_options%'             => $stalking_options,
        '%notes%'                        => $notes,
        '%notes_url%'                    => $notes_url,
        '%action_url%'                   => $action_url,
        '%icon_image%'                   => $icon_image,
        '%icon_image_alt%'               => $icon_image_alt,
        '%vrml_image%'                   => $vrml_image,
        '%statusmap_image%'              => $statusmap_image,
        '%2d_coords%'                    => $twod_coords,
        '%3d_coords%'                    => $threed_coords,
      ));

      $output->writeln($hostDefinition);
      if (!$this->getDialog()->askConfirmation($output,'<question>Is the information correct?</question> <comment>(deafult: yes)</comment>: ',true)){
        return(0);
      }

      $output->writeln('let us continue...');
    }

    /**
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}
