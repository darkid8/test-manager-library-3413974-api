<?php
namespace App\Infrastructure\Repositories;
use App\Domain\Repositories\LoanRepository;
use App\Domain\Entities\Loan;
use App\Domain\Entities\LoanItem;
use App\Models\Loan as LoanModel;
use App\Models\LoanItem as LoanItemModel;
use DateTimeImmutable;

class EloquentLoanRepository implements LoanRepository {
    public function save(Loan $loan): void {
        LoanModel::updateOrCreate(['id' => $loan->id], ['member_id' => $loan->memberId, 'loaned_by_user_id' => $loan->loanedByUserId, 'loaned_at' => $loan->loanedAt->format('Y-m-d H:i:s')]);
        foreach ($loan->getItems() as $item) {
            LoanItemModel::updateOrCreate(
                ['id' => $item->id],
                [
                    'loan_id' => $item->loanId,
                    'book_id' => $item->bookId,
                    'book_title' => $item->bookTitle,
                    'isbn' => $item->isbn,
                    'category_name' => $item->categoryName,
                    'due_at' => $item->dueAt->format('Y-m-d H:i:s'),
                    'returned_at' => $item->getReturnedAt()?->format('Y-m-d H:i:s')
                ]
            );
        }
    }
    public function findById(string $id): ?Loan {
        $model = LoanModel::with('items')->find($id);
        if (!$model) return null;
        $items = [];
        foreach ($model->items as $item) {
            $items[] = new LoanItem($item->id, $item->loan_id, $item->book_id, $item->book_title, $item->isbn, $item->category_name, new DateTimeImmutable($item->due_at), $item->returned_at ? new DateTimeImmutable($item->returned_at) : null);
        }
        return new Loan($model->id, $model->member_id, $model->loaned_by_user_id, new DateTimeImmutable($model->loaned_at), $items);
    }
}