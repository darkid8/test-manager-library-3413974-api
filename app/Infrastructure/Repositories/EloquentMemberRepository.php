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