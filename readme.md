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
    chmod +x server-tool
    ./server-tool
    sudo ln -s /path/to/server-tool /sbin/server-tool

Adding more tasks/tools
-----------------------

Check out https://github.com/JoshuaEstes/stHelloWorld for some help. Also
check this projects composer.json file to see how I am telling it to use
that repository. I am going to assume that most of your tools are going
to be private repositories.

Notes
-----

It might be a good idea to fork this repo to your own place and make it private
in case you want to modify the composer.json file to include private tools used
by you or your own company.

If your company has a private git server (gitosis or gitolite) then you can add
another git repository. You can even head over to bitbucket.org and get free
private repos there.

Example

    git remote add bitbucket git@bitbucket.com:username/servertools.git

This will let you pull updates "git pull origin master" while you maintain
your own stuff.




