<?php

namespace App\Domain\Entities;

use DomainException;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;

class Book
{
    private ?DateTimeImmutable $deletedAt = null;

    public function __construct(
        public readonly string $id,
        public string $title,
        public string $author,
        public Isbn $isbn,
        public readonly string $categoryId,
        private int $totalCopies,
        private int $availableCopies,
        public ?string $coverKey = null
    ) {
        if ($this->totalCopies < 1) {
            throw new DomainException("Total copies must be at least 1.");
        }
        if ($this->availableCopies < 0 || $this->availableCopies > $this->totalCopies) {
            throw new DomainException("Available copies must be between 0 and total copies.");
        }
    }

    public function withdraw(): void
    {
        if ($this->availableCopies === 0) {
            throw new DomainException("No copies available to withdraw.");
        }
        $this->availableCopies--;
    }

    public function restock(): void
    {
        if ($this->availableCopies === $this->totalCopies) {
            throw new DomainException("Cannot restock: all copies are already available.");
        }
        $this->availableCopies++;
    }

    public function changeTotalCopies(int $newTotal): void
    {
        if ($newTotal < 1) {
            throw new DomainException("Total copies must be at least 1.");
        }
        $difference = $newTotal - $this->totalCopies;
        $newAvailable = $this->availableCopies + $difference;

        if ($newAvailable < 0) {
            throw new DomainException("Cannot decrease total copies: it would result in negative available copies.");
        }

        $this->totalCopies = $newTotal;
        $this->availableCopies = $newAvailable;
    }

    public function markAsDeleted(DateTimeImmutable $at): void
    {
        $this->deletedAt = $at;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getTotalCopies(): int
    {
        return $this->totalCopies;
    }

    public function getAvailableCopies(): int
    {
        return $this->availableCopies;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
