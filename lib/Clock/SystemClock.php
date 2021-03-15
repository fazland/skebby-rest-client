<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Clock;

use DateTimeImmutable;

class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
