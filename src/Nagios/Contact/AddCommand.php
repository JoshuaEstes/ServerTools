<?php

namespace Nagios\Contact;

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
            ->setName('nagios:contact:add')
            ->setDescription('Create a contact definition')
            ->addOption('contact-cfg-path', null, InputOption::VALUE_REQUIRED, 'Path where to write to the file', '/usr/local/nagios/etc/objects/contact')
            // Required
            ->addOption('contact_name', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a short name used to identify the contact. It is referenced in contact group definitions. Under the right circumstances, the $CONTACTNAME$ macro will contain this value.')
            ->addOption('host_notifications_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the contact will receive notifications about host problems and recoveries. Values: 0 = don\'t send notifications, 1 = send notifications.', 1)
            ->addOption('service_notifications_enabled', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the contact will receive notifications about service problems and recoveries. Values: 0 = don\'t send notifications, 1 = send notifications.', 1)
            ->addOption('host_notification_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which the contact can be notified about host problems or recoveries. You can think of this as an "on call" time for host notifications for the contact. Read the documentation on time periods for more information on how this works and potential problems that may result from improper use.', '24x7')
            ->addOption('service_notification_period', null, InputOption::VALUE_REQUIRED, 'This directive is used to specify the short name of the time period during which the contact can be notified about service problems or recoveries. You can think of this as an "on call" time for service notifications for the contact. Read the documentation on time periods for more information on how this works and potential problems that may result from improper use.', '24x7')
            ->addOption('host_notification_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the host states for which notifications can be sent out to this contact. Valid options are a combination of one or more of the following: d = notify on DOWN host states, u = notify on UNREACHABLE host states, r = notify on host recoveries (UP states), f = notify when the host starts and stops flapping, and s = send notifications when host or service scheduled downtime starts and ends. If you specify n (none) as an option, the contact will not receive any type of host notifications.', 'd,u,r,f')
            ->addOption('service_notification_options', null, InputOption::VALUE_REQUIRED, 'This directive is used to define the service states for which notifications can be sent out to this contact. Valid options are a combination of one or more of the following: w = notify on WARNING service states, u = notify on UNKNOWN service states, c = notify on CRITICAL service states, r = notify on service recoveries (OK states), and f = notify when the service starts and stops flapping. If you specify n (none) as an option, the contact will not receive any type of service notifications.', 'w,u,c,r,f')
            ->addOption('host_notification_commands', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a list of the short names of the commands used to notify the contact of a host problem or recovery. Multiple notification commands should be separated by commas. All notification commands are executed when the contact needs to be notified. The maximum amount of time that a notification command can run is controlled by the notification_timeout option.', 'notify-host-by-email')
            ->addOption('service_notification_commands', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a list of the short names of the commands used to notify the contact of a service problem or recovery. Multiple notification commands should be separated by commas. All notification commands are executed when the contact needs to be notified. The maximum amount of time that a notification command can run is controlled by the notification_timeout option.', 'notify-service-by-email')
            // Optional
            ->addOption('alias', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a longer name or description for the contact. Under the rights circumstances, the $CONTACTALIAS$ macro will contain this value. If not specified, the contact_name will be used as the alias.')
            ->addOption('contactgroups', null, InputOption::VALUE_REQUIRED, 'This directive is used to identify the short name(s) of the contactgroup(s) that the contact belongs to. Multiple contactgroups should be separated by commas. This directive may be used as an alternative to (or in addition to) using the members directive in contactgroup definitions.')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'This directive is used to define an email address for the contact. Depending on how you configure your notification commands, it can be used to send out an alert email to the contact. Under the right circumstances, the $CONTACTEMAIL$ macro will contain this value.')
            ->addOption('pager', null, InputOption::VALUE_REQUIRED, 'This directive is used to define a pager number for the contact. It can also be an email address to a pager gateway (i.e. pagejoe@pagenet.com). Depending on how you configure your notification commands, it can be used to send out an alert page to the contact. Under the right circumstances, the $CONTACTPAGER$ macro will contain this value.')
            ->addOption('can_submit_commands', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not the contact can submit external commands to Nagios from the CGIs. Values: 0 = don\'t allow contact to submit commands, 1 = allow contact to submit commands.', 1)
            ->addOption('retain_status_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not status-related information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable status information retention, 1 = enable status information retention.', 1)
            ->addOption('retain_nonstatus_information', null, InputOption::VALUE_REQUIRED, 'This directive is used to determine whether or not non-status information about the contact is retained across program restarts. This is only useful if you have enabled state retention using the retain_state_information directive. Value: 0 = disable non-status information retention, 1 = enable non-status information retention.', 1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cfg_file = array('define contact{');

        // Required
        do {
            $contact_name = $this->getDialog()->ask($output, \sprintf('<question>contact_name</question> <comment>(default: %s)</comment>: ', $input->getOption('contact_name')), $input->getOption('contact_name'));
        }
        while (!$contact_name);
        $cfg_file[] = \sprintf('contact_name %s', $contact_name);

        do {
            $host_notifications_enabled = $this->getDialog()->ask($output, \sprintf('<question>host_notifications_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('host_notifications_enabled')), $input->getOption('host_notifications_enabled'));
        }
        while (!$host_notifications_enabled);
        $cfg_file[] = \sprintf('host_notifications_enabled %s', $host_notifications_enabled);

        do {
            $service_notifications_enabled = $this->getDialog()->ask($output, \sprintf('<question>service_notifications_enabled</question> <comment>(default: %s)</comment>: ', $input->getOption('service_notifications_enabled')), $input->getOption('service_notifications_enabled'));
        }
        while (!$service_notifications_enabled);
        $cfg_file[] = \sprintf('service_notifications_enabled %s', $service_notifications_enabled);

        do {
            $host_notification_period = $this->getDialog()->ask($output, \sprintf('<question>host_notification_period</question> <comment>(default: %s)</comment>: ', $input->getOption('host_notification_period')), $input->getOption('host_notification_period'));
        }
        while (!$host_notification_period);
        $cfg_file[] = \sprintf('host_notification_period %s', $host_notification_period);

        do {
            $service_notification_period = $this->getDialog()->ask($output, \sprintf('<question>service_notification_period</question> <comment>(default: %s)</comment>: ', $input->getOption('service_notification_period')), $input->getOption('service_notification_period'));
        }
        while (!$service_notification_period);
        $cfg_file[] = \sprintf('service_notification_period %s', $service_notification_period);

        do {
            $host_notification_options = $this->getDialog()->ask($output, \sprintf('<question>host_notification_options</question> <comment>(default: %s)</comment>: ', $input->getOption('host_notification_options')), $input->getOption('host_notification_options'));
        }
        while (!$host_notification_options);
        $cfg_file[] = \sprintf('host_notification_options %s', $host_notification_options);

        do {
            $service_notification_options = $this->getDialog()->ask($output, \sprintf('<question>service_notification_options</question> <comment>(default: %s)</comment>: ', $input->getOption('service_notification_options')), $input->getOption('service_notification_options'));
        }
        while (!$service_notification_options);
        $cfg_file[] = \sprintf('service_notification_options %s', $service_notification_options);

        do {
            $host_notification_commands = $this->getDialog()->ask($output, \sprintf('<question>host_notification_commands</question> <comment>(default: %s)</comment>: ', $input->getOption('host_notification_commands')), $input->getOption('host_notification_commands'));
        }
        while (!$host_notification_commands);
        $cfg_file[] = \sprintf('host_notification_commands %s', $host_notification_commands);

        do {
            $service_notification_commands = $this->getDialog()->ask($output, \sprintf('<question>service_notification_commands</question> <comment>(default: %s)</comment>: ', $input->getOption('service_notification_commands')), $input->getOption('service_notification_commands'));
        }
        while (!$service_notification_commands);
        $cfg_file[] = \sprintf('service_notification_commands %s', $service_notification_commands);

        // Optional
        if ($alias = $this->getDialog()->ask($output, \sprintf('<question>alias</question> <comment>(default: %s)</comment>: ', $input->getOption('alias')), $input->getOption('alias'))) {
            $cfg_file[] = \sprintf('alias %s', $alias);
        }
        if ($contactgroups = $this->getDialog()->ask($output, \sprintf('<question>contactgroups</question> <comment>(default: %s)</comment>: ', $input->getOption('contactgroups')), $input->getOption('contactgroups'))) {
            $cfg_file[] = \sprintf('contactgroups %s', $contactgroups);
        }
        if ($email = $this->getDialog()->ask($output, \sprintf('<question>email</question> <comment>(default: %s)</comment>: ', $input->getOption('email')), $input->getOption('email'))) {
            $cfg_file[] = \sprintf('email %s', $email);
        }
        if ($pager = $this->getDialog()->ask($output, \sprintf('<question>pager</question> <comment>(default: %s)</comment>: ', $input->getOption('pager')), $input->getOption('pager'))) {
            $cfg_file[] = \sprintf('pager %s', $pager);
        }
        if ($can_submit_commands = $this->getDialog()->ask($output, \sprintf('<question>can_submit_commands</question> <comment>(default: %s)</comment>: ', $input->getOption('can_submit_commands')), $input->getOption('can_submit_commands'))) {
            $cfg_file[] = \sprintf('can_submit_commands %s', $can_submit_commands);
        }
        if ($retain_status_information = $this->getDialog()->ask($output, \sprintf('<question>retain_status_information</question> <comment>(default: %s)</comment>: ', $input->getOption('retain_status_information')), $input->getOption('retain_status_information'))) {
            $cfg_file[] = \sprintf('retain_status_information %s', $retain_status_information);
        }
        if ($retain_nonstatus_information = $this->getDialog()->ask($output, \sprintf('<question>retain_nonstatus_information</question> <comment>(default: %s)</comment>: ', $input->getOption('retain_nonstatus_information')), $input->getOption('retain_nonstatus_information'))) {
            $cfg_file[] = \sprintf('retain_nonstatus_information %s', $retain_nonstatus_information);
        }

        $cfg_file[] = '}';
        // Create the file
        $objectDefinition = \implode("\n", $cfg_file);
        $output->writeln($objectDefinition);
        if (!$this->getDialog()->askConfirmation($output, '<question>Is the information correct?</question> <comment>(deafult: yes)</comment>: ', true)) {
            return(0);
        }

        /**
         * Place file where it needs to go
         */
        $file = $input->getOption('contact-cfg-path') . '/' . $contact_name . '.cfg';
        if ($this->getDialog()->askConfirmation($output, \sprintf('<question>Would you like to write to file "%s"</question> <comment>(deafult: yes)</comment>: ', $file), true)) {
            $tmpFile = '/tmp/' . \time() . '.cfg';
            \file_put_contents($tmpFile, $objectDefinition);
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