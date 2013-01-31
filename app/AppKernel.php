<?php

/**
 * Main API for developers to work with for adding new commands
 * to the application
 */
class AppKernel
{

    /**
     * If you are including a new bundle, you will need to do this here
     *
     * @see JoshuaEstes\Bundle\ServerToolsBundle\ServerToolsBundle
     *
     * @return array
     */
    public function registerBundles()
    {
        $bundles = array(
            new JoshuaEstes\Bundle\ServerToolsBundle\ServerToolsBundle(),
        );
        return $bundles;
    }

}
