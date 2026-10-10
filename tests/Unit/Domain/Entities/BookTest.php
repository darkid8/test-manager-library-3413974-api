<?php

namespace Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;
use DomainException;

class BookTest extends TestCase
{
    private function createBook(int $total, int $available): Book
    {
        return new Book(
            id: 'uuid-123',
            title: 'Test Book',
            author: 'Test Author',
            isbn: new Isbn('9780307474728'),
            categoryId: 'cat-uuid',
            totalCopies: $total,
            availableCopies: $available
        );
    }

    public function test_withdraw_decreases_available_copies()
    {
        $book = $this->createBook(3, 3);
        $book->withdraw();
        
        $this->assertEquals(2, $book->getAvailableCopies());
    }

    public function test_withdraw_throws_when_no_copies_available()
    {
        $book = $this->createBook(3, 0);
        
        $this->expectException(DomainException::class);
        $book->withdraw();
    }

    public function test_restock_increases_available_copies()
    {
        $book = $this->createBook(3, 2);
        $book->restock();
        
        $this->assertEquals(3, $book->getAvailableCopies());
    }

    public function test_restock_throws_when_already_full()
    {
        $book = $this->createBook(3, 3);
        
        $this->expectException(DomainException::class);
        $book->restock();
    }

    public function test_change_total_copies_adjusts_available_copies_correctly_when_increased()
    {
        $book = $this->createBook(3, 1);
        $book->changeTotalCopies(5); // +2 difference
        
        $this->assertEquals(5, $book->getTotalCopies());
        $this->assertEquals(3, $book->getAvailableCopies());
    }

    public function test_change_total_copies_adjusts_available_copies_correctly_when_decreased()
    {
        $book = $this->createBook(3, 3);
        $book->changeTotalCopies(2); // -1 difference
        
        $this->assertEquals(2, $book->getTotalCopies());
        $this->assertEquals(2, $book->getAvailableCopies());
    }

    public function test_change_total_copies_throws_if_available_would_become_negative()
    {
        $book = $this->createBook(3, 0); // 0 available
        
        $this->expectException(DomainException::class);
        $book->changeTotalCopies(2); // would try to set available to -1
    }

    public function test_mark_as_deleted()
    {
        $book = $this->createBook(3, 3);
        $this->assertFalse($book->isDeleted());
        
        $book->markAsDeleted(new DateTimeImmutable('2026-10-10'));
        
        $this->assertTrue($book->isDeleted());
        $this->assertInstanceOf(DateTimeImmutable::class, $book->getDeletedAt());
    }
}
