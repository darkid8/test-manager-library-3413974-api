<?php

$files = [
    'app/Http/Controllers/AuthController.php' => <<<'PHP'
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
PHP,
    'app/Http/Controllers/UserController.php' => <<<'PHP'
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
PHP,
    'app/Http/Controllers/CategoryController.php' => <<<'PHP'
<?php
namespace App\Http\Controllers;
class CategoryController extends Controller {
    public function index() {
        return response()->json(\App\Models\Category::all());
    }
}
PHP,
    'app/Http/Controllers/MemberController.php' => <<<'PHP'
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
PHP,
];

foreach ($files as $path => $content) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($path, $content);
}
echo "Auth, User, Category, Member controllers created.";
