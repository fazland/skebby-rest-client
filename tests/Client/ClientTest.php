<?php

namespace Fazland\SkebbyRestClient\Tests\Client;

use Fazland\SkebbyRestClient\Client\Client;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Kcs\FunctionMock\NamespaceProphecy;
use Kcs\FunctionMock\Prophet\Prophet as KcsProphet;
use Prophecy\Argument;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $skebbyRestClient;

    /**
     * @var KcsProphet
     */
    private $functionMockProphet;

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
        ];

        $this->skebbyRestClient = new Client($this->config);

        $this->functionMockProphet = new KcsProphet();

        $this->functionMockNamespace = $this->functionMockProphet->prophesize(Client::class);
        $this->functionMockNamespace->curl_init()->willReturn();
        $this->functionMockNamespace->curl_setopt(Argument::cetera())->willReturn();
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("");
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
                '+393910000000'
            ])
            ->setText('Some text')
        ;
    }

    protected function verifyMockObjects()
    {
        parent::verifyMockObjects();

        $this->functionMockProphet->checkPredictions();
    }

    /**
     * @return Sms
     */
    private function getSmsWithRecipientsAndRecipientsVariables()
    {
        return Sms::create()
            ->setRecipients([
                '+393473322444',
                '+393910000000'
            ])
            ->setRecipientVariables('+393473322444', [
                'FirstName' => 'This is a first name',
                'LastName' => 'This is a last name',
                'Infos' => 'These are infos'
            ])
            ->setRecipientVariables('+393910000000', [
                'FirstName' => 'This is another first name',
                'LastName' => 'This is another last name',
                'Infos' => 'These are other infos'
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
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("this=is&a=response&without=status");

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldReturnResponses()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("status=success&message=");

        $sms = $this->getSmsWithRecipients();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testSendSmsWithRecipientsVariablesShouldReturnResponses()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("status=success&message=");

        $sms = $this->getSmsWithRecipientsAndRecipientsVariables();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testQueryStringSentToSkebby()
    {
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_CONNECTTIMEOUT, 10)->shouldBeCalled();
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_RETURNTRANSFER, true)->shouldBeCalled();
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_TIMEOUT, 60)->shouldBeCalled();
        $this->functionMockNamespace->curl_setopt(Argument::any(), CURLOPT_POST, 1)->shouldBeCalled();

        $smsNamespace = $this->functionMockProphet->prophesize(Sms::class);
        $smsNamespace->time()->willReturn(1477060140);
        $deliveryStart = new \DateTime('2016-10-21 14:30:00');

        $expectedPostFieldsValue =
            'username=test&' .
            'password=test&' .
            'method=send_sms_classic&' .
            'sender_number=393333333333&' .
            'sender_string=&' .
            'recipients=[{"recipient":"393930000123","name":"Mario"}]&' .
            'text=Hi+${name}&' .
            'user_reference=WelcomeMario&' .
            'delivery_start=Fri%2C+21+Oct+2016+14%3A30%3A00+%2B0000&' .
            'validity_period=2000&' .
            'encoding_scheme=normal&' .
            'charset=UTF-8'
        ;

        $this->functionMockNamespace
            ->curl_setopt(Argument::any(), CURLOPT_POSTFIELDS, $expectedPostFieldsValue)
            ->shouldBeCalled()
        ;
        $this->functionMockNamespace
            ->curl_setopt(Argument::any(), CURLOPT_URL, Endpoints::REST_HTTPS)
            ->shouldBeCalled()
        ;
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("status=success&message=");

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

    public function testMassiveSmsSend()
    {
        $this->functionMockNamespace->curl_exec(Argument::cetera())->willReturn("status=success&message=");

        $sms = Sms::create()
            ->setText('Some text')
            ->addRecipient('003335566777')
        ;

        for ($i = 0; $i < Recipients::MAX + 100; $i++) {
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
