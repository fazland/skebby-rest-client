<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Clock;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

use function assert;

class FrozenClock implements ClockInterface
{
    private DateTimeImmutable $current;

    public function __construct(DateTimeInterface $now)
    {
        $current = $now instanceof DateTime ? DateTimeImmutable::createFromMutable($now) : $now;
        assert($current instanceof DateTimeImmutable);

        $this->current = $current;
    }

    public function now(): DateTimeImmutable
    {
        return $this->current;
    }
}
