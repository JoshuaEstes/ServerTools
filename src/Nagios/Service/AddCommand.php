<?php

namespace Nagios\Service;

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
            ->setName('nagios:service:add')
            ->setDescription('Create a host definition')
            ->addOption('service-cfg-path', null, InputOption::VALUE_REQUIRED, 'Path where to write to the file', '/usr/local/nagios/etc/objects/service')
            // Required
            ->addOption('host_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name(s) of the host(s) that the service "runs" on or is associated with. Multiple hosts should be separated by commas.')
            ->addOption('service_description', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the description of the service, which may contain spaces, dashes, and colons (semicolons, apostrophes, and quotation marks should be avoided). No two services associated with the same host can have the same description. Services are uniquely identified with their host_name and service_description directives.')
            ->addOption('check_command', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the command that Nagios will run in order to check the status of the service. The maximum amount of time that the service check command can run is controlled by the service_check_timeout option.')
            ->addOption('max_check_attempts', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of times that Nagios will retry the service check command if it returns any state other than an OK state. Setting this value to 1 will cause Nagios to generate an alert without retrying the service check again.', 4)
            ->addOption('check_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before scheduling the next "regular" check of the service. "Regular" checks are those that occur when the service is in an OK state or when the service is in a non-OK state, but has already been rechecked max_check_attempts number of times. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. More information on this value can be found in the check scheduling documentation.')
            ->addOption('retry_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before scheduling a re-check of the service. Services are rescheduled at the retry interval when they have changed to a non-OK state. Once the service has been retried max_check_attempts times without a change in its status, it will revert to being scheduled at its "normal" rate as defined by the check_interval value. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. More information on this value can be found in the check scheduling documentation.')
            ->addOption('check_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which active checks of this service can be made.', '24x7')
            ->addOption('notification_interval', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before re-notifying a contact that this service is still in a non-OK state. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. If you set this value to 0, Nagios will not re-notify contacts about problems for this service - only one problem notification will be sent out.', 0)
            ->addOption('notification_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which notifications of events for this service can be sent out to contacts. No service notifications will be sent out during times which is not covered by the time period.', '24x7')
            ->addOption('contacts', null, InputOption::VALUE_REQUIRED, 'This is a list of the short names of the contacts that should be notified whenever there are problems (or recoveries) with this service. Multiple contacts should be separated by commas. Useful if you want notifications to go to just a few people and don\'t want to configure contact groups. You must specify at least one contact or contact group in each service definition.')
            ->addOption('contact_groups', null, InputOption::VALUE_REQUIRED, 'This is a list of the short names of the contact groups that should be notified whenever there are problems (or recoveries) with this service. Multiple contact groups should be separated by commas. You must specify at least one contact or contact group in each service definition.', 'admins')
            // Optional
            ->addOption('hostgroup_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name(s) of the hostgroup(s) that the service "runs" on or is associated with. Multiple hostgroups should be separated by commas. The hostgroup_name may be used instead of, or in addition to, the host_name directive.')
            ->addOption('display_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an alternate name that should be displayed in the web interface for this service. If not specified, this defaults to the value you specify for the service_description directive. Note: The current CGIs do not use this option, although future versions of the web interface will.')
            ->addOption('servicegroups', null, InputOption::VALUE_REQUIRED, 'This directive is used to identify the short name(s) of the servicegroup(s) that the service belongs to. Multiple servicegroups should be separated by commas. This directive may be used as an alternative to using the members directive in servicegroup definitions.')
            ->addOption('is_volatile', null, InputOption::VALUE_REQUIRED, 'This directive is used to denote whether the service is "volatile". Services are normally not volatile. More information on volatile service and how they differ from normal services can be found here. Value: 0 = service is not volatile, 1 = service is volatile.', 0)
            ->addOption('initial_state', null, InputOption::VALUE_REQUIRED, 'By default Nagios will assume that all services are in OK states when it starts. You can override the initial state for a service by using this directive. Valid options are: o = OK, w = WARNING, u = UNKNOWN, and c = CRITICAL.', 'o')
            ->addOption('active_checks_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not active checks of this service are enabled. Values: 0 = disable active service checks, 1 = enable active service checks (default).', 1)
            ->addOption('passive_checks_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not passive checks of this service are enabled. Values: 0 = disable passive service checks, 1 = enable passive service checks (default).', 1)
            ->addOption('obsess_over_service', null, InputOption::VALUE_REQUIRED, 'This directive determines whether or not checks for the service will be "obsessed" over using the ocsp_command.', 1)
            ->addOption('check_freshness', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not freshness checks are enabled for this service. Values: 0 = disable freshness checks, 1 = enable freshness checks (default).', 1)
            ->addOption('freshness_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the freshness threshold (in seconds) for this service. If you set this directive to a value of 0, Nagios will determine a freshness threshold to use automatically.', 0)
            ->addOption('event_handler', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the command that should be run whenever a change in the state of the service is detected (i.e. whenever it goes down or recovers). Read the documentation on event handlers for a more detailed explanation of how to write scripts for handling events. The maximum amount of time that the event handler command can run is controlled by the event_handler_timeout option.')
            ->addOption('event_handler_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the event handler for this service is enabled. Values: 0 = disable service event handler, 1 = enable service event handler.', 1)
            ->addOption('low_flap_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the low state change threshold used in flap detection for this service. More information on flap detection can be found here. If you set this directive to a value of 0, the program-wide value specified by the low_service_flap_threshold directive will be used.')
            ->addOption('high_flap_threshold', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the high state change threshold used in flap detection for this service. More information on flap detection can be found here. If you set this directive to a value of 0, the program-wide value specified by the high_service_flap_threshold directive will be used.')
            ->addOption('flap_detection_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not flap detection is enabled for this service. More information on flap detection can be found here. Values: 0 = disable service flap detection, 1 = enable service flap detection.', 1)
            ->addOption('flap_detection_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine what service states the flap detection logic will use for this service. Valid options are a combination of one or more of the following: o = OK states, w = WARNING states, c = CRITICAL states, u = UNKNOWN states.')
            ->addOption('process_perf_data', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the processing of performance data is enabled for this service. Values: 0 = disable performance data processing, 1 = enable performance data processing.', 1)
            ->addOption('retain_status_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not status-related information about the service is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable status information retention, 1 = enable status information retention.', 1)
            ->addOption('retain_nonstatus_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not non-status information about the service is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable non-status information retention, 1 = enable non-status information retention.', 1)
            ->addOption('first_notification_delay', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the number of "time units" to wait before sending out the first problem notification when this service enters a non-OK state. Unless you\'ve changed the interval_length directive from the default value of 60, this number will mean minutes. If you set this value to 0, Nagios will start sending out notifications immediately.')
            ->addOption('notification_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine when notifications for the service should be sent out. Valid options are a combination of one or more of the following: w = send notifications on a WARNING state, u = send notifications on an UNKNOWN state, c = send notifications on a CRITICAL state, r = send notifications on recoveries (OK state), f = send notifications when the service starts and stops flapping, and s = send notifications when scheduled downtime starts and ends. If you specify n (none) as an option, no service notifications will be sent out. If you do not specify any notification options, Nagios will assume that you want notifications to be sent out for all possible states. Example: If you specify w,r in this field, notifications will only be sent out when the service goes into a WARNING state and when it recovers from a WARNING state.', 'w,u,c,r')
            ->addOption('notifications_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not notifications for this service are enabled. Values: 0 = disable service notifications, 1 = enable service notifications.', 1)
            ->addOption('stalking_options', null, InputOption::VALUE_REQUIRED, 'This directive determines which service states "stalking" is enabled for. Valid options are a combination of one or more of the following: o = stalk on OK states, w = stalk on WARNING states, u = stalk on UNKNOWN states, and c = stalk on CRITICAL states. More information on state stalking can be found here.')
            ->addOption('notes', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional string of notes pertaining to the service. If you specify a note here, you will see the it in the extended information CGI (when you are viewing information about the specified service).')
            ->addOption('notes_url', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional URL that can be used to provide more information about the service. If you specify an URL, you will see a red folder icon in the CGIs (when you are viewing service information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/). This can be very useful if you want to make detailed information on the service, emergency contact methods, etc. available to other support staff.')
            ->addOption('action_url', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an optional URL that can be used to provide more actions to be performed on the service. If you specify an URL, you will see a red "splat" icon in the CGIs (when you are viewing service information) that links to the URL you specify here. Any valid URL can be used. If you plan on using relative paths, the base path will the the same as what is used to access the CGIs (i.e. /cgi-bin/nagios/).')
            ->addOption('icon_image', null, InputOption::VALUE_REQUIRED, 'This variable is used to define the name of a GIF, PNG, or JPG image that should be associated with this service. This image will be displayed in the status and extended information CGIs. The image will look best if it is 40x40 pixels in size. Images for services are assumed to be in the logos/ subdirectory in your HTML images directory (i.e. /usr/local/nagios/share/images/logos).')
            ->addOption('icon_image_alt', null, InputOption::VALUE_REQUIRED, 'This variable is used to define an optional string that is used in the ALT tag of the image specified by the <icon_image> argument. The ALT tag is used in the status, extended information and statusmap CGIs.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /* @var $input Symfony\Component\Console\Input\ArgvInput */
        $cfg_file = array('define service{');

        // Required
        do {
            $host_name = $this->getDialog()->ask($output, \sprintf('<question>host_name</question> <comment>(default: %s)</comment>: ', $input->getOption('host_name')), $input->getOption('host_name'));
        }
        while (!$host_name);
        $cfg_file[] = \sprintf('host_name %s', $host_name);
        do {
            $service_description = $this->getDialog()->ask($output, \sprintf('<question>service_description</question> <comment>(default: %s)</comment>: ', $input->getOption('service_description')), $input->getOption('service_description'));
        }
        while (!$service_description);
        $cfg_file[] = \sprintf('service_description %s', $service_description);
        do {
            $check_command = $this->getDialog()->ask($output, \sprintf('<question>check_command</question> <comment>(default: %s)</comment>: ', $input->getOption('check_command')), $input->getOption('check_command'));
        }
        while (!$check_command);
        $cfg_file[] = \sprintf('check_command %s', $check_command);
        do {
            $max_check_attempts = $this->getDialog()->ask($output, \sprintf('<question>max_check_attempts</question> <comment>(default: %s)</comment>: ', $input->getOption('max_check_attempts')), $input->getOption('max_check_attempts'));
        }
        while (!$max_check_attempts);
        $cfg_file[] = \sprintf('max_check_attempts %s', $max_check_attempts);
        do {
            $check_interval = $this->getDialog()->ask($output, \sprintf('<question>check_interval</question> <comment>(default: %s)</comment>: ', $input->getOption('check_interval')), $input->getOption('check_interval'));
        }
        while (!$check_interval);
        $cfg_file[] = \sprintf('check_interval %s', $check_interval);
        do {
            $retry_interval = $this->getDialog()->ask($output, \sprintf('<question>retry_interval</question> <comment>(default: %s)</comment>: ', $input->getOption('retry_interval')), $input->getOption('retry_interval'));
        }
        while (!$retry_interval);
        $cfg_file[] = \sprintf('retry_interval %s', $retry_interval);
        do {
            $check_period = $this->getDialog()->ask($output, \sprintf('<question>check_period</question> <comment>(default: %s)</comment>: ', $input->getOption('check_period')), $input->getOption('check_period'));
        }
        while (!$check_period);
        $cfg_file[] = \sprintf('check_period %s', $check_period);
        do {
            $notification_interval = $this->getDialog()->ask($output, \sprintf('<question>notification_interval</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_interval')), $input->getOption('notification_interval'));
        }
        while (!$notification_interval);
        $cfg_file[] = \sprintf('notification_interval %s', $notification_interval);
        do {
            $notification_period = $this->getDialog()->ask($output, \sprintf('<question>notification_period</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_period')), $input->getOption('notification_period'));
        }
        while (!$notification_period);
        $cfg_file[] = \sprintf('notification_period %s', $notification_period);
        do {
            $contacts = $this->getDialog()->ask($output, \sprintf('<question>contacts</question> <comment>(default: %s)</comment>: ', $input->getOption('contacts')), $input->getOption('contacts'));
        }
        while (!$contacts);
        $cfg_file[] = \sprintf('contacts %s', $contacts);
        do {
            $contact_groups = $this->getDialog()->ask($output, \sprintf('<question>contact_groups</question> <comment>(default: %s)</comment>: ', $input->getOption('contact_groups')), $input->getOption('contact_groups'));
        }
        while (!$contact_groups);
        $cfg_file[] = \sprintf('contact_groups %s', $contact_groups);

        // Optional
        if ($hostgroup_name = $this->getDialog()->ask($output, \sprintf('<question>hostgroup_name</question> <comment>(default: %s)</comment>: ', $input->getOption('hostgroup_name')), $input->getOption('hostgroup_name'))) {
            $cfg_file[] = \sprintf('hostgroup_name %s', $hostgroup_name);
        }
        if ($display_name = $this->getDialog()->ask($output, \sprintf('<question>display_name</question> <comment>(default: %s)</comment>: ', $input->getOption('display_name')), $input->getOption('display_name'))) {
            $cfg_file[] = \sprintf('display_name %s', $display_name);
        }
        if ($servicegroups = $this->getDialog()->ask($output, \sprintf('<question>servicegroups</question> <comment>(default: %s)</comment>: ', $input->getOption('servicegroups')), $input->getOption('servicegroups'))) {
            $cfg_file[] = \sprintf('servicegroups %s', $servicegroups);
        }
        if ($is_volatile = $this->getDialog()->ask($output, \sprintf('<question>is_volatile</question> <comment>(default: %s)</comment>: ', $input->getOption('is_volatile')), $input->getOption('is_volatile'))) {
            $cfg_file[] = \sprintf('is_volatile %s', $is_volatile);
        }
        if ($initial_state = $this->getDialog()->ask($output, \sprintf('<question>initial_state</question> <comment>(default: %s)</comment>: ', $input->getOption('initial_state')), $input->getOption('initial_state'))) {
            $cfg_file[] = \sprintf('initial_state %s', $initial_state);
        }
        if ($active_checks_enabled = $this->getDialog()->ask($output, \sprintf('<question>active_checks_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('active_checks_enabled')), $input->getOption('active_checks_enabled'))) {
            $cfg_file[] = \sprintf('active_checks_enabled %s', $active_checks_enabled);
        }
        if ($passive_checks_enabled = $this->getDialog()->ask($output, \sprintf('<question>passive_checks_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('passive_checks_enabled')), $input->getOption('passive_checks_enabled'))) {
            $cfg_file[] = \sprintf('passive_checks_enabled %s', $passive_checks_enabled);
        }
        if ($obsess_over_service = $this->getDialog()->ask($output, \sprintf('<question>obsess_over_service</question> <comment>(default: %s)</comment>: ', $input->getOption('obsess_over_service')), $input->getOption('obsess_over_service'))) {
            $cfg_file[] = \sprintf('obsess_over_service %s', $obsess_over_service);
        }
        if ($check_freshness = $this->getDialog()->ask($output, \sprintf('<question>check_freshness</question> <comment>(default: %s)</comment>: ', $input->getOption('check_freshness')), $input->getOption('check_freshness'))) {
            $cfg_file[] = \sprintf('check_freshness %s', $check_freshness);
        }
        if ($freshness_threshold = $this->getDialog()->ask($output, \sprintf('<question>freshness_threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('freshness_threshold')), $input->getOption('freshness_threshold'))) {
            $cfg_file[] = \sprintf('freshness_threshold %s', $freshness_threshold);
        }
        if ($event_handler = $this->getDialog()->ask($output, \sprintf('<question>event_handler</question> <comment>(default: %s)</comment>: ', $input->getOption('event_handler')), $input->getOption('event_handler'))) {
            $cfg_file[] = \sprintf('event_handler %s', $event_handler);
        }
        if ($event_handler_enabled = $this->getDialog()->ask($output, \sprintf('<question>event_handler_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('event_handler_enabled')), $input->getOption('event_handler_enabled'))) {
            $cfg_file[] = \sprintf('event_handler_enabled %s', $event_handler_enabled);
        }
        if ($low_flap_threshold = $this->getDialog()->ask($output, \sprintf('<question>low_flap_threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('low_flap_threshold')), $input->getOption('low_flap_threshold'))) {
            $cfg_file[] = \sprintf('low_flap_threshold %s', $low_flap_threshold);
        }
        if ($high_flap_threshold = $this->getDialog()->ask($output, \sprintf('<question>high_flap_threshold</question> <comment>(default: %s)</comment>: ', $input->getOption('high_flap_threshold')), $input->getOption('high_flap_threshold'))) {
            $cfg_file[] = \sprintf('high_flap_threshold %s', $high_flap_threshold);
        }
        if ($flap_detection_enabled = $this->getDialog()->ask($output, \sprintf('<question>flap_detection_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('flap_detection_enabled')), $input->getOption('flap_detection_enabled'))) {
            $cfg_file[] = \sprintf('flap_detection_enabled %s', $flap_detection_enabled);
        }
        if ($flap_detection_options = $this->getDialog()->ask($output, \sprintf('<question>flap_detection_options</question> <comment>(default: %s)</comment>: ', $input->getOption('flap_detection_options')), $input->getOption('flap_detection_options'))) {
            $cfg_file[] = \sprintf('flap_detection_options %s', $flap_detection_options);
        }
        if ($process_perf_data = $this->getDialog()->ask($output, \sprintf('<question>process_perf_data</question> <comment>(default: %s)</comment>: ', $input->getOption('process_perf_data')), $input->getOption('process_perf_data'))) {
            $cfg_file[] = \sprintf('process_perf_data %s', $process_perf_data);
        }
        if ($retain_status_information = $this->getDialog()->ask($output, \sprintf('<question>retain_status_information</question> <comment>(default: %s)</comment>: ', $input->getOption('retain_status_information')), $input->getOption('retain_status_information'))) {
            $cfg_file[] = \sprintf('retain_status_information %s', $retain_status_information);
        }
        if ($retain_nonstatus_information = $this->getDialog()->ask($output, \sprintf('<question>retain_nonstatus_information</question> <comment>(default: %s)</comment>: ', $input->getOption('retain_nonstatus_information')), $input->getOption('retain_nonstatus_information'))) {
            $cfg_file[] = \sprintf('retain_nonstatus_information %s', $retain_nonstatus_information);
        }
        if ($first_notification_delay = $this->getDialog()->ask($output, \sprintf('<question>first_notification_delay</question> <comment>(default: %s)</comment>: ', $input->getOption('first_notification_delay')), $input->getOption('first_notification_delay'))) {
            $cfg_file[] = \sprintf('first_notification_delay %s', $first_notification_delay);
        }
        if ($notification_options = $this->getDialog()->ask($output, \sprintf('<question>notification_options</question> <comment>(default: %s)</comment>: ', $input->getOption('notification_options')), $input->getOption('notification_options'))) {
            $cfg_file[] = \sprintf('notification_options %s', $notification_options);
        }
        if ($notifications_enabled = $this->getDialog()->ask($output, \sprintf('<question>notifications_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('notifications_enabled')), $input->getOption('notifications_enabled'))) {
            $cfg_file[] = \sprintf('notifications_enabled %s', $notifications_enabled);
        }
        if ($stalking_options = $this->getDialog()->ask($output, \sprintf('<question>stalking_options</question> <comment>(default: %s)</comment>: ', $input->getOption('stalking_options')), $input->getOption('stalking_options'))) {
            $cfg_file[] = \sprintf('stalking_options %s', $stalking_options);
        }
        if ($notes = $this->getDialog()->ask($output, \sprintf('<question>notes</question> <comment>(default: %s)</comment>: ', $input->getOption('notes')), $input->getOption('notes'))) {
            $cfg_file[] = \sprintf('notes %s', $notes);
        }
        if ($notes_url = $this->getDialog()->ask($output, \sprintf('<question>notes_url</question> <comment>(default: %s)</comment>: ', $input->getOption('notes_url')), $input->getOption('notes_url'))) {
            $cfg_file[] = \sprintf('notes_url %s', $notes_url);
        }
        if ($action_url = $this->getDialog()->ask($output, \sprintf('<question>action_url</question> <comment>(default: %s)</comment>: ', $input->getOption('action_url')), $input->getOption('action_url'))) {
            $cfg_file[] = \sprintf('action_url %s', $action_url);
        }
        if ($icon_image = $this->getDialog()->ask($output, \sprintf('<question>icon_image</question> <comment>(default: %s)</comment>: ', $input->getOption('icon_image')), $input->getOption('icon_image'))) {
            $cfg_file[] = \sprintf('icon_image %s', $icon_image);
        }
        if ($icon_image_alt = $this->getDialog()->ask($output, \sprintf('<question>icon_image_alt</question> <comment>(default: %s)</comment>: ', $input->getOption('icon_image_alt')), $input->getOption('icon_image_alt'))) {
            $cfg_file[] = \sprintf('icon_image_alt %s', $icon_image_alt);
        }

        $cfg_file[] = '}';
        // Create the file
        $serviceDefinition = \implode("\n", $cfg_file);
        $output->writeln($serviceDefinition);
        if (!$this->getDialog()->askConfirmation($output, '<question>Is the information correct?</question> <comment>(deafult: yes)</comment>: ', true)) {
            return(0);
        }

        /**
         * Place file where it needs to go
         */
        $file = $input->getOption('service-cfg-path') . '/' . $host_name . '.cfg';
        if ($this->getDialog()->askConfirmation($output, \sprintf('<question>Would you like to write to file "%s"</question> <comment>(deafult: yes)</comment>: ', $file), true)) {
            $tmpFile = '/tmp/' . \time() . '.cfg';
            \file_put_contents($tmpFile, $serviceDefinition);
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

}
