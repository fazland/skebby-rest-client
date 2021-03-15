<?php

namespace Fazland\SkebbyRestClient\Event;

use Fazland\SkebbyRestClient\DataStructure\Sms;
use Symfony\Contracts\EventDispatcher\Event;

final class SmsMessageSent extends Event
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
