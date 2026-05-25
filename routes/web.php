<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index']);
Route::resource('books', BookController::class);

Route::get('/export/csv', [BookController::class, 'exportCsv'])->name('export.csv');
Route::get('/export/xml', [BookController::class, 'exportXml'])->name('export.xml');
