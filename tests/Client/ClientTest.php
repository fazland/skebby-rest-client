<?php

namespace Fazland\SkebbyRestClient\Tests\Client;

use Fazland\SkebbyRestClient\Client\Client;
use Fazland\SkebbyRestClient\Constant\Endpoints;
use Fazland\SkebbyRestClient\Constant\Recipients;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;
use Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 * @runTestsInSeparateProcesses
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var MockedFunctionResult
     */
    static $mockedFunctionResult;

    /**
     * @var Client
     */
    private $skebbyRestClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->config = [
            'username' => 'test',
            'password' => 'test',
            'sender_number' => '+393333333333',
            'method' => SendMethods::CLASSIC,
            'endpoint_uri' => Endpoints::REST_HTTPS,
        ];

        $this->skebbyRestClient = new Client($this->config);
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
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client 
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value) { }

    function curl_exec($curl)
    {
        return "";
    }

    function curl_close($curl) { }
}
EOT
        );

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\UnknownErrorResponseException
     */
    public function testSendShouldThrowUnknownErrorResponseExceptionOnResponseWithoutStatus()
    {
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value) { }

    function curl_exec($curl)
    {
        return "this=is&a=response&without=status";
    }

    function curl_close($curl) { }
}
EOT
        );

        $sms = $this->getSmsWithRecipients();
        $this->skebbyRestClient->send($sms);
    }

    public function testSendShouldReturnResponses()
    {
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value) { }

    function curl_exec($curl)
    {
        return "status=success&message=";
    }

    function curl_close($curl) { }
}
EOT
        );

        $sms = $this->getSmsWithRecipients();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testSendSmsWithRecipientsVariablesShouldReturnResponses()
    {
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value) { }

    function curl_exec($curl)
    {
        return "status=success&message=";
    }

    function curl_close($curl) { }
}
EOT
        );

        $sms = $this->getSmsWithRecipientsAndRecipientsVariables();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }

    public function testQueryStringSentToSkebby()
    {
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value)
    {
        \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(true, "");

        if ($option === CURLOPT_CONNECTTIMEOUT && 10 !== $value) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Connection timeout value is not equal to 10 (value: $value)"
            );
        }

        if ($option === CURLOPT_RETURNTRANSFER && true !== $value) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Return Transfer value is not boolean true (value: $value)"
            );
        }

        if ($option === CURLOPT_TIMEOUT && 60 !== $value) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Timeout value is not equalt to 60 (value: $value)"
            );
        }

        if ($option === CURLOPT_POST && 1 !== $value) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Post value is not equal to 1 (value: $value)"
            );
        }

        $methodClassic = \Fazland\SkebbyRestClient\Constant\SendMethods::CLASSIC;
        $expectedPostFieldsValue = 'username=test&password=test&method=send_sms_classic&sender_number="393333333333"&recipients=[{"recipient":"393930000123","name":"Mario"}]&text=Hi+${name}&user_reference=WelcomeMario&delivery_start=&validity_period=2880&encoding_scheme=normal&charset=UTF-8';
        if ($option === CURLOPT_POSTFIELDS && $expectedPostFieldsValue !== trim($value)) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Post Fields are not the same as:\n" .
                "$expectedPostFieldsValue\n" .
                "$value"
            );
        }

        if ($option === CURLOPT_URL && \Fazland\SkebbyRestClient\Constant\Endpoints::REST_HTTPS !== $value) {
            \Fazland\SkebbyRestClient\Tests\Client\ClientTest::$mockedFunctionResult = new \Fazland\SkebbyRestClient\Tests\Util\MockedFunctionResult(
                false,
                "Curl Url is not the same it was specified in configuration (value: $value)"
            );
        }
    }

    function curl_exec($curl) 
    {
        return "status=success&message=";
    }

    function curl_close($curl) { }
}
EOT
        );

        $sms = new Sms();
        $sms
            ->addRecipient('00393930000123')
            ->addRecipientVariable('00393930000123', 'name', 'Mario')
            ->setUserReference('WelcomeMario')
            ->setDeliveryStart(new \DateTime('+10 days'))
            ->setValidityPeriod(\DateInterval::createFromDateString('2000 minutes'))
            ->setText('Hi ${name}')
        ;

        $this->skebbyRestClient->send($sms);
        $this->assertTrue(self::$mockedFunctionResult->isSuccessful(), self::$mockedFunctionResult->getMessage());
    }

    public function testMassiveSmsSend()
    {
        eval(<<<'EOT'
?><?php

namespace Fazland\SkebbyRestClient\Client
{
    function curl_init() { }

    function curl_setopt($curl, $option, $value) { }

    function curl_exec($curl)
    {
        return "status=success&message=";
    }

    function curl_close($curl) { }
}
EOT
        );

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
