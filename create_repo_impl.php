<?php

$files = [
    'app/Infrastructure/Repositories/EloquentBookRepository.php' => <<<'PHP'
<?php
namespace App\Infrastructure\Repositories;
use App\Domain\Repositories\BookRepository;
use App\Domain\Entities\Book;
use App\Models\Book as BookModel;
use App\Domain\ValueObjects\Isbn;
use DateTimeImmutable;

class EloquentBookRepository implements BookRepository {
    public function save(Book $book): void {
        BookModel::updateOrCreate(
            ['id' => $book->id],
            [
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn->value,
                'category_id' => $book->categoryId,
                'total_copies' => $book->getTotalCopies(),
                'available_copies' => $book->getAvailableCopies(),
                'cover_key' => $book->coverKey,
                'deleted_at' => $book->getDeletedAt()?->format('Y-m-d H:i:s'),
            ]
        );
    }
    public function findById(string $id): ?Book {
        $model = BookModel::find($id);
        if (!$model) return null;
        $book = new Book($model->id, $model->title, $model->author, new Isbn($model->isbn), $model->category_id, $model->total_copies, $model->available_copies, $model->cover_key);
        if ($model->deleted_at) $book->markAsDeleted(new DateTimeImmutable($model->deleted_at));
        return $book;
    }
    public function search(?string $query, ?string $categoryId): array {
        $q = BookModel::whereNull('deleted_at');
        if ($categoryId) $q->where('category_id', $categoryId);
        if ($query) {
            $q->where(function($qq) use ($query) {
                $qq->where('title', 'ilike', "%$query%")->orWhere('author', 'ilike', "%$query%")->orWhere('isbn', 'like', "%$query%");
            });
        }
        return $q->get()->map(fn($m) => $this->findById($m->id))->toArray();
    }
    public function checkIsbnExists(string $isbn): bool {
        return BookModel::where('isbn', $isbn)->whereNull('deleted_at')->exists();
    }
}
PHP,
    'app/Infrastructure/Repositories/EloquentMemberRepository.php' => <<<'PHP'
<?php
namespace App\Infrastructure\Repositories;
use App\Domain\Repositories\MemberRepository;
use App\Domain\Entities\Member;
use App\Models\Member as MemberModel;

class EloquentMemberRepository implements MemberRepository {
    public function save(Member $member): void {
        MemberModel::updateOrCreate(['id' => $member->id], ['full_name' => $member->fullName, 'document_number' => $member->documentNumber]);
    }
    public function findById(string $id): ?Member {
        $model = MemberModel::find($id);
        return $model ? new Member($model->id, $model->full_name, $model->document_number) : null;
    }
    public function checkDocumentExists(string $document): bool {
        return MemberModel::where('document_number', $document)->exists();
    }
}
PHP,
    'app/Infrastructure/Repositories/EloquentUserRepository.php' => <<<'PHP'
<?php
namespace App\Infrastructure\Repositories;
use App\Domain\Repositories\UserRepository;
use App\Domain\Entities\User;
use App\Models\User as UserModel;

class EloquentUserRepository implements UserRepository {
    public function save(User $user): void {
        UserModel::updateOrCreate(['id' => $user->id], ['username' => $user->username, 'password_hash' => $user->passwordHash, 'role' => $user->role]);
    }
    public function findByUsername(string $username): ?User {
        $model = UserModel::where('username', $username)->first();
        return $model ? new User($model->id, $model->username, $model->password_hash, $model->role) : null;
    }
    public function findById(string $id): ?User {
        $model = UserModel::find($id);
        return $model ? new User($model->id, $model->username, $model->password_hash, $model->role) : null;
    }
}
PHP,
    'app/Infrastructure/Repositories/EloquentLoanRepository.php' => <<<'PHP'
<?php
namespace App\Infrastructure\Repositories;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Models\Loan as LoanModel;
use App\Models\LoanItem as LoanItemModel;
use DateTimeImmutable;

class EloquentLoanRepository implements LoanRepository {
    public function save(Loan $loan): void {
        LoanModel::updateOrCreate(['id' => $loan->id], ['member_id' => $loan->memberId, 'loaned_by_user_id' => $loan->loanedByUserId, 'loaned_at' => $loan->loanedAt->format('Y-m-d H:i:s')]);
        foreach ($loan->getItems() as $item) {
            LoanItemModel::updateOrCreate(
                ['id' => $item->id],
                [
                    'loan_id' => $item->loanId,
                    'book_id' => $item->bookId,
                    'book_title' => $item->bookTitle,
                    'isbn' => $item->isbn,
                    'category_name' => $item->categoryName,
                    'due_at' => $item->dueAt->format('Y-m-d H:i:s'),
                    'returned_at' => $item->getReturnedAt()?->format('Y-m-d H:i:s')
                ]
            );
        }
    }
    public function findById(string $id): ?Loan {
        $model = LoanModel::with('items')->find($id);
        if (!$model) return null;
        $items = [];
        foreach ($model->items as $item) {
            $items[] = new LoanItem($item->id, $item->loan_id, $item->book_id, $item->book_title, $item->isbn, $item->category_name, new DateTimeImmutable($item->due_at), $item->returned_at ? new DateTimeImmutable($item->returned_at) : null);
        }
        return new Loan($model->id, $model->member_id, $model->loaned_by_user_id, new DateTimeImmutable($model->loaned_at), $items);
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
echo "Implementations created.";
