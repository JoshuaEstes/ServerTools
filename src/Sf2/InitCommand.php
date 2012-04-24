<?php

namespace Sf2;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InitCommand extends Command {

    protected function configure() {
        $this
            ->setName('sf2:init')
            ->setDescription('download and install symfony 2 in the current directory.')
            ->addArgument('version',InputArgument::OPTIONAL, 'symfony 2 version', '2.1.*');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (!$this->getDialog()->askConfirmation($output,'<question>This will download the latest version of symfony2, Do you want to continue? (default: no)</question> ', false)){
            $output->writeln('aborted');
        }
        $batchProcesses = array(
          new Process('git clone http://github.com/symfony/symfony-standard.git .'),
          new Process('rm -rf .git/'),
          new Process('curl -s http://getcomposer.org/installer | php'),
          new Process('chmod +x composer.phar; ./composer.phar install'),
          new Process('php app/check.php'),
        );
        foreach ($batchProcesses as $process) {
            $process->run(function($type, $buffer) use($output) {$output->write($buffer);});
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
