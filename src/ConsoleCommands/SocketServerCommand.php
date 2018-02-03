<?php

namespace Asil\Otus\HomeTask_2\ConsoleCommands;

use Asil\Otus\HomeTask_2\Services\SocketServerConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SocketServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverConfigurationService = new SocketServerConfigurationService();
        $serverConfigurationService->build($output);

        return 1;
    }
}