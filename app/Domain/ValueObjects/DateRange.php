<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;
use DateTimeImmutable;

readonly class DateRange
{
    public function __construct(
        public DateTimeImmutable $start,
        public DateTimeImmutable $end
    ) {
        if ($end < $start) {
            throw new InvalidArgumentException("End date must be greater than or equal to start date.");
        }
    }
}
