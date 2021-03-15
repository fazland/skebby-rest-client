<?php

declare(strict_types=1);

namespace Tests\Client;

use DateInterval;
use DateTimeImmutable;
use Fazland\SkebbyRestClient\Client\Client;
use Fazland\SkebbyRestClient\Clock\FrozenClock;
use Fazland\SkebbyRestClient\Constant\Charsets;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Fazland\SkebbyRestClient\Event\SmsMessageSent;
use Fazland\SkebbyRestClient\Exception\EmptyResponseException;
use Fazland\SkebbyRestClient\Exception\NoRecipientsSpecifiedException;
use Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException;
use Fazland\SkebbyRestClient\Transport\CurlExtensionTransport;
use Fazland\SkebbyRestClient\Transport\Psr7ClientTransport;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ClientTest extends TestCase
{
    use ProphecyTrait;

    private const RESPONSE_WITHOUT_STATUS =
    '<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic_report><remaining_sms>5</remaining_sms><id>1477056680</id></test_send_sms_classic_report></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    private const RESPONSE_FAIL =
    '<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic><response><code>11</code><message>Unknown charset, use ISO-8859-1 or UTF-8</message></response><status>failed</status></test_send_sms_classic></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    private const RESPONSE_SUCCESS =
    '<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic_report><remaining_sms>5</remaining_sms><id>1477056680</id><status>success</status></test_send_sms_classic_report></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    /** @var ObjectProphecy|ClientInterface */
    private ObjectProphecy $client;

    /** @var ObjectProphecy|RequestFactoryInterface */
    private ObjectProphecy $requestFactory;

    /** @var ObjectProphecy|StreamFactoryInterface */
    private ObjectProphecy $streamFactory;

    /** @var ObjectProphecy|EventDispatcherInterface */
    private ObjectProphecy $eventDispatcher;

    private array $config;
    private Client $skebbyRestClient;

    protected function setUp(): void
    {
        $this->config = [
            'username' => 'test',
            'password' => 'test',
            'sender' => '+393333333333',
            'method' => SendMethods::CLASSIC,
            'endpoint_uri' => Endpoints::REST_HTTPS,
            'charset' => Charsets::UTF8,
        ];

        $this->client = $this->prophesize(ClientInterface::class);
        $this->requestFactory = $this->prophesize(RequestFactoryInterface::class);
        $this->streamFactory = $this->prophesize(StreamFactoryInterface::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->skebbyRestClient = new Client($this->config, new Psr7ClientTransport(
            $this->client->reveal(),
            $this->requestFactory->reveal(),
            $this->streamFactory->reveal()
        ), $this->eventDispatcher->reveal());

        $this->streamFactory->createStream(Argument::any())->willReturn($stream = Stream::create());
        $this->requestFactory->createRequest('POST', Endpoints::REST_HTTPS)
            ->willReturn($request = new Request('POST', Endpoints::REST_HTTPS));
    }

    private function getSmsWithRecipients(): Sms
    {
        return Sms::create()
            ->setRecipients([
                '+393473322444',
                '+393910000000',
            ])
            ->setText('Some text');
    }

    private function getSmsWithRecipientsAndRecipientsVariables(): Sms
    {
        return Sms::create()
            ->setRecipients([
                '+393473322444',
                '+393910000000',
            ])
            ->setRecipientVariables('+393473322444', [
                'FirstName' => 'This is a first name',
                'LastName' => 'This is a last name',
                'Infos' => 'These are infos',
            ])
            ->setRecipientVariables('+393910000000', [
                'FirstName' => 'This is another first name',
                'LastName' => 'This is another last name',
                'Infos' => 'These are other infos',
            ])
            ->setText('Some text');
    }

    public function testSendShouldThrowNoRecipientSpecifiedExceptionOnEmptyRecipient(): void
    {
        $this->expectException(NoRecipientsSpecifiedException::class);
        $sms = Sms::create()->setText('some text');
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldThrowEmptyResponseExceptionOnEmptyResponse(): void
    {
        $this->expectException(EmptyResponseException::class);
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response());

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldThrowUnknownErrorResponseExceptionOnResponseWithoutStatus(): void
    {
        $this->expectException(UnknownErrorResponseException::class);
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_WITHOUT_STATUS));

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldReturnFailingResponseOnUnrecognizedCharset(): void
    {
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_FAIL));

        $sms = Sms::create()
            ->addRecipient('+393930000123')
            ->setText('Hey mate');

        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            self::assertInstanceOf(Response::class, $response);
            self::assertEquals('failed', $response->getStatus());
        }
    }

    public function testSendShouldReturnResponses(): void
    {
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $this->eventDispatcher->dispatch(Argument::type(SmsMessageSent::class))
            ->shouldBeCalledTimes(1);

        $sms = $this->getSmsWithRecipients();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            self::assertInstanceOf(Response::class, $response);
        }
    }

    public function testSendShouldDispatchEvents(): void
    {
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $skebbyRestClient = new Client($this->config, new CurlExtensionTransport(), new EventDispatcher());

        $sms = $this->getSmsWithRecipients();
        $responses = $skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            self::assertInstanceOf(Response::class, $response);
        }
    }

    public function testSendSmsWithRecipientsVariablesShouldReturnResponses(): void
    {
        $this->client->sendRequest(Argument::type(Request::class))
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $sms = $this->getSmsWithRecipientsAndRecipientsVariables();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            self::assertInstanceOf(Response::class, $response);
        }
    }

    public function testQueryStringSentToSkebby(): void
    {
        $expectedPostFieldsValue =
            'username=test&' .
            'password=test&' .
            'method=send_sms_classic&' .
            'sender_number=393333333333&' .
            'recipients=[{"recipient":"393930000123","name":"Mario"}]&' .
            'text=Hi+${name}&' .
            'user_reference=WelcomeMario&' .
            'delivery_start=Fri%2C+21+Oct+2016+14%3A30%3A00+%2B0000&' .
            'validity_period=2000&' .
            'encoding_scheme=normal&' .
            'charset=UTF-8';

        $this->streamFactory->createStream($expectedPostFieldsValue)->willReturn($stream = Stream::create($expectedPostFieldsValue));
        $this->client->sendRequest(Argument::that(static function (Request $request) use ($expectedPostFieldsValue) {
            return (string) $request->getBody() === $expectedPostFieldsValue;
        }))
            ->shouldBeCalled()
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $clock = new FrozenClock(new DateTimeImmutable('2016-10-21 14:29:00'));
        $deliveryStart = new DateTimeImmutable('2016-10-21 14:30:00');

        $sms = new Sms($clock);
        $sms
            ->addRecipient('00393930000123')
            ->addRecipientVariable('00393930000123', 'name', 'Mario')
            ->setUserReference('WelcomeMario')
            ->setDeliveryStart($deliveryStart)
            ->setValidityPeriod(DateInterval::createFromDateString('2000 minutes'))
            ->setText('Hi ${name}');

        $this->skebbyRestClient->send($sms);
    }

    public function testShouldUseSmsSenderIfSet(): void
    {
        $expectedPostFieldsValue =
            'username=test&' .
            'password=test&' .
            'method=send_sms_classic&' .
            'sender_number=&' .
            'sender_string=Fazland&' .
            'recipients=["393930000123"]&' .
            'text=FOO+BAR!&' .
            'user_reference=&' .
            'delivery_start=&' .
            'validity_period=2800&' .
            'encoding_scheme=normal&' .
            'charset=UTF-8';

        $this->streamFactory->createStream($expectedPostFieldsValue)->willReturn($stream = Stream::create($expectedPostFieldsValue));
        $this->client->sendRequest(Argument::that(static function (Request $request) use ($expectedPostFieldsValue) {
            return (string) $request->getBody() === $expectedPostFieldsValue;
        }))
            ->shouldBeCalled()
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $sms = new Sms();
        $sms
            ->setSender('Fazland')
            ->addRecipient('00393930000123')
            ->setText('FOO BAR!');

        $this->skebbyRestClient->send($sms);
    }

    public function testMassiveSmsSend(): void
    {
        $this->client->sendRequest(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn(new Psr7Response(200, [], self::RESPONSE_SUCCESS));

        $sms = Sms::create()
            ->setText('Some text')
            ->addRecipient('003335566777');

        for ($i = 0; $i < Recipients::MAX + 100; ++$i) {
            $sms
                ->addRecipient('003334455666')
                ->addRecipientVariable('003334455666', 'name', "name-$i");
        }

        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            self::assertInstanceOf(Response::class, $response);
        }
    }
}
