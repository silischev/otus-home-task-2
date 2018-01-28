<?php

namespace Asil\Otus\HomeTask_2\Services;

use InvalidArgumentException;

class SocketDataValidationService
{
    /**
     * @param string $host
     * @throws InvalidArgumentException
     * @return int
     */
    public static function getProtocolVersionByHost(string $host): int
    {
        $protocol = null;

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $protocol = AF_INET;
        } elseif (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $protocol = AF_INET6;
        } else {
            throw new InvalidArgumentException('Invalid IP. Protocol must be IPv4 or IPv6 type');
        }

        return $protocol;
    }
}