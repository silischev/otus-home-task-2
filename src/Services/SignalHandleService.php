<?php

namespace Asil\Otus\HomeTask_2\Services;

use Asil\Otus\HomeTask_2\SocketServer;

class SignalHandleService
{
    /**
     * @param SocketServer $server
     */
    public static function sighupHandle(SocketServer $server)
    {
        pcntl_signal(SIGHUP, function () use ($server) {
            $serverConfigurationService = new SocketServerConfigurationService();

            if ($server->getPort() !== $serverConfigurationService->getPort()) {
                $server->cleanResources();
                $serverConfigurationService->build();
            }
        });
    }
}