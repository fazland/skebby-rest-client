<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Psr7ClientTransport implements TransportInterface
{
    private ClientInterface $client;
    private RequestFactoryInterface $messageFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $messageFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->streamFactory = $streamFactory;
    }

    public function executeRequest(string $uri, string $body): string
    {
        $request = $this->messageFactory
            ->createRequest('POST', $uri)
            ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withBody($this->streamFactory->createStream($body));
        $response = $this->client->sendRequest($request);

        return (string) $response->getBody();
    }
}
