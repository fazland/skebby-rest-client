<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SymfonyHttpClientTransport implements TransportInterface
{
    private HttpClientInterface $client;

    public function __construct(?HttpClientInterface $client = null)
    {
        $this->client = $client ?: HttpClient::create();
    }

    public function executeRequest(string $uri, string $body): string
    {
        $response = $this->client->request('POST', $uri, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded' ],
            'body' => $body,
        ]);

        return $response->getContent();
    }
}
