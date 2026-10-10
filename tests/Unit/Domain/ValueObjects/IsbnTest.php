<?php

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObjects\Isbn;
use InvalidArgumentException;

class IsbnTest extends TestCase
{
    public function test_valid_isbn_is_accepted()
    {
        $isbn = new Isbn('9780307474728');
        $this->assertEquals('9780307474728', $isbn->value);
    }

    public function test_isbn_with_letters_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Isbn('978030747472A');
    }

    public function test_isbn_too_short_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Isbn('123456789012');
    }
    
    public function test_isbn_too_long_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        new Isbn('12345678901234');
    }
}
