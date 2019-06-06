<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Tests\DataStructure;

use Fazland\SkebbyRestClient\DataStructure\Sms;

/**
 * @author Massimiliano Braglia <massimiliano.braglia@fazland.com>
 */
class SmsTest extends \PHPUnit_Framework_TestCase
{
    public function testRemoveRecipientRemovesAlsoItsRecipientVariables()
    {
        $sms = Sms::create()
            ->addRecipient('+393334455666')
            ->addRecipientVariable('+393334455666', 'name', 'Mario')
            ->addRecipient('+393337788999')
            ->addRecipientVariable('+393337788999', 'name', 'Luigi')
        ;

        $sms->removeRecipient('+393337788999');
        self::assertNotContains('+393337788999', $sms->getRecipients());
        self::assertFalse(isset($sms->getRecipientVariables()['+393337788999']));
    }

    public function testRemoveRecipientVariables()
    {
        $sms = Sms::create()
            ->addRecipient('+393334455666')
            ->addRecipientVariable('+393334455666', 'name', 'Mario')
        ;

        $sms->removeRecipientVariable('+393334455666', 'name');
        self::assertFalse(isset($sms->getRecipientVariables()['+393337788999']));
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\InvalidDeliveryStartException
     */
    public function testSetDeliveryStartShouldThrowInvalidDeliveryStartExceptionOnInvalidDateTime()
    {
        $sms = new Sms();
        $sms
            ->setDeliveryStart(new \DateTime('yesterday'))
        ;
    }

    /**
     * @expectedException \Fazland\SkebbyRestClient\Exception\InvalidValidityPeriodException
     */
    public function testSetValidityPeriodShouldThrowInvalidValidityPeriodExceptionOnIntervalNotInBoundary()
    {
        $sms = new Sms();
        $sms
            ->setValidityPeriod(\DateInterval::createFromDateString('3000 minutes'))
        ;
    }

    public function testSetValidDeliveryStartAndValidityPeriodShouldNotThrowException()
    {
        $sms = new Sms();
        $sms
            ->setDeliveryStart(new \DateTime('+10 days'))
            ->setValidityPeriod(\DateInterval::createFromDateString('2000 minutes'))
        ;

        self::assertTrue(true);
    }
}
