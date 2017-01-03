<?php

namespace Fazland\SkebbyRestClient\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Guzzle6Transport implements TransportInterface
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    /**
     * {@inheritdoc}
     */
    public function executeRequest($uri, $body)
    {
        $request = new Request('POST', $uri, [], $body);
        $response = $this->client->send($request);

        return (string) $response->getBody();
    }
}
