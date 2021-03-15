<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;

/**
 * Guzzle6 Transport.
 */
class Guzzle6Transport implements TransportInterface
{
    private ClientInterface $client;

    public function __construct(?ClientInterface $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function executeRequest(string $uri, string $body): string
    {
        $request = new Request('POST', $uri, ['Content-Type' => 'application/x-www-form-urlencoded'], $body);
        $response = $this->client->send($request);

        return (string) $response->getBody();
    }
}
