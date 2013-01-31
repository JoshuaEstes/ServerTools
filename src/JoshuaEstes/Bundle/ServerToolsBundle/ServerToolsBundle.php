<?php

namespace JoshuaEstes\Bundle\ServerToolsBundle;

use Symfony\Component\Console\Application;

/**
 * This is the main bundle that is used by ServerTools
 */
class ServerToolsBundle
{

    /**
     * Every bundle must have this function, this function
     * allows you to register commands with the application
     *
     * @param Application $application
     */
    public function registerCommands(Application $application)
    {
        $application->add(new \JoshuaEstes\Bundle\ServerToolsBundle\Command\HostsCommand());
    }

}
