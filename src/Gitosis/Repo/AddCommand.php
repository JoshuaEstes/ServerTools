<?php

namespace Gitosis\Repo;

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
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;
use Gitosis\Config;

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('gitosis:repo:add')
            ->setDescription('add a repo to your gitosis.conf file')
            ->addOption('gitosis-conf', null, InputOption::VALUE_REQUIRED, "Location of gitosis.conf", \getcwd() . '/gitosis.conf')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, "Name of repo")
            ->addOption('gitweb', null, InputOption::VALUE_REQUIRED, 'Allow gitweb to show this repo', 'no')
            ->addOption('owner', null, InputOption::VALUE_REQUIRED, 'Owner of repo, displayed on gitweb')
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Description of repo, displayed on gitweb')
            ->addOption('daemon', null, InputOption::VALUE_REQUIRED, 'Allow git-daemon to publish this repo', 'yes')
        ;
    }

    /**
     *
     * @param InputInterface $input 
     */
    protected function validateGitosisConf(InputInterface $input) {
        if (!\is_file($input->getOption('gitosis-conf'))) {
            throw new \LogixException(sprintf('Cannot find "%s".', $input->getOption('gitosis-conf')));
        }

        if (!\is_writable($input->getOption('gitosis-conf'))) {
            throw new \LogicException(sprintf('"%s" is not writable.', $input->getOption('gitosis-conf')));
        }
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output) {
        if (null !== $input->getOption('gitosis-conf')) {
            $this->validateGitosisConf($input);
        }
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output) {
        do {
            $input->setOption('gitosis-conf', $this->getDialog()->ask($output, sprintf('<question>Path to gitosis.conf</question> (default: %s): ', $input->getOption('gitosis-conf')), $input->getOption('gitosis-conf')));
        }
        while (!$input->getOption('gitosis-conf'));

        $this->setOption('name', $this->getDialog()->ask($output, sprintf('<question></question> (default: %s) :', $input->getOption('name')), $input->getOption('name')));
        $this->setOption('gitweb', $this->getDialog()->ask($output, sprintf('<question></question> (default: %s) :', $input->getOption('gitweb')), $input->getOption('gitweb')));
        $this->setOption('owner', $this->getDialog()->ask($output, sprintf('<question></question> (default: %s) :', $input->getOption('owner')), $input->getOption('owner')));
        $this->setOption('description', $this->getDialog()->ask($output, sprintf('<question></question> (default: %s) :', $input->getOption('description')), $input->getOption('description')));
        $this->setOption('daemon', $this->getDialog()->ask($output, sprintf('<question></question> (default: %s) :', $input->getOption('daemon')), $input->getOption('daemon')));
    }

    /**
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

    /**
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->validateGitosisConf($input);

        
    }

}