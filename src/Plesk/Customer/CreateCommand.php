<?php

namespace Plesk\Customer;

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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

if (!defined('PLESK_BIN')) {
    define('PLESK_BIN', '/usr/local/psa/bin');
}

class CreateCommand extends Command {

    protected function configure() {
        $this
            ->setName('plesk:customer:create')
            ->setDescription('Create a new plesk customer');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $cmd = array();

        // required
        do {
            $login_name = $this->getDialog()->ask($output, '<question>Login Name</question>:  ');
        }
        while (!$login_name);
        $cmd[] = sprintf('--create "%s"', $login_name);

        // optional
        if ($company = $this->getDialog()->ask($output, '<question>Company Name (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-company "%s"', $company);
        }
        if ($name = $this->getDialog()->ask($output, '<question>Contact Name (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-name "%s"', $name);
        }
        else {
            $cmd[] = sprintf('-name "%s"', $login_name);
        }
        if ($passwd = $this->getDialog()->ask($output, '<question>Password (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-passwd "%s"', $passwd);
        }
        if ($phone = $this->getDialog()->ask($output, '<question>Customer Phone Number (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-phone "%s"', $phone);
        }
        if ($fax = $this->getDialog()->ask($output, '<question>Customer Fax (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-fax "%s"', $fax);
        }
        if ($email = $this->getDialog()->ask($output, '<question>Customer Email Address (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-email "%s"', $email);
        }
        if ($address = $this->getDialog()->ask($output, '<question>Customer Address (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-address "%s"', $address);
        }
        if ($city = $this->getDialog()->ask($output, '<question>Customer City (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-city "%s"', $city);
        }
        if ($state = $this->getDialog()->ask($output, '<question>Customer State (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-state "%s"', $state);
        }
        if ($zip = $this->getDialog()->ask($output, '<question>Customer Zip (defualt: null)</question>: ')) {
            $cmd[] = sprintf('-zip "%s"', $zip);
        }
        $country = $this->getDialog()->ask($output, '<question>Customer Country (defualt: US)</question>: ', 'US');
        $cmd[] = sprintf('-country "%s"', $country);

        $command = \implode(" ", $cmd);
        $process = new Process(sprintf('%s/customer %s', \PLESK_BIN, $command));
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