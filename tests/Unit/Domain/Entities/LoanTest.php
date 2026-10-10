<?php

namespace Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\Loan;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;
use DomainException;

class LoanTest extends TestCase
{
    private function createBook(string $id, int $available): Book
    {
        return new Book(
            id: $id,
            title: 'Test Book',
            author: 'Author',
            isbn: new Isbn('9780307474728'),
            categoryId: 'cat-id',
            totalCopies: 5,
            availableCopies: $available
        );
    }

    public function test_add_item_adds_line_and_withdraws_book()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 2);
        
        $now = new DateTimeImmutable('2026-10-10 10:00:00');
        $loan->addItem('item-1', $book, 'Novela', $now);
        
        $this->assertCount(1, $loan->getItems());
        $this->assertEquals(1, $book->getAvailableCopies());
        
        $item = $loan->getItems()[0];
        $this->assertEquals('2026-10-24 10:00:00', $item->dueAt->format('Y-m-d H:i:s'));
    }

    public function test_add_item_throws_if_max_3_items()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $now = new DateTimeImmutable();
        
        $loan->addItem('item-1', $this->createBook('book-1', 1), 'Novela', $now);
        $loan->addItem('item-2', $this->createBook('book-2', 1), 'Novela', $now);
        $loan->addItem('item-3', $this->createBook('book-3', 1), 'Novela', $now);
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("MEMBER_LIMIT_EXCEEDED");
        $loan->addItem('item-4', $this->createBook('book-4', 1), 'Novela', $now);
    }

    public function test_add_item_throws_if_duplicate_book()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $now = new DateTimeImmutable();
        $book = $this->createBook('book-1', 2);
        
        $loan->addItem('item-1', $book, 'Novela', $now);
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("DUPLICATE_BOOK_IN_LOAN");
        $loan->addItem('item-2', $book, 'Novela', $now);
    }

    public function test_return_item_restocks_book()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 1);
        $now = new DateTimeImmutable();
        
        $loan->addItem('item-1', $book, 'Novela', $now);
        
        $returnTime = new DateTimeImmutable('2026-10-11');
        $loan->returnItem('item-1', $book, $returnTime);
        
        $this->assertTrue($loan->getItems()[0]->isReturned());
        $this->assertEquals(1, $book->getAvailableCopies());
    }

    public function test_return_already_returned_item_throws()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 1);
        $now = new DateTimeImmutable();
        
        $loan->addItem('item-1', $book, 'Novela', $now);
        $loan->returnItem('item-1', $book, $now);
        
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("ITEM_ALREADY_RETURNED");
        $loan->returnItem('item-1', $book, $now);
    }

    public function test_state_is_open_when_not_returned_and_not_overdue()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 1);
        
        $loanTime = new DateTimeImmutable('2026-10-10');
        $loan->addItem('item-1', $book, 'Novela', $loanTime);
        
        $checkTime = new DateTimeImmutable('2026-10-15'); // 5 days later
        $this->assertEquals('open', $loan->getState($checkTime));
    }

    public function test_state_is_overdue_when_not_returned_and_past_due()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 1);
        
        $loanTime = new DateTimeImmutable('2026-10-10');
        $loan->addItem('item-1', $book, 'Novela', $loanTime);
        
        $checkTime = new DateTimeImmutable('2026-10-25'); // 15 days later
        $this->assertEquals('overdue', $loan->getState($checkTime));
    }

    public function test_state_is_closed_when_all_returned()
    {
        $loan = new Loan('loan-1', 'member-1', 'user-1', new DateTimeImmutable());
        $book = $this->createBook('book-1', 1);
        
        $loanTime = new DateTimeImmutable('2026-10-10');
        $loan->addItem('item-1', $book, 'Novela', $loanTime);
        
        $returnTime = new DateTimeImmutable('2026-10-15');
        $loan->returnItem('item-1', $book, $returnTime);
        
        $checkTime = new DateTimeImmutable('2026-10-25'); // Even if time passes, it is closed
        $this->assertEquals('closed', $loan->getState($checkTime));
    }
}