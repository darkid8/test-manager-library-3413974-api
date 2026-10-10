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