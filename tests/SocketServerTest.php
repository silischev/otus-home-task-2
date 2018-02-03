<?php

namespace Asil\Otus\HomeTask_2;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SocketServerTest extends TestCase
{
    const HOST = '127.0.0.1';
    const PORT = 1234;

    /**
     * @var SocketServer
     */
    private $server;

    public function testSocketCreation()
    {
        $onClientSendMessageHandler = (function () {
            return;
        });

        $this->server = new SocketServer(self::HOST, self::PORT, $onClientSendMessageHandler);

        $method = $this->getPrivateMethod(SocketServer::class, 'buildSocket');
        $method->invokeArgs($this->server, []);

        $this->assertSame(is_resource(($this->server->getSocket())), true);
        $this->server->cleanResources();
    }

    public function testServerInvalidHostException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $onClientSendMessageHandler = (function () {
            return;
        });

        $this->server = new SocketServer('127.0.', self::PORT, $onClientSendMessageHandler);
    }

    /**
     * @param string $className
     * @param string $methodName
     *
     * @return \ReflectionMethod
     */
    public function getPrivateMethod(string $className, string $methodName)
    {
        $reflector = new ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}