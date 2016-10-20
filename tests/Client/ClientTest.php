<?php

namespace Fazland\SkebbyRestClient\Tests\Client;

use Fazland\SkebbyRestClient\Client\Client;
use Fazland\SkebbyRestClient\Constant\SendMethods;
use Fazland\SkebbyRestClient\DataStructure\Response;
use Fazland\SkebbyRestClient\DataStructure\Sms;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 * @runTestsInSeparateProcesses
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    private $skebbyRestClient;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->skebbyRestClient = new Client([
            'username' => 'test',
            'password' => 'test',
            'sender_number' => '+393333333333',
            'method' => SendMethods::CLASSIC
        ]);
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
    
    function curl_exec()
    {
        return "";
    }
    
    function curl_close() { }
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
    
    function curl_exec()
    {
        return "this=is&a=response&without=status";
    }
    
    function curl_close() { }
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
    
    function curl_exec()
    {
        return "status=success&message=";
    }
    
    function curl_close() { }
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
    
    function curl_exec()
    {
        return "status=success&message=";
    }
    
    function curl_close() { }
}
EOT
        );

        $sms = $this->getSmsWithRecipientsAndRecipientsVariables();
        $responses = $this->skebbyRestClient->send($sms);

        foreach ($responses as $response) {
            $this->assertInstanceOf(Response::class, $response);
        }
    }
}
