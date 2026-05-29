<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\AuthorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookController::class, 'index']);
Route::resource('books', BookController::class);
Route::resource('authors', AuthorController::class);

Route::get('/export/csv', [BookController::class, 'exportCsv'])->name('export.csv');
Route::get('/export/xml', [BookController::class, 'exportXml'])->name('export.xml');
