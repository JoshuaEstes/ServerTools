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
        $version = $input->getArgument('version');
        $filename = sprintf('Symfony_Standard_%s.tgz',$version);
        $output->writeln('Waiting for 2.1 to com out before setting this up =\ ');
//        $process = new Process(sprintf('wget http://symfony.com/get/%s',$filename));
//        $process->run(function($type, $buffer) use($output) {
//                $output->write($buffer);
//            }
//        );
//
//        $process = new Process(sprintf('tar -zxvf %s',$filename));
//        $process->run(function($type, $buffer) use($output) {
//                $output->write($buffer);
//            }
//        );
//
//        $process = new Process(sprintf('rm %s',$filename));
//        $process->run(function($type, $buffer) use($output) {
//                $output->write($buffer);
//            }
//        );
//
//        $process = new Process('cp -R Symfony/ ./');
//        $process->run(function($type, $buffer) use($output) {
//                $output->write($buffer);
//            }
//        );
//
//        $process = new Process('curl -s http://getcomposer.org/installer | php && php composer.phar install');
//        $process->run(function($type, $buffer) use($output) {
//                $output->write($buffer);
//            }
//        );
    }

    /**
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}
