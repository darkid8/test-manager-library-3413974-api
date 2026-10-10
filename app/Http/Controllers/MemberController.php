<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Domain\Repositories\MemberRepository;
use App\Domain\Entities\Member;
use Illuminate\Support\Str;

class MemberController extends Controller {
    public function index() {
        return response()->json(\App\Models\Member::all());
    }
    public function store(Request $request, MemberRepository $repo) {
        $validated = $request->validate(['full_name' => 'required', 'document_number' => 'required']);
        if ($repo->checkDocumentExists($validated['document_number'])) {
            return response()->json(['message' => 'Documento duplicado.'], 409);
        }
        $member = new Member(Str::uuid()->toString(), $validated['full_name'], $validated['document_number']);
        $repo->save($member);
        return response()->json(['id' => $member->id], 201);
    }
    public function show($id) {
        return response()->json(\App\Models\Member::findOrFail($id));
    }
}