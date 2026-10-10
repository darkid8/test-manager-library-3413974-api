<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class Quantity
{
    public function __construct(public int $value)
    {
        if ($value < 1) {
            throw new InvalidArgumentException("Quantity must be greater than 0.");
        }
    }
}
