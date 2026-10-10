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