<?php

declare(strict_types=1);

namespace Tests\Transport;

use Fazland\SkebbyRestClient\Exception\RuntimeException;
use Fazland\SkebbyRestClient\Runtime\RuntimeInterface;
use Fazland\SkebbyRestClient\Transport\CurlExtensionTransport;
use Fazland\SkebbyRestClient\Transport\Factory;
use Fazland\SkebbyRestClient\Transport\Guzzle6Transport;
use Fazland\SkebbyRestClient\Transport\Psr7ClientTransport;
use GuzzleHttp\Client;
use Http\Discovery\ClassDiscovery;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateTransportShouldTryAutoDiscovery(): void
    {
        $transport = Factory::createTransport(new class implements RuntimeInterface {
            public function classExists(string $fqcn, bool $autoload = true): bool
            {
                return $fqcn === Psr18ClientDiscovery::class || $fqcn === Psr17FactoryDiscovery::class;
            }

            public function extensionLoaded(string $extension): bool
            {
            }
        });

        self::assertInstanceOf(Psr7ClientTransport::class, $transport);
    }

    public function testCreateTransportShouldTestForGuzzle(): void
    {
        $transport = Factory::createTransport(new class implements RuntimeInterface {
            public function classExists(string $fqcn, bool $autoload = true): bool
            {
                return $fqcn === Client::class;
            }

            public function extensionLoaded(string $extension): bool
            {
            }
        });

        self::assertInstanceOf(Guzzle6Transport::class, $transport);
    }

    public function testCreateTransportShouldFallbackToAnotherStrategyIfAutodiscoveryThrows(): void
    {
        $strategies = ClassDiscovery::getStrategies();

        try {
            ClassDiscovery::setStrategies([]);

            $transport = Factory::createTransport(new class implements RuntimeInterface {
                public function classExists(string $fqcn, bool $autoload = true): bool
                {
                    return $fqcn === Psr18ClientDiscovery::class || $fqcn === Psr17FactoryDiscovery::class;
                }

                public function extensionLoaded(string $extension): bool
                {
                    return $extension === 'curl';
                }
            });

            self::assertInstanceOf(CurlExtensionTransport::class, $transport);
        } finally {
            ClassDiscovery::setStrategies($strategies);
        }
    }

    public function testCreateTransportShouldThrowIfCannotCreateATransportClass(): void
    {
        $this->expectException(RuntimeException::class);

        Factory::createTransport(new class implements RuntimeInterface {
            public function classExists(string $fqcn, bool $autoload = true): bool
            {
                return false;
            }

            public function extensionLoaded(string $extension): bool
            {
                return false;
            }
        });
    }
}
