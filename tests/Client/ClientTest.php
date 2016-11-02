<?php

namespace Fazland\SkebbyRestClient\Tests\Client;

use Fazland\SkebbyRestClient\Client\Client;
use Fazland\SkebbyRestClient\Constant\Charsets;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\PhpUnit\FunctionMockTrait;
use Prophecy\Argument;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    use FunctionMockTrait;

    const RESPONSE_WITHOUT_STATUS =
'<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic_report><remaining_sms>5</remaining_sms><id>1477056680</id></test_send_sms_classic_report></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    const RESPONSE_FAIL =
'<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic><response><code>11</code><message>Unknown charset, use ISO-8859-1 or UTF-8</message></response><status>failed</status></test_send_sms_classic></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    const RESPONSE_SUCCESS =
'<?xml version="1.0" encoding="UTF-8"?>
<SkebbyApi_Public_Send_SmsEasy_Advanced generator="zend" version="1.0"><test_send_sms_classic_report><remaining_sms>5</remaining_sms><id>1477056680</id><status>success</status></test_send_sms_classic_report></SkebbyApi_Public_Send_SmsEasy_Advanced>';

    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $skebbyRestClient;

    /**
     * @var NamespaceProphecy
     */
    private $functionMockNamespace;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->config = [
            'username' => 'test',
            'password' => 'test',
            'sender' => '+393333333333',
            'method' => SendMethods::CLASSIC,
            'endpoint_uri' => Endpoints::REST_HTTPS,
            'charset' => Charsets::UTF8,
        ];

        $this->skebbyRestClient = new Client($this->config);

        $this->functionMockNamespace = $this->prophesizeForFunctions(Client::class);
        $this->functionMockNamespace->curl_init()->willReturn();
        $this->functionMockNamespace->curl_setopt(Argument::cetera())->willReturn();
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn('');
        $this->functionMockNamespace->curl_close(Argument::cetera())->willReturn();
    }

    /**
     * @return Sms
     */
    private function getSmsWithRecipients()
    {
        return Sms::create()
            ->setRecipients([
                '+393473322444',
                '+393910000000',
            ])
            ->setText('Some text')
        ;
    }

    /**
     * @return Sms
     */
    private function getSmsWithRecipientsAndRecipientsVariables()
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
            ->setText('Some text')
        ;
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\NoRecipientsSpecifiedException
     */
    public function testSendShouldThrowNoRecipientSpecifiedExceptionOnEmptyRecipient()
    {
        $sms = Sms::create()->setText('some text');
        $this->skebbyRestClient->send($sms);
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\EmptyResponseException
     */
    public function testSendShouldThrowEmptyResponseExceptionOnEmptyResponse()
    {
        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException
     */
    public function testSendShouldThrowUnknownErrorResponseExceptionOnResponseWithoutStatus()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_WITHOUT_STATUS);

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldReturnFailingResponseOnUnrecognizedCharset()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_FAIL);

        $this->functionMockNamespace->urlencode($this->config['charset'])->willReturn('I am not your charset');

        $sms = Sms::create()
            ->addRecipient('+393930000123')
            ->setText('Hey mate')
        ;

        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
            $this->assertEquals('failed', $response->getStatus());
        }
    }

    public function testSendShouldReturnResponses()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_SUCCESS);

        $sms = $this->getSmsWithRecipients();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testSendSmsWithRecipientsVariablesShouldReturnResponses()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_SUCCESS);

        $sms = $this->getSmsWithRecipientsAndRecipientsVariables();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testQueryStringSentToSkebby()
    {
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_POST, 1)->shouldBeCalled();
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_SUCCESS);

        $smsNamespace = $this->prophesizeForFunctions(Sms::class);
        $smsNamespace->time()->willReturn(1477060140);
        $deliveryStart = new \DateTime('2016-10-21 14:30:00');

        $expectedPostFieldsValue =
            'username=test&'.
            'password=test&'.
            'method=send_sms_classic&'.
            'sender_number=393333333333&'.
            'sender_string=&'.
            'recipients=[{"recipient":"393930000123","name":"Mario"}]&'.
            'text=Hi+${name}&'.
            'user_reference=WelcomeMario&'.
            'delivery_start=Fri%2C+21+Oct+2016+14%3A30%3A00+%2B0000&'.
            'validity_period=2000&'.
            'encoding_scheme=normal&'.
            'charset=UTF-8'
        ;

        $this->functionMockNamespace
            ->curl_setopt(Argument::any(), CURLOPT_POSTFIELDS, $expectedPostFieldsValue)
            ->shouldBeCalled()
        ;

        $sms = new Sms();
        $sms
            ->addRecipient('00393930000123')
            ->addRecipientVariable('00393930000123', 'name', 'Mario')
            ->setUserReference('WelcomeMario')
            ->setDeliveryStart($deliveryStart)
            ->setValidityPeriod(\DateInterval::createFromDateString('2000 minutes'))
            ->setText('Hi ${name}')
        ;

        $this->skebbyRestClient->send($sms);
    }

    public function testShouldUseSmsSenderIfSet()
    {
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_POST, 1)->shouldBeCalled();
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_SUCCESS);

        $expectedPostFieldsValue =
            'username=test&'.
            'password=test&'.
            'method=send_sms_classic&'.
            'sender_number=&'.
            'sender_string=Fazland&'.
            'recipients=["393930000123"]&'.
            'text=FOO+BAR!&'.
            'user_reference=&'.
            'delivery_start=&'.
            'validity_period=2800&'.
            'encoding_scheme=normal&'.
            'charset=UTF-8'
        ;

        $this->functionMockNamespace
            ->curl_setopt(Argument::any(), CURLOPT_POSTFIELDS, $expectedPostFieldsValue)
            ->shouldBeCalled()
        ;

        $sms = new Sms();
        $sms
            ->setSender('Fazland')
            ->addRecipient('00393930000123')
            ->setText('FOO BAR!')
        ;

        $this->skebbyRestClient->send($sms);
    }

    public function testMassiveSmsSend()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn(self::RESPONSE_SUCCESS);

        $sms = Sms::create()
            ->setText('Some text')
            ->addRecipient('003335566777')
        ;

        for ($i = 0; $i < Recipients::MAX + 100; ++$i) {
            $sms
                ->addRecipient('003334455666')
                ->addRecipientVariable('003334455666', 'name', "name-$i")
            ;
        }

        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }
}
