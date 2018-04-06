<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

/**
 * Transport interface.
 *
 * @author Alessandro Chitolina <alessandro.chitolina@fazland.com>
 */
interface TransportInterface
{
    /**
     * Performs an HTTP request to $uri.
     *
     * @param string $uri
     * @param string $body
     *
     * @return string
     */
    public function executeRequest(string $uri, string $body): string;
}
