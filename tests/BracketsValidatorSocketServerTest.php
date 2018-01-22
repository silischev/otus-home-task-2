<?php

namespace Asil\Otus\HomeTask_2;

use PHPUnit\Framework\TestCase;

class BracketsValidatorSocketServerTest extends TestCase
{
    const HOST = '127.0.0.1';
    const PORT = 1234;

    /**
     * @var SocketServer
     */
    private $server;

    public function testServerConfigure()
    {
        $this->server = new BracketsValidatorSocketServer(self::HOST, self::PORT);
        $socket = $this->server->create();
        $this->server->bind();
        $this->server->listen();

        $this->assertSame($this->server->getSocket(), $socket);
        $this->server->cleanResources();
    }

    public function testServerInvalidHostException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->server = new BracketsValidatorSocketServer('127.0.', self::PORT);
    }
}
