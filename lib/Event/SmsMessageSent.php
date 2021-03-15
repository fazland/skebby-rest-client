<?php

namespace Fazland\SkebbyRestClient\Event;

use Fazland\SkebbyRestClient\DataStructure\Sms;

final class SmsMessageSent
{
    /**
     * @var Sms
     */
    private $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function getSms(): Sms
    {
        return $this->sms;
    }
}
