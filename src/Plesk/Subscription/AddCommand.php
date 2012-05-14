<?php

namespace Plesk\Subscription;

/**
 * Description
 *
 * @package    ServerTools
 * @subpackage Plesk
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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

if (!defined('PLESK_BIN')) {
    define('PLESK_BIN', '/usr/local/psa/bin');
}

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('plesk:subscription:add')
            ->setDescription('Create a new subscription');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cmd = array();
        do {
            $subscription_name = $this->getDialog()->ask($output, '<question>Subscription Name (ie: example.com)</question>: ');
        }
        while (!$subscription_name);
        $cmd[] = sprintf('--create "%s"', $subscription_name);

        do {
            $owner = $this->getDialog()->ask($output, '<question>Owner (ie: login_name)</question>: ');
        }
        while (!$owner);
        $cmd[] = sprintf('-owner "%s"', $owner);

        do {
            $ip = $this->getDialog()->ask($output, '<question>IP Address (ie: The public IP of the server)</question>: ');
        }
        while (!$ip);
        $cmd[] = sprintf('-ip "%s"', $ip);

        $service_plan = $this->getDialog()->ask($output, '<question>Service Plan (default: Default Domain)</question>: ', 'Default Domain');
        $cmd[] = sprintf('-service-plan "%s"', $service_plan);

        $hosting = $this->getDialog()->askConfirmation($output, '<question>Enable Hosting (default: y)</question>: ', true);
        $cmd[] = sprintf('-hosting %s', $hosting ? 'true' : 'false');
        if ($hosting) {
            do {
                $login = $this->getDialog()->ask($output, '<question>Username for loggin into services</question>: ');
            }
            while (!$login);
            $cmd[] = sprintf('-login "%s"', $login);

            do {
                $passwd = $this->getDialog()->ask($output, '<question>Password for user</question>: ');
            }
            while (!$passwd);
            $cmd[] = sprintf('-passwd "%s"', $passwd);
            
            $cmd[] = '-php true';
            $cmd[] = '-php_handler_type fastcgi';
            $cmd[] = '-php_safe_mode true';
        }

        $command = \implode(" ", $cmd);
        $process = new Process(sprintf('%s/subscription %s', \PLESK_BIN, $command));
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
