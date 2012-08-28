<?php

namespace Sf2;

/**
 * Description
 *
 * @author      Joshua Estes
 * @copyright
 * @package     ServerTools
 * @subpackage  Sf2
 * @version
 */
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
            ->setDescription('download and install symfony 2 in the current directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Want to make sure we really want to run this stuff
        if (!$this->getDialog()->askConfirmation($output,'<question>This will download the latest version of symfony2, Do you want to continue? (default: no)</question> ', false)){
            $output->writeln('aborted');
        }
        $batchProcesses = array(
          // Clone the symfony standard repo. This is bleeding edge =)
          // @todo in the future can checkout the version of symfony we want then
          //       then delete the git directory and ignore file
          // @todo What if user does not have git installed?
          new Process('git clone http://github.com/symfony/symfony-standard.git .'),
          // We won't need this any more
          new Process('rm -rf .git/ .gitignore'),
          // Download a new gitignore file
          new Process('curl -s https://raw.github.com/github/gitignore/master/Symfony2.gitignore -o .gitignore'),
          // Add some more entires in the .gitignore file
          new Process('echo "composer.phar" >> .gitignore'),
          new Process('echo "web/bundles" >> .gitignore'),
          // These will replace the commented out umask with an uncomment umask line
          new Process('sed -i -e "s/\/\/umask/umask/g" app/console'),
          new Process('sed -i -e "s/\/\/umask/umask/g" web/app.php'),
          new Process('sed -i -e "s/\/\/umask/umask/g" web/app_dev.php'),
          // make sure we can write to the cache and logs directories
          new Process('chmod -R 0777 app/cache/ app/logs'),
          // Let's copy the parameters.yml file to a dist so we can ignore this
          new Process('cp app/config/parameters.yml app/config/parameters.yml.dist'),
          // download and install composer, then install all the symfony stuff
          new Process('if [ -z $(which composer.phar) ]; then curl -s http://getcomposer.org/installer | php; fi; composer.phar -v install;', null, null, null, 900),
          // Let's get rid of the Acme demo bundle
          new Process('rm -rf src/Acme/'),
          /**
           * @todo Remove the templates and just remove what needs to be removed 
           * @todo Edit other files to remove the demo/acme stuff
           */
          new Process(sprintf('cp %s/Templates/routing_dev.yml %s/app/config/routing_dev.yml',__DIR__,\getcwd())),
          new Process(sprintf('cp %s/Templates/AppKernel.php %s/app/AppKernel.php',__DIR__,\getcwd())),
          new Process('rm -rf web/bundles/acmedemo'),
          // Don't need this any more
          new Process('rm web/config.php'),
          new Process('rm web/favicon.ico'),
          new Process('rm web/apple-touch-icon.png'),
          // Let's see if there is anything else we should do last
          new Process('php app/check.php'),
        );
        foreach ($batchProcesses as $process) {
            $output->writeln(sprintf('Executing Command: %s',$process->getCommandLine()));
            $process->run(function($type, $buffer) use($output) {$output->write($buffer);});
            if (!$process->isSuccessful()){
                $output->writeln(sprintf('<error>%s</error>',$process->getErrorOutput()));
            }
        }

        // Ask if user wants to create the parameters.yml file
        if ($this->getDialog()->askConfirmation($output,'<question>Would you like to configure parameters.yml? (default: yes)</question> ', true)){
          do 
          {
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
                $output->writeln(Yaml::dump($parameters));
          }while(!($this->getDialog()->askConfirmation($output,'<question>Does this look correct? (default: yes)</question> ', true)));
          \file_put_contents('app/config/parameters.yml',Yaml::dump($parameters));
        }

        // Setup a vhost because I'm too lazy to do this every time
        if ($this->getDialog()->askConfirmation($output, '<question>Would you like to setup a vhost file? (default: yes)</question> ',true)){
          do {
            $ServerName = $this->getDialog()->ask($output,'<question>ServerName (ie: foo.local)</question> ');
          } while (!$ServerName);
          // Since the apache vhost command to add a vhost exists, we want to use this
          // instead of making new code
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
