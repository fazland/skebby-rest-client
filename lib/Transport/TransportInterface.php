<?php

namespace Fazland\SkebbyRestClient\Transport;

interface TransportInterface
{
    /**
     * Performs an HTTP request to $uri
     *
     * @param string $uri
     * @param string $body
     *
     * @return string
     */
    public function executeRequest($uri, $body);
}
