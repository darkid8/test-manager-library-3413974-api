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