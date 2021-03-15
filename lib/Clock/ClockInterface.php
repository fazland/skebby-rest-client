<?php

declare(strict_types=1);

namespace Fazland\SkebbyRestClient\Clock;

use DateTimeImmutable;

interface ClockInterface
{
    /**
     * Gets an immutable DateTime object set to the current "now" value.
     */
    public function now(): DateTimeImmutable;
}
