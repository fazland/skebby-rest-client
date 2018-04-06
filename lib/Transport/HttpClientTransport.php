<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;

/**
 * Http Client Transport.
 *
 * @author Alessandro Chitolina <alessandro.chitolina@fazland.com>
 */
class HttpClientTransport implements TransportInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(HttpClient $client, MessageFactory $messageFactory)
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function executeRequest(string $uri, string $body): string
    {
        $request = $this->messageFactory->createRequest('POST', $uri, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $body);
        $response = $this->client->sendRequest($request);

        return (string) $response->getBody();
    }
}
