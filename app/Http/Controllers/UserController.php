<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Domain\Repositories\UserRepository;
use App\Domain\Entities\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    public function createLibrarian(Request $request, UserRepository $repo) {
        if (auth()->user()->role !== 'admin') return response()->json(['message' => 'Prohibido.'], 403);
        $validated = $request->validate(['username' => 'required', 'password' => 'required']);
        $username = strtolower(trim($validated['username']));
        if ($repo->findByUsername($username)) {
            return response()->json(['message' => 'El usuario ya existe.'], 409);
        }
        $user = new User(Str::uuid()->toString(), $username, Hash::make($validated['password']), 'librarian');
        $repo->save($user);
        return response()->json(['id' => $user->id], 201);
    }
}