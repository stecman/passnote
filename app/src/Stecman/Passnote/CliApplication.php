<?php

namespace Stecman\Passnote;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Helper\HelperSet;

class CliApplication extends BaseApplication
{
    /**
     * Set the name and version (with the version a dummy var to be swapped out by the compiler)
     */
    public function __construct()
    {
        parent::__construct('Passnote', 'v3.0');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\AddCommand();
        $commands[] = new Command\ViewCommand();

        return $commands;
    }

    /**
     * Return the default helper set
     * @return \Symfony\Component\Console\Helper\HelperSet
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet();
    }
}
