Server Tools
============

ServerTools is a command line utility that allows a developer to setup a new
project with little time setting up the extra stuff such as the apache vhost
commands and such. With this utility placed on various servers, you can ssh in
to setup apache, nagios, plesk, etc. The list goes on as well as giving you the
ability to create your own commands.

Installation
------------

    cd ~
    git clone git://github.com/JoshuaEstes/ServerTools.git
    cd ServerTools
    curl -s http://getcomposer.org/installer | php
    ./composer.phar install
    ln -s $HOME/ServerTools/st $HOME/bin/st

Adding more tasks/tools
-----------------------

Check out https://github.com/JoshuaEstes/stHelloWorld for some help. Also
check this projects composer.json file to see how I am telling it to use
that repository. I am going to assume that most of your tools are going
to be private repositories.
