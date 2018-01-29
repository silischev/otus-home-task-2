<?php

namespace Asil\Otus\HomeTask_2\ConsoleCommands;

use Asil\Otus\HomeTask_1_1\SimpleBracketsProcessor;
use Asil\Otus\HomeTask_2\SocketServer;
use Asil\Otus\HomeTask_2\Exceptions\SocketException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
        $config = Yaml::parseFile(__DIR__ . DIRECTORY_SEPARATOR . '../../configs/config.yaml');

        $host = $config['host'];
        $port = $config['port'];

        $onClientSendMessageHandlerFunction = (function ($message) {
            $result = null;

            try {
                $bracketsProcessor = new SimpleBracketsProcessor();
                $result = $bracketsProcessor->isValidBracketLine($message) ? 'String is valid' : 'String is invalid';
            } catch (\Throwable $e) {
                $result = $e->getMessage();
            }

            return $result;
        });

        try {
            $server = new SocketServer($host, (int) $port, $onClientSendMessageHandlerFunction);
            $server->run();
        } catch (SocketException|\Throwable $e) {
            $output->writeln($e->getMessage());
        }

        return 1;
    }
}