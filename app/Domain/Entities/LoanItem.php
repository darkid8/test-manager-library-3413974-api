<?php

namespace App\Domain\Entities;

use DateTimeImmutable;
use DomainException;

class LoanItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $loanId,
        public readonly string $bookId,
        public readonly string $bookTitle,
        public readonly string $isbn,
        public readonly string $categoryName,
        public readonly DateTimeImmutable $dueAt,
        private ?DateTimeImmutable $returnedAt = null
    ) {}

    public function markAsReturned(DateTimeImmutable $at): void
    {
        if ($this->returnedAt !== null) {
            throw new DomainException("ITEM_ALREADY_RETURNED");
        }
        $this->returnedAt = $at;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function isOverdue(DateTimeImmutable $now): bool
    {
        if ($this->isReturned()) {
            return false;
        }
        return $now > $this->dueAt;
    }

    public function getReturnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }
}