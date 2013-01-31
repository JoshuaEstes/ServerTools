<?php

namespace JoshuaEstes\Bundle\ServerToolsBundle\Console;

use Symfony\Component\Console\Application as BaseApplication;

/**
 */
class Application extends BaseApplication
{

    private $kernel;

    /**
     * @return AppKernel
     */
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
        parent::__construct('ServerTools', '*');
        foreach ($this->kernel->registerBundles() as $bundle) {
            $bundle->registerCommands($this);
        }
    }

    /**
     * @return AppKernel
     */
    public function getKernel()
    {
        return $this->kernel;
    }

}
