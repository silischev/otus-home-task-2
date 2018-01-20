<?php

namespace Asil\Otus\HomeTask_2;

use Asil\Otus\HomeTask_1_1\SimpleBracketsProcessor;
use Asil\Otus\HomeTask_2\Exceptions\SocketException;
use Asil\Otus\HomeTask_2\Services\SocketDataValidationService;

class SocketServer implements SocketInterface
{
    private $host;
    private $protocol;
    private $port;
    private $socket;
    private $clients = [];
    private $socketsStorage;
    private $maxByteReadLength = 2048;

    /**
     * SocketServer constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port)
    {
        $this->protocol = SocketDataValidationService::getProtocolVersionByHost($host);
        $this->host = $host;
        $this->port = $port;
    }

    public function run()
    {
        $this->socket = $this->create();
        $this->bind();
        $this->listen();

        do {
            $this->loop();
        } while (true);

        $this->cleanResources();
    }

    /**
     * @return resource
     * @throws SocketException
     */
    public function create()
    {
        set_time_limit(0);
        $this->socket = @socket_create($this->protocol, SOCK_STREAM, SOL_TCP);

        if ($this->socket === false) {
            throw new SocketException('Couldn`t create socket: ' . socket_strerror(socket_last_error()));
        }

        return $this->socket;
    }

    /**
     * @return void
     * @throws SocketException
     */
    public function bind()
    {
        if (@socket_bind($this->socket, $this->host, $this->port) === false) {
            throw new SocketException('Couldn`t bind socket: ' . socket_strerror(socket_last_error()));
        }
    }

    /**
     * @return void
     * @throws SocketException
     */
    public function listen()
    {
        if (@socket_listen($this->socket) === false) {
            throw new SocketException('Couldn`t listen socket: ' . socket_strerror(socket_last_error()));
        }
    }

    /**
     * @return void
     * @throws SocketException
     */
    public function select()
    {
        $this->socketsStorage = [];
        $write = null;
        $except = null;
        $timeout = 5;

        $this->socketsStorage[] = $this->socket;
        $this->socketsStorage = array_merge($this->socketsStorage, $this->clients);

        if (@socket_select($this->socketsStorage, $write, $except, $timeout) === false) {
            throw new SocketException('Couldn`t accept array of sockets: ' . socket_strerror(socket_last_error()));
        }

        if (in_array($this->socket, $this->socketsStorage)) {
            $this->clients[] = $this->accept();
        }
    }

    /**
     * @return resource
     * @throws SocketException
     */
    public function accept()
    {
        $spawn = @socket_accept($this->socket);

        if ($spawn === false) {
            throw new SocketException('Couldn`t accept socket: ' . socket_strerror(socket_last_error()));
        }

        return $spawn;
    }

    /**
     * @param  resource $client
     * @return string
     * @throws SocketException
     */
    public function read($client)
    {
        $input = @socket_read($client, $this->maxByteReadLength);

        if ($input === false) {
            throw new SocketException('Could not read input: ' . socket_strerror(socket_last_error()));
        }

        return trim($input);
    }

    /**
     * @param resource $client
     * @param string $output
     * @throws SocketException
     */
    public function write($client, $output)
    {
        $out = @socket_write($client, $output, strlen($output)) or die('Could not write output' . PHP_EOL);

        if ($out === false) {
            throw new SocketException('Could not write output: ' . socket_strerror(socket_last_error()));
        }
    }

    /**
     * @param resource $client
     */
    public function close($client)
    {
        socket_close($client);
    }

    /**
     * @param int $length
     */
    public function setMaxByteReadLength(int $length)
    {
        $this->maxByteReadLength = $length;
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @throws SocketException
     */
    private function loop()
    {
        $this->select();

        foreach ($this->clients as $key => $client) {
            if (in_array($client, $this->socketsStorage)) {
                $input = $this->read($client);

                if ($input !== 'exit') {
                    $this->write($client, $this->bracketStringValidation($input) . PHP_EOL);
                } else {
                    $this->closeClientSocket($key, $client);
                    break;
                }
            }
        }
    }

    /**
     * @param $key
     * @param resource $client
     */
    private function closeClientSocket($key, $client)
    {
        unset($this->clients[$key]);
        $this->close($client);
    }

    public function cleanResources()
    {
        if (!empty($this->clients)) {
            foreach ($this->clients as $key => $client) {
                $this->closeClientSocket($key, $client);
            }
        }

        $this->close($this->socket);
    }

    /**
     * @param string $line
     * @return string
     */
    private function bracketStringValidation(string $line): string
    {
        $msg = '';

        try {
            $bracketsProcessor = new SimpleBracketsProcessor();
            $msg = $bracketsProcessor->isValidBracketLine($line) ? 'String is valid' : 'String is invalid';
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
        }

        return $msg;
    }

}