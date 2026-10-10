<?php

$files = [
    'app/Domain/Repositories/BookRepository.php' => <<<'PHP'
<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Book;

interface BookRepository {
    public function save(Book $book): void;
    public function findById(string $id): ?Book;
    public function search(?string $query, ?string $categoryId): array;
    public function checkIsbnExists(string $isbn): bool;
}
PHP,
    'app/Domain/Repositories/MemberRepository.php' => <<<'PHP'
<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Member;

interface MemberRepository {
    public function save(Member $member): void;
    public function findById(string $id): ?Member;
    public function checkDocumentExists(string $document): bool;
}
PHP,
    'app/Domain/Repositories/UserRepository.php' => <<<'PHP'
<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\User;

interface UserRepository {
    public function save(User $user): void;
    public function findByUsername(string $username): ?User;
    public function findById(string $id): ?User;
}
PHP,
    'app/Domain/Repositories/LoanRepository.php' => <<<'PHP'
<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Loan;

interface LoanRepository {
    public function save(Loan $loan): void;
    public function findById(string $id): ?Loan;
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
echo "Interfaces created.";
