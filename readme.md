Server Tools
============

ServerTools is a tool for developers and sys admins to make better use of their
time by putting this script in their PATH and allowing the tasks to do the work
for them. Setting up a vhost file for a new project? Managing a Nagios server?
The list can go on and on.

Installation
------------

    cd ~
    git clone git://github.com/JoshuaEstes/ServerTools.git
    cd ServerTools
    curl -s http://getcomposer.org/installer | php
    ./composer.phar install
    chmod +x st
    ./st
    sudo ln -s /path/to/st /sbin/st

If you want to allow all users on your system to use this, then symlink
this to /usr/bin directoy

    sudo ln -s /path/to/st /usr/bin/st

This repo comes with an optional autocompletion script for this tool. If you
want to have autocompletion, then place the file st-autocompletion.bash in
either a directory that where the file will get loaded or put the file some
place and add a few lines in your bashrc to source it.

Adding more tasks/tools
-----------------------

Check out https://github.com/JoshuaEstes/stHelloWorld for some help. Also
check this projects composer.json file to see how I am telling it to use
that repository. I am going to assume that most of your tools are going
to be private repositories.

