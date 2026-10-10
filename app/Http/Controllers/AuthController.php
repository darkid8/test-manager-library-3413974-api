<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Domain\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller {
    public function login(Request $request, UserRepository $repo) {
        $validated = $request->validate(['username' => 'required', 'password' => 'required']);
        $user = $repo->findByUsername(strtolower(trim($validated['username'])));
        if (!$user || !Hash::check($validated['password'], $user->passwordHash)) {
            return response()->json(['message' => 'Credenciales inválidas.'], 401);
        }
        $token = JWTAuth::fromUser(\App\Models\User::find($user->id));
        return response()->json(['token' => $token, 'role' => $user->role]);
    }
}