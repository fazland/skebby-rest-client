<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

/**
 * Transport interface.
 */
interface TransportInterface
{
    /**
     * Performs an HTTP request to $uri.
     */
    public function executeRequest(string $uri, string $body): string;
}
