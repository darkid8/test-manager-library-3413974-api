<?php

$files = [
    'app/Http/Controllers/BookController.php' => <<<'PHP'
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Domain\Repositories\BookRepository;
use App\Domain\Entities\Book;
use App\Domain\ValueObjects\Isbn;
use Illuminate\Support\Str;
use DateTimeImmutable;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller {
    public function index(Request $request, BookRepository $repo) {
        $books = $repo->search($request->query('q'), $request->query('category_id'));
        return response()->json(array_map(fn($b) => [
            'id' => $b->id, 'title' => $b->title, 'author' => $b->author, 'isbn' => $b->isbn->value,
            'category_id' => $b->categoryId, 'total_copies' => $b->getTotalCopies(), 'available_copies' => $b->getAvailableCopies(),
            'cover_key' => $b->coverKey
        ], $books));
    }
    public function store(Request $request, BookRepository $repo) {
        $validated = $request->validate(['title' => 'required', 'author' => 'required', 'isbn' => 'required', 'category_id' => 'required', 'total_copies' => 'required|integer|min:1']);
        if ($repo->checkIsbnExists($validated['isbn'])) return response()->json(['message' => 'ISBN activo duplicado.'], 409);
        $book = new Book(Str::uuid()->toString(), $validated['title'], $validated['author'], new Isbn($validated['isbn']), $validated['category_id'], $validated['total_copies'], $validated['total_copies']);
        $repo->save($book);
        return response()->json(['id' => $book->id], 201);
    }
    public function show($id, BookRepository $repo) {
        $book = $repo->findById($id);
        if (!$book || $book->isDeleted()) return response()->json(['message' => 'Not found'], 404);
        return response()->json([
            'id' => $book->id, 'title' => $book->title, 'author' => $book->author, 'isbn' => $book->isbn->value,
            'category_id' => $book->categoryId, 'total_copies' => $book->getTotalCopies(), 'available_copies' => $b->getAvailableCopies(),
            'cover_key' => $book->coverKey
        ]);
    }
    public function update($id, Request $request, BookRepository $repo) {
        $book = $repo->findById($id);
        if (!$book || $book->isDeleted()) return response()->json(['message' => 'Not found'], 404);
        $validated = $request->validate(['total_copies' => 'required|integer']);
        try {
            $book->changeTotalCopies($validated['total_copies']);
            $repo->save($book);
            return response()->json(['message' => 'OK']);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    public function destroy($id, BookRepository $repo) {
        $book = $repo->findById($id);
        if (!$book || $book->isDeleted()) return response()->json(['message' => 'Not found'], 404);
        $hasOpenLoans = \App\Models\LoanItem::where('book_id', $id)->whereNull('returned_at')->exists();
        if ($hasOpenLoans) return response()->json(['message' => 'Cannot delete book with open loans.'], 409);
        $book->markAsDeleted(new DateTimeImmutable());
        $repo->save($book);
        return response()->json(['message' => 'OK']);
    }
    public function uploadCover($id, Request $request, BookRepository $repo) {
        $book = $repo->findById($id);
        if (!$book || $book->isDeleted()) return response()->json(['message' => 'Not found'], 404);
        $request->validate(['cover' => 'required|image|mimes:jpeg,png|max:1024']);
        $path = $request->file('cover')->store('covers', 'public');
        $book->coverKey = $path;
        $repo->save($book);
        return response()->json(['cover_key' => $path]);
    }
}
PHP,
    'app/Http/Controllers/LoanController.php' => <<<'PHP'
<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Repositories\BookRepository;
use App\Domain\Entities\Loan;
use Illuminate\Support\Str;
use DateTimeImmutable;

class LoanController extends Controller {
    public function lend(Request $request, LoanRepository $loanRepo, BookRepository $bookRepo) {
        $validated = $request->validate(['member_id' => 'required', 'book_ids' => 'required|array']);
        
        // Concurrency lock (simulate or use DB transaction)
        return \Illuminate\Support\Facades\DB::transaction(function() use ($validated, $loanRepo, $bookRepo) {
            $loan = new Loan(Str::uuid()->toString(), $validated['member_id'], auth()->id() ?? 'admin-id', new DateTimeImmutable());
            foreach ($validated['book_ids'] as $bookId) {
                // Lock book for update
                \App\Models\Book::where('id', $bookId)->lockForUpdate()->first();
                $book = $bookRepo->findById($bookId);
                if (!$book || $book->isDeleted()) throw new \DomainException("Book not found or deleted");
                $category = \App\Models\Category::find($book->categoryId);
                $loan->addItem(Str::uuid()->toString(), $book, $category->name, new DateTimeImmutable());
                $bookRepo->save($book);
            }
            $loanRepo->save($loan);
            return response()->json(['id' => $loan->id], 201);
        });
    }

    public function returnItem($loanId, $itemId, LoanRepository $loanRepo, BookRepository $bookRepo) {
        return \Illuminate\Support\Facades\DB::transaction(function() use ($loanId, $itemId, $loanRepo, $bookRepo) {
            $loan = $loanRepo->findById($loanId);
            if (!$loan) return response()->json(['message' => 'Not found'], 404);
            $bookId = null;
            foreach ($loan->getItems() as $item) {
                if ($item->id === $itemId) { $bookId = $item->bookId; break; }
            }
            if (!$bookId) return response()->json(['message' => 'Item not found'], 404);
            \App\Models\Book::where('id', $bookId)->lockForUpdate()->first();
            $book = $bookRepo->findById($bookId);
            $loan->returnItem($itemId, $book, new DateTimeImmutable());
            $bookRepo->save($book);
            $loanRepo->save($loan);
            return response()->json(['message' => 'OK']);
        });
    }

    public function overdue() {
        $now = new DateTimeImmutable();
        $loans = \App\Models\Loan::with('items')->whereHas('items', function($q) use ($now) {
            $q->whereNull('returned_at')->where('due_at', '<', $now->format('Y-m-d H:i:s'));
        })->get();
        return response()->json($loans);
    }
}
PHP,
    'app/Http/Controllers/ReportController.php' => <<<'PHP'
<?php
namespace App\Http\Controllers;
class ReportController extends Controller {
    public function mostBorrowed() {
        $data = \Illuminate\Support\Facades\DB::select("
            SELECT book_id, book_title, category_name, COUNT(*) as borrow_count
            FROM library.loan_item
            GROUP BY book_id, book_title, category_name
            ORDER BY borrow_count DESC
            LIMIT 10
        ");
        return response()->json($data);
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
echo "Book, Loan, Report controllers created.";
