<?php

namespace Sf2;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

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
          new Process('rm -rf .git/ .gitignore'),
          new Process('curl -s http://getcomposer.org/installer | php'),
          new Process('chmod +x composer.phar; ./composer.phar install'),
          new Process('php app/check.php'),
          new Process('chmod -R 0777 app/cache/ app/logs'),
          new Process('rm -rf src/Acme/'),
          new Process(sprintf('cp %s/Templates/routing_dev.yml %s/app/config/routing_dev.yml',__DIR__,\getcwd())),
          new Process(sprintf('cp %s/Templates/AppKernel.php %s/app/AppKernel.php',__DIR__,\getcwd())),
          new Process('rm -rf web/bundles/acmedemo'),
        );
        foreach ($batchProcesses as $process) {
            $output->writeln(sprintf('Executing Command: %s',$process->getCommandLine()));
            $process->run(function($type, $buffer) use($output) {$output->write($buffer);});
        }
        if ($this->getDialog()->askConfirmation($output,'<question>Would you like to configure parameters.yml? (default: yes)</question> ', true)){
          $parameters = Yaml::parse(sprintf("%s/app/config/parameters.yml",\getcwd()));
          $parameters['parameters']['database_driver'] = $this->getDialog()->ask($output,'<question>Database Driver? (default: pdo_mysql)</question> ','pdo_mysql');
          $parameters['parameters']['database_host'] = $this->getDialog()->ask($output,'<question>Database Host? (default: 127.0.0.1)</question> ','127.0.0.1');
          $parameters['parameters']['database_port'] = $this->getDialog()->ask($output,'<question>Database Port? (default: 3306)</question> ','3306');
          $parameters['parameters']['database_name'] = $this->getDialog()->ask($output,'<question>Database Name? (default: symfony)</question> ','symfony');
          $parameters['parameters']['database_user'] = $this->getDialog()->ask($output,'<question>Database User? (default: root)</question> ','root');
          $parameters['parameters']['database_password'] = $this->getDialog()->ask($output,'<question>Database Password? (default: root)</question> ', 'root');
          $parameters['parameters']['mailer_transport'] = $this->getDialog()->ask($output,'<question>Mailer Transport? (Available: smtp, mail, sendmail, or gmail) (default: sendmail)</question> ', 'sendmail');
          $parameters['parameters']['mailer_host'] = $this->getDialog()->ask($output,'<question>Mailer Host? (default: localhost)</question> ', 'localhost');
          $parameters['parameters']['mailer_user'] = $this->getDialog()->ask($output,'<question>Mailer User? (default: null)</question> ', null);
          $parameters['parameters']['mailer_password'] = $this->getDialog()->ask($output,'<question>Mailer Password? (default: null)</question> ', null);
          $parameters['parameters']['locale'] = $this->getDialog()->ask($output,'<question>Locale? (default: en)</question> ', 'en');
          $parameters['parameters']['secret'] = md5(uniqid(rand(),TRUE));
          \file_put_contents('app/config/parameters.yml',Yaml::dump($parameters));
        }

        // Setup a vhost because I'm too lazy to do this every time
        if ($this->getDialog()->askConfirmation($output, '<question>Would you like to setup a vhost file?</question> ',true)){
          do {
            $ServerName = $this->getDialog()->ask($output,'<question>ServerName (ie: foo.local)</question> ');
          } while (!$ServerName);
          $command = $this->getApplication()->find('apache:vhost:add');
          $arguments = array(
            'command' => 'apache:vhost:add',
            'ServerName' => $ServerName,
            '--DocumentRoot' => sprintf('%s/web',\getcwd()),
            '--DirectoryIndex' => 'app.php',
          );
          $command->run(new ArrayInput($arguments), $output);
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
