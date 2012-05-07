<?php

namespace Apache\Vhost;

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
            ->setName('apache:vhost:add')
            ->setDescription('Create a vhost file')
            ->addArgument('ServerName', InputArgument::OPTIONAL, 'ServerName (ie: example.local)')
            ->addOption('DocumentRoot', null, InputOption::VALUE_OPTIONAL, 'DocumentRoot (ie: /var/www/website/public_html)')
            ->addOption('DirectoryIndex', null, InputOption::VALUE_OPTIONAL, 'DirectoryIndex (ie: index.php)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      // ServerName is required
      if (!$ServerName = $input->getArgument('ServerName')){
        do {
          $ServerName = $this->getDialog()->ask($output, '<question>ServerName (ie example.local)</question> ');
        } while (!$ServerName);
      }

      if (!$DocumentRoot = $input->getOption('DocumentRoot')){
        $DocumentRoot = $this->getDialog()->ask($output, sprintf('<question>DocumentRoot (default: %s)</question> ',\getcwd()), \getcwd());
      }

      if (!$DirectoryIndex = $input->getOption('DirectoryIndex')){
        $DirectoryIndex = $this->getDialog()->ask($output, '<question>DirectoryIndex (default: index.php)</question> ', 'index.php');
      }

      // Can setup various templates based on PHP_OS or can just use defaults
      // base on PHP_OS like a base default log directory
      $vhost_template = <<<EOF
<VirtualHost *:80>
  ServerName %ServerName%
#  ServerAdmin root@%ServerName%
#  ServerAlias *.%ServerName%
  DocumentRoot %DocumentRoot%
  DirectoryIndex %DirectoryIndex%

  <Directory %DocumentRoot%>
    AllowOverride All
    Allow from All
  </Directory> 

#  ErrorLog /var/log/apache2/%ServerName%-error_log
#  CustomLog /var/log/apache2/%ServerName%-access_log common
</VirtualHost>
EOF;

      $VhostFile = strtr($vhost_template,array(
        '%ServerName%' => $ServerName,
        '%DocumentRoot%' => $DocumentRoot,
        '%DirectoryIndex%' => $DirectoryIndex,
      ));
      
      $output->writeln($VhostFile);

      /**
       * @todo There needs to be a way we can change where this vhost file
       *       goes. As an example, Mac, Linux, and Windows. This could be
       *       an option so when the user runs the command they can be decide
       *       where the file goes, default will be linux
       */
      if (in_array(PHP_OS,array('Linux'))){
        if ($this->getDialog()->askConfirmation($output, '<question>Would you like to write this file to sites-available directory? (default: y)</question> ', true)){
          $filename = sprintf('/tmp/vhost-%s',time());
          \file_put_contents($filename,$VhostFile);
          $process = new Process(sprintf('sudo cp %s /etc/apache2/sites-available/%s', $filename, $ServerName));
          $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

          /**
           * This is another example of a system specific command, this will only
           * work with linux.
           */
          if ($this->getDialog()->askConfirmation($output, '<question>Would you like to enable the vhost? (default: y)</question> ', true)){
            $process = new Process(sprintf('sudo a2ensite %s', $ServerName));
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
          }
        }
      } else {
        // User is not using linux so I have no idea where to put this stuff
        $output->writeln(sprintf('<comment>If only you were running Linux I could add the vhost and enable your site.</comment>'));
        $output->writeln(sprintf('<comment>%s != Linux</comment>',PHP_OS));
      }

      if (in_array(PHP_OS,array('Linux','Darwin'))){
        if ($this->getDialog()->askConfirmation($output, '<question>Would you like me to update your /etc/hosts file? (default: y)</question> ', true)){
          /**
           * This command is part of the JoshuaEstes/stEtc library
           */
          $command = $this->getApplication()->find('etc:hosts:add');
          $returnCode = $command->run(new ArrayInput(array('command'=>'etc:hosts:add','hostname'=>$ServerName)), $output);
        }
      } else {
        $output->writeln(sprintf('<comment>%s does not have a hosts file at /etc/hosts, I cannot allow you to update your hosts file.</comment>',PHP_OS));
      }

      if ($this->getDialog()->askConfirmation($output, '<question>Would you like to restart apache? (default: y)</question> ', true)){
        // just messing around, trying some different stuff
        unset($process);
        switch(PHP_OS){
          case('Linux'):
          case('Darwin'):
            $process = new Process('sudo $(which apachectl) -k restart');
            break;
        }
        if (isset($process)){
          $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
        } else{
          $output->writeln('<comment>I have no idea how to restart your apache =(</comment>');
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
