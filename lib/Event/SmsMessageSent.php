<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Event;

use Fazland\SkebbyRestClient\DataStructure\Sms;

final class SmsMessageSent
{
    private Sms $sms;

    public function __construct(Sms $sms)
    {
        $this->sms = $sms;
    }

    public function getSms(): Sms
    {
        return $this->sms;
    }
}
