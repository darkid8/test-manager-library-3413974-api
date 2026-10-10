<?php

namespace Tests\Unit\Domain\ValueObjects;

use PHPUnit\Framework\TestCase;
use App\Domain\ValueObjects\DateRange;
use DateTimeImmutable;
use InvalidArgumentException;

class DateRangeTest extends TestCase
{
    public function test_end_date_after_start_date_is_accepted()
    {
        $start = new DateTimeImmutable('2026-10-10');
        $end = new DateTimeImmutable('2026-10-15');
        
        $range = new DateRange($start, $end);
        
        $this->assertSame($start, $range->start);
        $this->assertSame($end, $range->end);
    }

    public function test_end_date_same_as_start_date_is_accepted()
    {
        $start = new DateTimeImmutable('2026-10-10 10:00:00');
        $end = new DateTimeImmutable('2026-10-10 10:00:00');
        
        $range = new DateRange($start, $end);
        
        $this->assertSame($start, $range->start);
        $this->assertSame($end, $range->end);
    }

    public function test_end_date_before_start_date_throws_exception()
    {
        $start = new DateTimeImmutable('2026-10-15');
        $end = new DateTimeImmutable('2026-10-10');
        
        $this->expectException(InvalidArgumentException::class);
        new DateRange($start, $end);
    }
}
