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