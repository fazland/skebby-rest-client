<?php declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Clock;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class FrozenClock implements ClockInterface
{
    private DateTimeImmutable $current;

    public function __construct(DateTimeInterface $now)
    {
        $this->current = $now instanceof DateTime ? DateTimeImmutable::createFromMutable($now) : $now;
    }

    /**
     * @inheritDoc
     */
    public function now(): DateTimeImmutable
    {
        return $this->current;
    }
}
