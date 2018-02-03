<?php

namespace Asil\Otus\HomeTask_2;

interface SocketInterface
{
    public function create();
    public function bind();
    public function listen();
    public function accept();
    public function select();

    /**
     * @param resource $socket
     *
     * @return string
     */
    public function read($socket);

    /**
     * @param resource $socket
     * @param string $output
     *
     * @return string
     */
    public function write($socket, $output);
    public function close($socket);
}