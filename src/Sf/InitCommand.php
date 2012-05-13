<?php

namespace Sf;

/**
 * Description
 *
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Sf
 * @version
 */
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InitCommand extends Command {

    protected function configure() {
        $this
            ->setName('sf:init')
            ->setDescription('download and install symfony 1.4.* in the current directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $process = new Process('git init; git submodule add git://github.com/symfony/symfony1.git lib/vendor/symfony');
        $process->run(function($type, $buffer)use($output){ $output->write($buffer); });
        $process = new Process('wget --no-check-certificate https://raw.github.com/github/gitignore/master/Symfony.gitignore -O .gitignore');
        $process->run(function($type, $buffer)use($output){ $output->write($buffer); });
    }

    /**
     *
     * @return Symfony\Component\Console\Helper\DialogHelper
     */
    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}
