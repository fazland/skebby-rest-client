<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use Fazland\SkebbyRestClient\Exception\RuntimeException;
use Fazland\SkebbyRestClient\Runtime\Runtime;
use Fazland\SkebbyRestClient\Runtime\RuntimeInterface;
use GuzzleHttp\Client;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Transport Factory.
 */
class Factory
{
    /**
     * Creates the transport based on which classes are defined.
     *
     * @throws RuntimeException
     */
    public static function createTransport(?RuntimeInterface $runtime = null): TransportInterface
    {
        $runtime ??= new Runtime();

        if ($runtime->classExists(Psr18ClientDiscovery::class) && $runtime->classExists(Psr17FactoryDiscovery::class)) {
            try {
                return new Psr7ClientTransport(
                    Psr18ClientDiscovery::find(),
                    Psr17FactoryDiscovery::findRequestFactory(),
                    Psr17FactoryDiscovery::findStreamFactory()
                );
            } catch (NotFoundException $e) {
                // @ignoreException
            }
        }

        if ($runtime->classExists(HttpClient::class)) {
            return new SymfonyHttpClientTransport();
        }

        if ($runtime->classExists(Client::class)) {
            return new Guzzle6Transport();
        }

        if ($runtime->extensionLoaded('curl')) {
            return new CurlExtensionTransport();
        }

        throw new RuntimeException('Cannot create an HTTP transport object');
    }
}
