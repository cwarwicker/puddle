<?php

namespace Puddle;

use Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Class for handling CLI commands
 */
class Console
{

    /**
     * @var Config Config object
     */
    protected Config $config;

    /**
     * @var Application The console application
     */
    protected Application $app;

    /**
     * Construct the object
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
        $this->app = new Application();
    }

    /**
     * Add command to the console
     * @param Command $command
     * @return void
     */
    public function addCommand(Command $command): void {
        $this->app->add($command);
    }

    /**
     * Run the CLI console
     * @return void
     * @throws Exception
     */
    public function run(): void {
        $this->app->run();
    }

}