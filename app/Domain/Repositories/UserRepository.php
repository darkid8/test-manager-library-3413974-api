<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\User;

interface UserRepository {
    public function save(User $user): void;
    public function findByUsername(string $username): ?User;
    public function findById(string $id): ?User;
}