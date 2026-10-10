<?php

namespace Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\User;
use DomainException;

class UserTest extends TestCase
{
    public function test_user_normalizes_username()
    {
        $user = new User('id', ' Ana ', 'hash', 'librarian');
        $this->assertEquals('ana', $user->username);
    }

    public function test_user_rejects_invalid_role()
    {
        $this->expectException(DomainException::class);
        new User('id', 'ana', 'hash', 'superadmin');
    }
}