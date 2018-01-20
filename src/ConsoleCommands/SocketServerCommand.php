<?php

namespace Asil\Otus\HomeTask_2\ConsoleCommands;

use Asil\Otus\HomeTask_2\Exceptions\SocketException;
use Asil\Otus\HomeTask_2\SocketServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SocketServerCommand extends Command
{
    protected function configure()
    {
        $this->setName('server');
        $this->addArgument('host', InputArgument::REQUIRED, 'Please enter host');
        $this->addArgument('port', InputArgument::REQUIRED, 'Please enter connection port number');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');
        $port = $input->getArgument('port');

        try {
            $server = new SocketServer($host, (int) $port);
            $server->run();
        } catch (SocketException|\Throwable $e) {
            $output->writeln($e->getMessage());
        }

        return 1;
    }
}