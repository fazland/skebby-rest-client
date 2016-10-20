<?php

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
        $this->assertNotContains('+393337788999', $sms->getRecipients());
        $this->assertFalse(isset($sms->getRecipientVariables()['+393337788999']));
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
}
