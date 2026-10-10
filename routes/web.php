<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/v1/health', function () {
    return response()->json(['status' => 'ok']);
});
