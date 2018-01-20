<?php

namespace Asil\Otus\HomeTask_2;

interface SocketInterface
{
    public function create();
    public function bind();
    public function listen();
    public function accept();
    public function select();
    public function read($socket);
    public function write($socket, $output);
    public function close($socket);
}