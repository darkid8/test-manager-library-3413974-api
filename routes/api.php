<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () { return response()->json(['status' => 'ok']); });
    
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/users', [UserController::class, 'createLibrarian']);
        
        Route::get('/categories', [CategoryController::class, 'index']);
        
        Route::get('/members', [MemberController::class, 'index']);
        Route::post('/members', [MemberController::class, 'store']);
        Route::get('/members/{id}', [MemberController::class, 'show']);
        
        Route::get('/books', [BookController::class, 'index']);
        Route::post('/books', [BookController::class, 'store']);
        Route::get('/books/{id}', [BookController::class, 'show']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
        Route::post('/books/{id}/cover', [BookController::class, 'uploadCover']);
        
        Route::post('/loans', [LoanController::class, 'lend']);
        Route::post('/loans/{loan_id}/items/{item_id}/return', [LoanController::class, 'returnItem']);
        Route::get('/loans/overdue', [LoanController::class, 'overdue']);
        
        Route::get('/reports/most-borrowed', [ReportController::class, 'mostBorrowed']);
    });
});
