<?php

namespace App\Domain\Entities;

use DateTimeImmutable;
use DomainException;

class Loan
{
    /** @var LoanItem[] */
    private array $items = [];

    public function __construct(
        public readonly string $id,
        public readonly string $memberId,
        public readonly string $loanedByUserId,
        public readonly DateTimeImmutable $loanedAt,
        array $items = []
    ) {
        $this->items = $items;
    }

    public function addItem(string $itemId, Book $book, string $categoryName, DateTimeImmutable $currentTime): void
    {
        if (count($this->items) >= 3) {
            throw new DomainException("MEMBER_LIMIT_EXCEEDED");
        }

        foreach ($this->items as $item) {
            if ($item->bookId === $book->id) {
                throw new DomainException("DUPLICATE_BOOK_IN_LOAN");
            }
        }

        $book->withdraw();

        $dueAt = $currentTime->modify('+14 days');

        $this->items[] = new LoanItem(
            id: $itemId,
            loanId: $this->id,
            bookId: $book->id,
            bookTitle: $book->title,
            isbn: $book->isbn->value,
            categoryName: $categoryName,
            dueAt: $dueAt
        );
    }

    public function returnItem(string $itemId, Book $book, DateTimeImmutable $returnTime): void
    {
        foreach ($this->items as $item) {
            if ($item->id === $itemId) {
                $item->markAsReturned($returnTime);
                $book->restock();
                return;
            }
        }
        throw new DomainException("Item not found in loan.");
    }

    public function getState(DateTimeImmutable $now): string
    {
        if (empty($this->items)) {
            return 'open';
        }

        $allReturned = true;
        foreach ($this->items as $item) {
            if (!$item->isReturned()) {
                $allReturned = false;
                if ($item->isOverdue($now)) {
                    return 'overdue';
                }
            }
        }

        return $allReturned ? 'closed' : 'open';
    }

    public function getItems(): array
    {
        return $this->items;
    }
}