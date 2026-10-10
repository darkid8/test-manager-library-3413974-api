<?php

$files = [
    'app/Models/User.php' => <<<'PHP'
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'library.user';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['id', 'username', 'password_hash', 'role'];
    protected $hidden = ['password_hash'];

    public function getAuthPassword() { return $this->password_hash; }

    public function getJWTIdentifier() { return $this->getKey(); }
    public function getJWTCustomClaims() { return ['role' => $this->role]; }
}
PHP,

    'app/Models/Category.php' => <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'library.category';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'name'];
}
PHP,

    'app/Models/Book.php' => <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'library.book';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'title', 'author', 'isbn', 'category_id', 'total_copies', 'available_copies', 'cover_key', 'deleted_at'];
    protected $casts = ['deleted_at' => 'datetime'];

    public function category() { return $this->belongsTo(Category::class); }
}
PHP,

    'app/Models/Member.php' => <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = 'library.member';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'full_name', 'document_number'];
}
PHP,

    'app/Models/Loan.php' => <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $table = 'library.loan';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'member_id', 'loaned_by_user_id', 'loaned_at'];
    protected $casts = ['loaned_at' => 'datetime'];

    public function items() { return $this->hasMany(LoanItem::class); }
    public function member() { return $this->belongsTo(Member::class); }
}
PHP,

    'app/Models/LoanItem.php' => <<<'PHP'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LoanItem extends Model
{
    protected $table = 'library.loan_item';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['id', 'loan_id', 'book_id', 'book_title', 'isbn', 'category_name', 'due_at', 'returned_at'];
    protected $casts = ['due_at' => 'datetime', 'returned_at' => 'datetime'];

    public function loan() { return $this->belongsTo(Loan::class); }
    public function book() { return $this->belongsTo(Book::class); }
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
echo "Models created.";
