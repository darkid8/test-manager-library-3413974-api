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