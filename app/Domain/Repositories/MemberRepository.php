<?php
namespace App\Domain\Repositories;
use App\Domain\Entities\Member;

interface MemberRepository {
    public function save(Member $member): void;
    public function findById(string $id): ?Member;
    public function checkDocumentExists(string $document): bool;
}