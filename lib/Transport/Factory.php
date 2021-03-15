<?php
declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Transport;

use Fazland\SkebbyRestClient\Exception\RuntimeException;
use GuzzleHttp\Client;
use Http\Discovery\Exception\NotFoundException;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;

/**
 * Transport Factory.
 *
 * @author Alessandro Chitolina <alessandro.chitolina@fazland.com>
 */
class Factory
{
    /**
     * Creates the transport based on which classes are defined.
     *
     * @throws RuntimeException
     */
    public static function createTransport(): TransportInterface
    {
        if (class_exists(HttpClientDiscovery::class) && class_exists(MessageFactoryDiscovery::class)) {
            try {
                return new HttpClientTransport(HttpClientDiscovery::find(), MessageFactoryDiscovery::find());
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        if (class_exists(Client::class)) {
            return new Guzzle6Transport();
        }

        if (extension_loaded('curl')) {
            return new CurlExtensionTransport();
        }

        throw new RuntimeException('Cannot create an HTTP transport object');
    }
}
