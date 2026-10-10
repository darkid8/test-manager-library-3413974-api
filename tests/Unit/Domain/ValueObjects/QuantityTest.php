<?php

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObjects\Quantity;
use InvalidArgumentException;

class QuantityTest extends TestCase
{
    public function test_positive_quantity_is_accepted()
    {
        $quantity = new Quantity(5);
        $this->assertEquals(5, $quantity->value);
    }

    public function test_zero_quantity_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Quantity(0);
    }

    public function test_negative_quantity_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Quantity(-1);
    }
}
