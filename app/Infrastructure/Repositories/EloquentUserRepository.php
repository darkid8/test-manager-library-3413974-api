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