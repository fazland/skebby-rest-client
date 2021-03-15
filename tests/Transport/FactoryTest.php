<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Tests\Transport;

use Fazland\SkebbyRestClient\Transport\CurlExtensionTransport;
use Fazland\SkebbyRestClient\Transport\Factory;
use Fazland\SkebbyRestClient\Transport\Guzzle6Transport;
use Fazland\SkebbyRestClient\Transport\HttpClientTransport;
use GuzzleHttp\Client;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Kcs\FunctionMock\PhpUnit\FunctionMockTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

/**
 * @runTestsInSeparateProcesses
 */
class FactoryTest extends TestCase
{
    use FunctionMockTrait;

    public function testCreateTransportShouldTryAutoDiscovery()
    {
        $namespace = $this->prophesizeForFunctions(Factory::class);
        $namespace->class_exists(HttpClientDiscovery::class)->willReturn(true);
        $namespace->class_exists(MessageFactoryDiscovery::class)->willReturn(true);

        $transport = Factory::createTransport();
        self::assertInstanceOf(HttpClientTransport::class, $transport);
    }

    public function testCreateTransportShouldTestForGuzzle()
    {
        $namespace = $this->prophesizeForFunctions(Factory::class);
        $namespace->class_exists(HttpClientDiscovery::class)->willReturn(false);
        $namespace->class_exists(MessageFactoryDiscovery::class)->willReturn(false);

        $namespace->class_exists(Client::class)->willReturn(true);

        $transport = Factory::createTransport();
        self::assertInstanceOf(Guzzle6Transport::class, $transport);
    }

    public function testCreateTransportShouldFallbackToAnotherStrategyIfAutodiscoveryThrows()
    {
        $namespace = $this->prophesizeForFunctions(Factory::class);
        $namespace->class_exists(HttpClientDiscovery::class)->willReturn(true);
        $namespace->class_exists(MessageFactoryDiscovery::class)->willReturn(true);

        $factoryNs = $this->prophesizeForFunctions(HttpClientDiscovery::class);
        $factoryNs->class_exists(Argument::any())->willReturn(false);

        $namespace->class_exists(Client::class)->shouldBeCalled()->willReturn(false);

        $transport = Factory::createTransport();
        self::assertInstanceOf(CurlExtensionTransport::class, $transport);
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\RuntimeException
     */
    public function testCreateTransportShouldThrowIfCannotCreateATransportClass()
    {
        $namespace = $this->prophesizeForFunctions(Factory::class);
        $namespace->class_exists(HttpClientDiscovery::class)->willReturn(true);
        $namespace->class_exists(MessageFactoryDiscovery::class)->willReturn(true);

        $factoryNs = $this->prophesizeForFunctions(HttpClientDiscovery::class);
        $factoryNs->class_exists(Argument::any())->willReturn(false);

        $namespace->class_exists(Client::class)->shouldBeCalled()->willReturn(false);
        $namespace->extension_loaded('curl')->shouldBeCalled()->willReturn(false);

        Factory::createTransport();
    }
}
