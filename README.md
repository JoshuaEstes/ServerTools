Server Tools 0.1.0
==================

ServerTools is a command line utility that allows a developer to setup a new
project with little time setting up the extra stuff such as the apache vhost
commands and such. With this utility placed on various servers, you can ssh in
to setup apache, nagios, plesk, etc. The list goes on as well as giving you the
ability to create your own commands.

Current Tasks/Commands
----------------------

* apache:restart
* apache:vhost:add
* etc:hosts:add
* nagios:contact:add
* nagios:host:add
* nagios:hostgroup:add
* nagios:restart
* nagios:service:add
* nagios:servicegroup:add
* plesk:customer:add
* plesk:database:add
* plesk:subscription:add
* sf:init
* sf2:init

Installation
------------

    cd ~
    git clone git://github.com/JoshuaEstes/ServerTools.git
    cd ServerTools
    curl -s http://getcomposer.org/installer | php
    ./composer.phar install
    ln -s $HOME/ServerTools/st $HOME/bin/st

Updating ServerTools
--------------------

    cd ~/ServerTools
    git pull origin master
    ./composer.phar update

Adding more tasks/tools
-----------------------

Let's make a simple hello world task.

    <?php
    namespace Hello;

    // src/Hello/WorldCommand.php

    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Output\OutputInterface;

    class WorldCommand extends Command
    {

      /**
       * Configures the current command.
       */
      protected function configure(){
        $this->setName('hello:world')
          ->setDescription('Tells the world hello');
      }

      /**
       * Execute the command
       *
       * @param InputInterface $input
       * @param OutputInterface $output
       */
      protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln('Hello World');
        return 0;
      }
    }

And that's all there is to it =) Feel free to take a look at the other tasks and
see how they are setup.

Testing
-------

All tests are located in the "Tests" folder and can be run with phpunit. If you
create a command, then please include a test.

Be sure that you run:

    ./composer.phar install --dev

Documentation
-------------

Documentation is located in the docs folder and is also used to output on the
command line. There is a template file in the root which you can use. If you
want to set this as your files help for the command, then just add this code
snippet to your configure function in your command.

    $this->setHelp(\file_get_contents(ST_DOCS_DIR . '/path/to/file'));