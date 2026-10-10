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