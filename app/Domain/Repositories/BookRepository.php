<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Book;

interface BookRepository {
    public function save(Book $book): void;
    public function findById(string $id): ?Book;
    public function search(?string $query, ?string $categoryId): array;
    public function checkIsbnExists(string $isbn): bool;
}