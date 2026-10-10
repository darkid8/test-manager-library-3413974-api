<?php

namespace Tests\Unit\Domain\Entities;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\Member;

class MemberTest extends TestCase
{
    public function test_member_normalizes_name()
    {
        $member = new Member('id', ' Ana ', '1234');
        $this->assertEquals('Ana', $member->fullName);
    }
}