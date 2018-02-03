<?php

namespace Asil\Otus\HomeTask_2\Services;

use Asil\Otus\HomeTask_1_1\SimpleBracketsProcessor;
use Asil\Otus\HomeTask_2\Exceptions\SocketException;
use Asil\Otus\HomeTask_2\SocketServer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class SocketServerConfigurationService
{
    private $host;
    private $port;

    public function __construct()
    {
        $config = Yaml::parseFile(__DIR__ . DIRECTORY_SEPARATOR . '../../configs/config.yaml');

        $this->host = $config['host'];
        $this->port = (int) $config['port'];
    }

    public function build(OutputInterface $output = null)
    {
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
            $server = new SocketServer($this->host, $this->port, $onClientSendMessageHandlerFunction);
            $server->run();
        } catch (SocketException|\Throwable $e) {
            if (!empty($output)) {
                $output->writeln($e->getMessage());
            } else {
                fwrite(STDOUT, $e->getMessage());
            }
        }
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }
}