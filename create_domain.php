<?php

$files = [
    'app/Domain/Entities/LoanItem.php' => <<<'PHP'
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
PHP,

    'app/Domain/Entities/Loan.php' => <<<'PHP'
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
PHP,

    'app/Domain/Entities/User.php' => <<<'PHP'
<?php

namespace App\Domain\Entities;

use DomainException;

class User
{
    public readonly string $username;
    
    public function __construct(
        public readonly string $id,
        string $username,
        public string $passwordHash,
        public readonly string $role
    ) {
        $this->username = strtolower(trim($username));
        
        if (!in_array($this->role, ['admin', 'librarian'], true)) {
            throw new DomainException("Invalid role.");
        }
    }
}
PHP,

    'app/Domain/Entities/Member.php' => <<<'PHP'
<?php

namespace App\Domain\Entities;

class Member
{
    public function __construct(
        public readonly string $id,
        public string $fullName,
        public readonly string $documentNumber
    ) {
        $this->fullName = trim($fullName);
    }
}
PHP,

];

foreach ($files as $path => $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
}
echo "Domain entities created.";
