<?php
declare(strict_types=1);

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
     */
    public function executeRequest(string $uri, string $body): string;
}
