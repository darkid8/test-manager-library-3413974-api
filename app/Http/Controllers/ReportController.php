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