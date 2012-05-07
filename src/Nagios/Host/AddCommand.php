<?php

namespace Nagios\Host;

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
            ->setName('nagios:host:add')
            ->setDescription('A host definition is used to define a physical server, workstation, device, etc. that resides on your network.')
            ->addOption('hosts-cfg-path', null, InputOption::VALUE_REQUIRED, 'Path to location where you want to store the hosts files', '/usr/local/nagios/etc/objects/hosts')
            ->addOption('host_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a short name used to identify the host. It is used in host group and service definitions to reference this particular host. Hosts can have multiple services (which are monitored) associated with them. When used properly, the $HOSTNAME$ macro will contain this short name.')
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a longer name or description used to identify the host. It is provided in order to allow you to more easily identify a particular host. When used properly, the $HOSTALIAS$ macro will contain this alias/description.')
            ->addOption('display_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an alternate name that should be displayed in the web interface for this host. If not specified, this defaults to the value you specify for the host_name directive. Note: The current CGIs do not use this option, although future versions of the web interface will.')
            ->addOption('address', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the address of the host. Normally, this is an IP address, although it could really be anything you want (so long as it can be used to check the status of the host). You can use a FQDN to identify the host instead of an IP address, but if DNS services are not available this could cause problems. When used properly, the $HOSTADDRESS$ macro will contain this address. Note: If you do not specify an address directive in a host definition, the name of the host will be used as its address. A word of caution about doing this, however - if DNS fails, most of your service checks will fail because the plugins will be unable to resolve the host name.')
            ->addOption('parents', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a comma-delimited list of short names of the "parent" hosts for this particular host. Parent hosts are typically routers, switches, firewalls, etc. that lie between the monitoring host and a remote hosts. A router, switch, etc. which is closest to the remote host is considered to be that host\'s "parent". Read the "Determining Status and Reachability of Network Hosts" document located here for more information. If this host is on the same network segment as the host doing the monitoring (without any intermediate routers, etc.) the host is considered to be on the local network and will not have a parent host. Leave this value blank if the host does not have a parent host (i.e. it is on the same segment as the Nagios host). The order in which you specify parent hosts has no effect on how things are monitored.')
            ->addOption('hostgroups', null, InputOption::VALUE_REQUIRED, 'This directive is used to identify the short name(s) of the hostgroup(s) that the host belongs to. Multiple hostgroups should be separated by commas. This directive may be used as an alternative to (or in addition to) using the members directive in hostgroup definitions.')
            ->addOption('check_command', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the command that should be used to check if the host is up or down. Typically, this command would try and ping the host to see if it is "alive". The command must return a status of OK (0) or Nagios will assume the host is down. If you leave this argument blank, the host will not be actively checked. Thus, Nagios will likely always assume the host is up (it may show up as being in a "PENDING" state in the web interface). This is useful if you are monitoring printers or other devices that are frequently turned off. The maximum amount of time that the notification command can run is controlled by the host_check_timeout option.', 'check-host-alive')
            ->addOption('initial_state', null, InputOption::VALUE_REQUIRED, 'By default Nagios will assume that all hosts are in UP states when it starts. You can override the initial state for a host by using this directive. Valid options are: o = UP, d = DOWN, and u = UNREACHABLE.', 'o')
            ->addOption('max_check_attempts', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of times that Nagios will retry the host check command if it returns any state other than an OK state. Setting this value to 1 will cause Nagios to generate an alert without retrying the host check. Note: If you do not want to check the status of the host, you must still set this to a minimum value of 1. To bypass the host check, just leave the check_command option blank.', 10)
            ->addOption('check_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" between regularly scheduled checks of the host. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. More information on this value can be found in the check scheduling documentation.')
            ->addOption('retry_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before scheduling a re-check of the hosts. Hosts are rescheduled at the retry interval when they have changed to a non-UP state. Once the host has been retried max_check_attempts times without a change in its status, it will revert to being scheduled at its "normal" rate as defined by the check_interval value. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. More information on this value can be found in the check scheduling documentation.')
            ->addOption('active_checks_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not active checks (either regularly scheduled or on-demand) of this host are enabled. Values: 0 = disable active host checks, 1 = enable active host checks (default).', 1)
            ->addOption('passive_checks_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not passive checks are enabled for this host. Values: 0 = disable passive host checks, 1 = enable passive host checks (default).', 1)
            ->addOption('check_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which active checks of this host can be made.', '24x7')
            ->addOption('obsess_over_host', null, InputOption::VALUE_REQUIRED, 'This directive determines whether or not checks for the host will be "obsessed" over using the ochp_command.', 1)
            ->addOption('check_freshness', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not freshness checks are enabled for this host. Values: 0 = disable freshness checks, 1 = enable freshness checks (default).', 1)
            ->addOption('freshness_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the freshness threshold (in seconds) for this host. If you set this directive to a value of 0, Nagios will determine a freshness threshold to use automatically.')
            ->addOption('event_handler', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the command that should be run whenever a change in the state of the host is detected (i.e. whenever it goes down or recovers). Read the documentation on event handlers for a more detailed explanation of how to write scripts for handling events. The maximum amount of time that the event handler command can run is controlled by the event_handler_timeout option.')
            ->addOption('event_handler_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the event handler for this host is enabled. Values: 0 = disable host event handler, 1 = enable host event handler.', 1)
            ->addOption('low_flap_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the low state change threshold used in flap detection for this host. More information on flap detection can be found here. If you set this directive to a value of 0, the program-wide value specified by the low_host_flap_threshold directive will be used.')
            ->addOption('high_flap_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the high state change threshold used in flap detection for this host. More information on flap detection can be found here. If you set this directive to a value of 0, the program-wide value specified by the high_host_flap_threshold directive will be used.')
            ->addOption('flap_detection_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not flap detection is enabled for this host. More information on flap detection can be found here. Values: 0 = disable host flap detection, 1 = enable host flap detection.', 1)
            ->addOption('flap_detection_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine what host states the flap detection logic will use for this host. Valid options are a combination of one or more of the following: o = UP states, d = DOWN states, u = UNREACHABLE states.', 'o,d,u')
            ->addOption('process_pref_data', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the processing of performance data is enabled for this host. Values: 0 = disable performance data processing, 1 = enable performance data processing.', 1)
            ->addOption('retain_status_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not status-related information about the host is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable status information retention, 1 = enable status information retention.', 1)
            ->addOption('retain_nonstatus_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not non-status information about the host is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable non-status information retention, 1 = enable non-status information retention.', 1)
            ->addOption('contacts', null, InputOption::VALUE_REQUIRED, 'This is a list of the short names of the contacts that should be notified whenever there are problems (or recoveries) with this host. Multiple contacts should be separated by commas. Useful if you want notifications to go to just a few people and don\'t want to configure contact groups. You must specify at least one contact or contact group in each host definition.')
            ->addOption('contact_groups', null, InputOption::VALUE_REQUIRED, 'This is a list of the short names of the contact groups that should be notified whenever there are problems (or recoveries) with this host. Multiple contact groups should be separated by commas. You must specify at least one contact or contact group in each host definition.', 'admins')
            ->addOption('notification_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before re-notifying a contact that this service is still down or unreachable. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. If you set this value to 0, Nagios will not re-notify contacts about problems for this host - only one problem notification will be sent out.', 0)
            ->addOption('first_notification_delay', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before sending out the first problem notification when this host enters a non-UP state. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. If you set this value to 0, Nagios will start sending out notifications immediately.')
            ->addOption('notification_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which notifications of events for this host can be sent out to contacts. If a host goes down, becomes unreachable, or recoveries during a time which is not covered by the time period, no notifications will be sent out.', '24x7')
            ->addOption('notification_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine when notifications for the host should be sent out. Valid options are a combination of one or more of the following: d = send notifications on a DOWN state, u = send notifications on an UNREACHABLE state, r = send notifications on recoveries (OK state), f = send notifications when the host starts and stops flapping, and s = send notifications when scheduled downtime starts and ends. If you specify n (none) as an option, no host notifications will be sent out. If you do not specify any notification options, Nagios will assume that you want notifications to be sent out for all possible states. Example: If you specify d,r in this field, notifications will only be sent out when the host goes DOWN and when it recovers from a DOWN state.', 'd,u,r')
            ->addOption('notifications_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not notifications for this host are enabled. Values: 0 = disable host notifications, 1 = enable host notifications.', 1)
            ->addOption('stalking_options', null, InputOption::VALUE_REQUIRED, 'This directive determines which host states "stalking" is enabled for. Valid options are a combination of one or more of the following: o = stalk on UP states, d = stalk on DOWN states, and u = stalk on UNREACHABLE states. More information on state stalking can be found here.')
            ->addOption('notes', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional string of notes pertaining to the host. If you specify a note here, you will see the it in the extended information CGI (when you are viewing information about the specified host).')
            ->addOption('notes_url', null, InputOption::VALUE_REQUIRED, 'This variable is used to define an optional URL that can be used to provide more information about the host. If you specify an URL, you will see a red folder icon in the CGIs (when you are viewing host information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/). This can be very useful if you want to make detailed information on the host, emergency contact methods, etc. available to other support staff.')
            ->addOption('action_url', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional URL that can be used to provide more actions to be performed on the host. If you specify an URL, you will see a red "splat" icon in the CGIs (when you are viewing host information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/).')
            ->addOption('icon_image', null, InputOption::VALUE_REQUIRED, 'This variable is used to define the name of a GIF, PNG, or JPG image that should be associated with this host. This image will be displayed in the various places in the CGIs. The image will look best if it is 40x40 pixels in size. Images for hosts are assumed to be in the logos/ subdirectory in your HTML images directory (i.e. /usr/local/nagios/share/images/logos).')
            ->addOption('icon_image_alt', null, InputOption::VALUE_REQUIRED, 'This variable is used to define an optional string that is used in the ALT tag of the image specified by the <icon_image> argument.')
            ->addOption('vrml_image', null, InputOption::VALUE_REQUIRED, 'This variable is used to define the name of a GIF, PNG, or JPG image that should be associated with this host. This image will be used as the texture map for the specified host in the statuswrl CGI. Unlike the image you use for the <icon_image> variable, this one should probably not have any transparency. If it does, the host object will look a bit wierd. Images for hosts are assumed to be in the logos/ subdirectory in your HTML images directory (i.e. /usr/local/nagios/share/images/logos).')
            ->addOption('statusmap_image', null, InputOption::VALUE_REQUIRED, 'This variable is used to define the name of an image that should be associated with this host in the statusmap CGI. You can specify a JPEG, PNG, and GIF image if you want, although I would strongly suggest using a GD2 format image, as other image formats will result in a lot of wasted CPU time when the statusmap image is generated. GD2 images can be created from PNG images by using the pngtogd2 utility supplied with Thomas Boutell\'s gd library. The GD2 images should be created in uncompressed format in order to minimize CPU load when the statusmap CGI is generating the network map image. The image will look best if it is 40x40 pixels in size. You can leave these option blank if you are not using the statusmap CGI. Images for hosts are assumed to be in the logos/ subdirectory in your HTML images directory (i.e. /usr/local/nagios/share/images/logos).')
            ->addOption('2d_coords', null, InputOption::VALUE_REQUIRED, 'This variable is used to define coordinates to use when drawing the host in the statusmap CGI. Coordinates should be given in positive integers, as they correspond to physical pixels in the generated image. The origin for drawing (0,0) is in the upper left hand corner of the image and extends in the positive x direction (to the right) along the top of the image and in the positive y direction (down) along the left hand side of the image. For reference, the size of the icons drawn is usually about 40x40 pixels (text takes a little extra space). The coordinates you specify here are for the upper left hand corner of the host icon that is drawn. Note: Don\'t worry about what the maximum x and y coordinates that you can use are. The CGI will automatically calculate the maximum dimensions of the image it creates based on the largest x and y coordinates you specify.')
            ->addOption('3d_coords', null, InputOption::VALUE_REQUIRED, 'This variable is used to define coordinates to use when drawing the host in the statuswrl CGI. Coordinates can be positive or negative real numbers. The origin for drawing is (0.0,0.0,0.0). For reference, the size of the host cubes drawn is 0.5 units on each side (text takes a little more space). The coordinates you specify here are used as the center of the host cube.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cfg_file = array('define host{');
        // Required
        do {
            $host_name = $this->getDialog()->ask($output, \sprintf('<question>Host Name</question> <comment>(default: %s)</comment>: ', $input->getOption('host_name')), $input->getOption('host_name'));
        }
        while (!$host_name);
        $cfg_file[] = \sprintf('host_name %s', $host_name);

        do {
            $alias = $this->getDialog()->ask($output, \sprintf('<question>Alias</question> <comment>(default: %s)</comment>: ', $input->getOption('alias')), $input->getOption('alias'));
        }
        while (!$alias);
        $cfg_file[] = \sprintf('alias %s', $alias);

        do {
            $address = $this->getDialog()->ask($output, \sprintf('<question>Address</question> <comment>(default: %s)</comment>: ', $input->getOption('address')), $input->getOption('address'));
        }
        while (!$address);
        $cfg_file[] = \sprintf('address %s', $address);

        do {
            $max_check_attempts = $this->getDialog()->ask($output, \sprintf('<question>Max Check Attempts</question> <comment>(default: %s)</comment>: ', $input->getOption('max_check_attempts')), $input->getOption('max_check_attempts'));
        }
        while (!$max_check_attempts);
        $cfg_file[] = \sprintf('max_check_attempts %s', $max_check_attempts);

        do {
            $check_period = $this->getDialog()->ask($output,\sprintf('<question>Check Period</question> <comment>(defualt: %s)</comment>: ', $input->getOption('check_period')), $input->getOption('check_period'));
        }
        while (!$check_period);
        $cfg_file[] = \sprintf('check_period %s', $check_period);

        do {
            $contacts = $this->getDialog()->ask($output, \sprintf('<question>Contacts</question> <comment>(default: %s)</comment>: ', $input->getOption('contacts')), $input->getOption('contacts'));
        }
        while (!$contacts);
        $cfg_file[] = \sprintf('contacts %s', $contacts);

        do {
            $contact_groups = $this->getDialog()->ask($output, \sprintf('<question>Contact Groups</question> <comment>(default: %s)</comment>: ', $input->getOption('contact_groups')), $input->getOption('contact_groups'));
        }
        while (!$contact_groups);
        $cfg_file[] = \sprintf('contact_groups %s', $contact_groups);

        do {
            $notification_interval = $this->getDialog()->ask($output, sprintf('<question>Notification Interval</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_interval')), $input->getOption('notification_interval'));
        }
        while (!is_numeric($notification_interval));
        $cfg_file[] = sprintf('notification_interval %s', $notification_interval);

        do {
            $notification_period = $this->getDialog()->ask($output, \sprintf('<question>Notification Period</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_period')), $input->getOption('notification_period'));
        }
        while (!$notification_period);
        $cfg_file[] = \sprintf('notification_period %s', $notification_period);

        // Optional
        $display_nameDefault = $input->getOption('display_name') ? $input->getOption('display_name') : $host_name;
        if ($display_name = $this->getDialog()->ask($output, sprintf('<question>Display Name</question> <comment>(default: %s)</comment>: ', $display_nameDefault), $display_nameDefault)) {
            $cfg_file[] = sprintf('display_name %s', $display_name);
        }
        if ($parents = $this->getDialog()->ask($output, sprintf('<question>Parents</question> <comment>(default: %s)</comment>: ', $input->getOption('parents')), $input->getOption('parents'))) {
            $cfg_file[] = sprintf('parents %s', $parents);
        }
        if ($hostgroups = $this->getDialog()->ask($output, sprintf('<question>Hostgroups</question> <comment>(default: %s)</comment>: ', $input->getOption('hostgroups')), $input->getOption('hostgroups'))) {
            $cfg_file[] = sprintf('hostgroups %s', $hostgroups);
        }
        if ($check_command = $this->getDialog()->ask($output, sprintf('<question>Check Command</question> <comment>(default: %s)</comment>: ', $input->getOption('check_command')), $input->getOption('check_command'))) {
            $cfg_file[] = sprintf('check_command %s', $check_command);
        }
        if ($initial_state = $this->getDialog()->ask($output, sprintf('<question>Initial State</question> <info>[o,d,u]</info> <comment>(default: %s)</comment>: ', $input->getOption('initial_state')), $input->getOption('initial_state'))) {
            $cfg_file[] = sprintf('initial_state %s', $initial_state);
        }
        if ($check_interval = $this->getDialog()->ask($output, sprintf('<question>Check Interval</question> <comment>(default: %s)</comment>: ', $input->getOption('check_interval')), $input->getOption('check_interval'))) {
            $cfg_file[] = sprintf('check_interval %s', $check_interval);
        }
        if ($retry_interval = $this->getDialog()->ask($output, sprintf('<question>Retry Interval</question> <comment>(default: %s)</comment>: ', $input->getOption('retry_interval')), $input->getOption('retry_interval'))) {
            $cfg_file[] = sprintf('retry_interval %s', $retry_interval);
        }
        if ($active_checks_enabled = $this->getDialog()->ask($output, sprintf('<question>Active Checks Enabled</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('active_checks_enabled')), $input->getOption('active_checks_enabled'))) {
            $cfg_file[] = sprintf('active_checks_enabled %s', $active_checks_enabled);
        }
        if ($passive_checks_enabled = $this->getDialog()->ask($output, sprintf('<question>Passive Checks Enabled</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('passive_checks_enabled')), $input->getOption('passive_checks_enabled'))) {
            $cfg_file[] = sprintf('passive_checks_enabled %s', $passive_checks_enabled);
        }
        if ($obsess_over_host = $this->getDialog()->ask($output, sprintf('<question>Obsess Over Host</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('obsess_over_host')), $input->getOption('obsess_over_host'))) {
            $cfg_file[] = sprintf('obsess_over_host %s', $obsess_over_host);
        }
        if ($check_freshness = $this->getDialog()->ask($output, sprintf('<question>Check Freshness</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('check_freshness')), $input->getOption('check_freshness'))) {
            $cfg_file[] = sprintf('check_freshness %s', $check_freshness);
        }
        if ($freshness_threshold = $this->getDialog()->ask($output, sprintf('<question>Freshness Threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('freshness_threshold')), $input->getOption('freshness_threshold'))) {
            $cfg_file[] = sprintf('freshness_threshold %s', $freshness_threshold);
        }
        if ($event_handler = $this->getDialog()->ask($output, sprintf('<question>Event Handler</question> <comment>(default: %s)</comment>: ', $input->getOption('event_handler')), $input->getOption('event_handler'))) {
            $cfg_file[] = sprintf('event_handler %s', $event_handler);
        }
        if ($event_handler_enabled = $this->getDialog()->ask($output, sprintf('<question>Event Handler Enabled</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('event_handler_enabled')), $input->getOption('event_handler_enabled'))) {
            $cfg_file[] = sprintf('event_handler_enabled %s', $event_handler_enabled);
        }
        if ($low_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>Low Flap Threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('low_flap_threshold')), $input->getOption('low_flap_threshold'))) {
            $cfg_file[] = sprintf('low_flap_threshold %s', $low_flap_threshold);
        }
        if ($high_flap_threshold = $this->getDialog()->ask($output, sprintf('<question>High Flap Threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('high_flap_threshold')), $input->getOption('high_flap_threshold'))) {
            $cfg_file[] = sprintf('high_flap_threshold %s', $high_flap_threshold);
        }
        if ($flap_detection_enabled = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Enabled</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('flap_detection_enabled')), $input->getOption('flap_detection_enabled'))) {
            $cfg_file[] = sprintf('flap_detection_enabled %s', $flap_detection_enabled);
        }
        if ($flap_detection_options = $this->getDialog()->ask($output, sprintf('<question>Flap Detection Options</question> <info>[o,d,u]</info> <comment>(default: %s)</comment>: ', $input->getOption('flap_detection_options')), $input->getOption('flap_detection_options'))) {
            $cfg_file[] = sprintf('flap_detection_options %s', $flap_detection_options);
        }
        if ($process_perf_data = $this->getDialog()->ask($output, sprintf('<question>Process Pref Data</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('process_perf_data')), $input->getOption('process_perf_data'))) {
            $cfg_file[] = sprintf('process_perf_data %s', $process_perf_data);
        }
        if ($retain_status_information = $this->getDialog()->ask($output, sprintf('<question>Retain Status Information</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('retain_status_information')), $input->getOption('retain_status_information'))) {
            $cfg_file[] = sprintf('retain_status_information %s', $retain_status_information);
        }
        if ($retain_nonstatus_information = $this->getDialog()->ask($output, sprintf('<question>Retain Nonstatus Information</question> <info>[0/1]</info> <comment>(default: %s)</comment>: ', $input->getOption('retain_nonstatus_information')), $input->getOption('retain_nonstatus_information'))) {
            $cfg_file[] = sprintf('retain_nonstatus_information %s', $retain_nonstatus_information);
        }
        if ($first_notification_delay = $this->getDialog()->ask($output, sprintf('<question>First Notification Delay</question> <comment>(default: %s)</comment>: ', $input->getOption('first_notification_delay')), $input->getOption('first_notification_delay'))) {
            $cfg_file[] = sprintf('first_notification_delay %s', $first_notification_delay);
        }
        if ($notification_options = $this->getDialog()->ask($output, sprintf('<question>Notification Options</question> <info>[d,u,r,f,s]</info> <comment>(default: %s)</comment>: ', $input->getOption('notification_options')), $input->getOption('notification_options'))) {
            $cfg_file[] = sprintf('notification_options %s', $notification_options);
        }
        if ($notifications_enabled = $this->getDialog()->ask($output, sprintf('<question>Notifications Enabled</question> <info>[0/1]</info><comment>(default: %s)</comment>: ', $input->getOption('notifications_enabled')), $input->getOption('notifications_enabled'))) {
            $cfg_file[] = sprintf('notifications_enabled %s', $notifications_enabled);
        }
        if ($stalking_options = $this->getDialog()->ask($output, sprintf('<question>Stalking Options</question> <info>[o,d,u]</info> <comment>(default: %s)</comment>: ', $input->getOption('stalking_options')), $input->getOption('stalking_options'))) {
            $cfg_file[] = sprintf('stalking_options %s', $stalking_options);
        }
        if ($notes = $this->getDialog()->ask($output, sprintf('<question>Notes</question> <comment>(default: %s)</comment>: ', $input->getOption('notes')), $input->getOption('notes'))) {
            $cfg_file[] = sprintf('notes %s', $notes);
        }
        if ($notes_url = $this->getDialog()->ask($output, sprintf('<question>Notes URL</question> <comment>(default: %s)</comment>: ', $input->getOption('notes_url')), $input->getOption('notes_url'))) {
            $cfg_file[] = sprintf('notes_url %s', $notes_url);
        }
        if ($action_url = $this->getDialog()->ask($output, sprintf('<question>Action URL</question> <comment>(default: %s)</comment>: ', $input->getOption('action_url')), $input->getOption('action_url'))) {
            $cfg_file[] = sprintf('action_url %s', $action_url);
        }
        if ($icon_image = $this->getDialog()->ask($output, sprintf('<question>Icon Image</question> <comment>(default: %s)</comment>: ', $input->getOption('icon_image')), $input->getOption('icon_image'))) {
            $cfg_file[] = sprintf('icon_image %s', $icon_image);
        }
        if ($icon_image_alt = $this->getDialog()->ask($output, sprintf('<question>Icon Image Alt</question> <comment>(default: %s)</comment>: ', $input->getOption('icon_image_alt')), $input->getOption('icon_image_alt'))) {
            $cfg_file[] = sprintf('icon_image_alt %s', $icon_image_alt);
        }
        if ($vrml_image = $this->getDialog()->ask($output, sprintf('<question>VRML Image</question> <comment>(default: %s)</comment>: ', $input->getOption('vrml_image')), $input->getOption('vrml_image'))) {
            $cfg_file[] = sprintf('vrml_image %s', $vrml_image);
        }
        if ($statusmap_image = $this->getDialog()->ask($output, sprintf('<question>Statusmap Image</question> <comment>(default: %s)</comment>: ', $input->getOption('statusmap_image')), $input->getOption('statusmap_image'))) {
            $cfg_file[] = sprintf('statusmap_image %s', $statusmap_image);
        }
        if ($twod_coords = $this->getDialog()->ask($output, sprintf('<question>2D Coords</question> <comment>(default: %s)</comment>: ', $input->getOption('2d_coords')), $input->getOption('2d_coords'))) {
            $cfg_file[] = sprintf('2d_coords %s', $twod_coords);
        }
        if ($threed_coords = $this->getDialog()->ask($output, sprintf('<question>3D Coords</question> <comment>(default: %s)</comment>: ', $input->getOption('3d_coords')), $input->getOption('3d_coords'))) {
            $cfg_file[] = sprintf('3d_coords %s', $threed_coords);
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
