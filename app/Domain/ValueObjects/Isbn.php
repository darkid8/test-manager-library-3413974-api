<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

readonly class Isbn
{
    public function __construct(public string $value)
    {
        if (!preg_match('/^\d{13}$/', $value)) {
            throw new InvalidArgumentException("ISBN must be exactly 13 digits.");
        }
    }
}
