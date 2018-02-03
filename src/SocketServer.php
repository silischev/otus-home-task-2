<?php

namespace Asil\Otus\HomeTask_2;

use Asil\Otus\HomeTask_2\Exceptions\SocketException;
use Asil\Otus\HomeTask_2\Services\SignalHandleService;
use Asil\Otus\HomeTask_2\Services\SocketDataValidationService;

class SocketServer implements SocketInterface
{
    /**
     * AbstractSocketServer host
     *
     * @var string
     */
    private $host;

    /**
     * AbstractSocketServer protocol
     *
     * @var int
     */
    private $protocol;

    /**
     * AbstractSocketServer port
     *
     * @var int
     */
    private $port;

    /**
     * AbstractSocketServer socket
     *
     * @var resource
     */
    private $socket;

    /**
     * Function that trigger on send message bu socket client
     *
     * @var callable
     */
    private $onClientSendMessageHandler;

    /**
     * AbstractSocketServer clients
     *
     * @var resource[]
     */
    private $clients = [];

    /**
     * AbstractSocketServer socketsStorage
     *
     * @var resource[]
     */
    private $socketsStorage;

    /**
     * AbstractSocketServer maxByteReadLength
     * @var int
     */
    private $maxByteReadLength = 2048;

    /**
     * SocketServer constructor.
     *
     * @param string $host
     * @param int $port
     * @param callable $onClientSendMessageHandler
     */
    public function __construct(string $host, int $port, callable $onClientSendMessageHandler)
    {
        $this->protocol = SocketDataValidationService::getProtocolVersionByHost($host);
        $this->host = $host;
        $this->port = $port;
        $this->onClientSendMessageHandler = $onClientSendMessageHandler;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Run server
     *
     * @throws \Exception
     */
    public function run()
    {
        try {
            $this->buildSocket();

            declare(ticks = 1);
            SignalHandleService::sighupHandle($this);

            do {
                pcntl_signal_dispatch();
                $this->loop();
            } while (true);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        } finally {
            $this->cleanResources();
        }
    }

    /**
     * Create a socket
     *
     * @return $this
     *
     * @throws SocketException
     */
    public function create()
    {
        set_time_limit(0);
        $this->socket = socket_create($this->protocol, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            throw new SocketException('Couldn`t create socket: ');
        }

        return $this;
    }

    /**
     * Set nonblock mode for socket
     *
     * @return $this
     *
     * @throws SocketException
     */
    public function setNonBlock()
    {
        if (socket_set_nonblock($this->socket) === false) {
            throw new SocketException('Couldn`t set nonblock mode for socket: ');
        }

        return $this;
    }

    /**
     * Binds a name to a socket
     *
     * @return $this
     *
     * @throws SocketException
     */
    public function bind()
    {
        if (socket_bind($this->socket, $this->host, $this->port) === false) {
            throw new SocketException('Couldn`t bind socket: ');
        }

        return $this;
    }

    /**
     * Listens for a connection on a socket
     *
     * @return $this
     *
     * @throws SocketException
     */
    public function listen()
    {
        if (socket_listen($this->socket) === false) {
            throw new SocketException('Couldn`t listen socket: ');
        }

        return $this;
    }

    /**
     * Select array of sockets and add accept connection to clients socket storage
     *
     * @return void
     *
     * @throws SocketException
     */
    public function select()
    {
        $this->socketsStorage = [];
        $write = null;
        $except = null;
        $timeout = 5;

        if (is_resource($this->socket)) {
            $this->socketsStorage[] = $this->socket;
            $this->socketsStorage = array_merge($this->socketsStorage, $this->clients);

            if (socket_select($this->socketsStorage, $write, $except, $timeout) === false) {
                throw new SocketException('Couldn`t accept array of sockets: ');
            }

            if (in_array($this->socket, $this->socketsStorage)) {
                $this->clients[] = $this->accept();
            }
        }
    }

    /**
     * Accepts a connection on a socket
     *
     * @return resource
     *
     * @throws SocketException
     */
    public function accept()
    {
        $spawn = socket_accept($this->socket);

        if ($spawn === false) {
            throw new SocketException('Couldn`t accept socket: ');
        }

        return $spawn;
    }

    /**
     * Reads a maximum of length bytes from a socket
     *
     * @param  resource $client
     *
     * @return string
     *
     * @throws SocketException
     */
    public function read($client)
    {
        $input = socket_read($client, $this->maxByteReadLength);

        if ($input === false) {
            throw new SocketException('Could not read input: ');
        }

        return trim($input);
    }

    /**
     * Write to a socket
     *
     * @param resource $client
     * @param string $output
     *
     * @throws SocketException
     */
    public function write($client, $output)
    {
        $out = socket_write($client, $output, strlen($output));

        if ($out === false) {
            throw new SocketException('Could not write output: ');
        }
    }

    /**
     * Closes a socket resource
     *
     * @param resource $client
     */
    public function close($client)
    {
        if (is_resource($client)) {
            socket_shutdown($client);
            socket_close($client);
        }
    }

    /**
     * @param int $length
     *
     * @return $this
     */
    public function setMaxByteReadLength(int $length)
    {
        $this->maxByteReadLength = $length;

        return $this;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return $this
     *
     * @throws SocketException
     */
    private function buildSocket()
    {
        $this
            ->create()
            ->bind()
            ->listen()
            ->setNonBlock();

        return $this;
    }

    /**
     * Process new connections
     *
     * @throws SocketException
     */
    private function loop()
    {
        $this->select();

        foreach ($this->clients as $key => $client) {
            if (in_array($client, $this->socketsStorage)) {
                $input = $this->read($client);

                if ($input !== 'exit') {
                    $result = ($this->onClientSendMessageHandler)($input);

                    if (!empty($result)) {
                        $this->write($client, $result . PHP_EOL);
                    }
                } else {
                    $this->close($client);
                    unset($this->clients[$key]);
                    unset($this->socketsStorage[$key]);
                    break;
                }
            }
        }
    }

    /**
     * Clean all active resources
     */
    public function cleanResources()
    {
        if (!empty($this->clients)) {
            foreach ($this->clients as $client) {
                $this->close($client);
            }

            unset($this->clients);
        }

        $this->close($this->socket);
        unset($this->socket);
    }

}